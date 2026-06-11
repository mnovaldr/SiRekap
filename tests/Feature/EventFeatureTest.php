<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;

/**
 * Feature Test untuk Model Event (Database Layer)
 */
class EventFeatureTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Create
    // =========================================================================

    #[Test]
    public function event_can_be_created_and_stored_in_database(): void
    {
        $event = Event::factory()->create([
            'nama_organisasi' => 'Komunitas Teknologi',
            'judul_kegiatan'  => 'Workshop Laravel',
            'tanggal'         => '2024-06-15',
            'status_data'     => 'Valid',
        ]);

        $this->assertDatabaseHas('events', [
            'nama_organisasi' => 'Komunitas Teknologi',
            'judul_kegiatan'  => 'Workshop Laravel',
        ]);
        $this->assertNotNull($event->id);
    }

    #[Test]
    public function event_id_is_uuid_format(): void
    {
        $event = Event::factory()->create();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $event->id
        );
    }

    #[Test]
    public function event_nullable_columns_accept_null(): void
    {
        $event = Event::factory()->create([
            'sub_sektor'     => null,
            'program_sdgs'   => null,
            'email'          => null,
            'no_telepon'     => null,
            'pic'            => null,
            'catatan_review' => null,
        ]);

        $this->assertDatabaseHas('events', [
            'id'         => $event->id,
            'sub_sektor' => null,
            'email'      => null,
        ]);
    }

    // =========================================================================
    // Read
    // =========================================================================

    #[Test]
    public function event_can_be_retrieved_by_id(): void
    {
        $event = Event::factory()->create(['judul_kegiatan' => 'Event Besar']);

        $found = Event::find($event->id);

        $this->assertNotNull($found);
        $this->assertEquals('Event Besar', $found->judul_kegiatan);
    }

    #[Test]
    public function multiple_events_can_be_retrieved(): void
    {
        Event::factory()->count(5)->create();

        $this->assertCount(5, Event::all());
    }

    // =========================================================================
    // Update
    // =========================================================================

    #[Test]
    public function event_can_be_updated(): void
    {
        $event = Event::factory()->create(['status_data' => 'Review']);

        $event->update(['status_data' => 'Valid', 'catatan_review' => null]);

        $this->assertDatabaseHas('events', [
            'id'          => $event->id,
            'status_data' => 'Valid',
        ]);
    }

    #[Test]
    public function event_jumlah_peserta_can_be_updated(): void
    {
        $event = Event::factory()->create(['jumlah_peserta' => 100]);

        $event->update(['jumlah_peserta' => 250]);

        $this->assertEquals(250, $event->fresh()->jumlah_peserta);
    }

    // =========================================================================
    // Delete
    // =========================================================================

    #[Test]
    public function event_can_be_deleted_virtually(): void
    {
        $event = Event::factory()->create(['status_data' => 'Valid']);

        $event->update([
            'status_data' => 'Dihapus',
            'alasan_dihapus' => 'Testing deletion reason',
        ]);

        $this->assertEquals('Dihapus', $event->fresh()->status_data);
        $this->assertEquals('Testing deletion reason', $event->fresh()->alasan_dihapus);
    }

    #[Test]
    public function deleting_event_virtually_does_not_affect_other_events(): void
    {
        $events   = Event::factory()->count(3)->create(['status_data' => 'Valid']);
        $toDelete = $events->first();

        $toDelete->update([
            'status_data' => 'Dihapus',
            'alasan_dihapus' => 'Testing deletion reason',
        ]);

        $this->assertEquals(2, Event::where('status_data', '!=', 'Dihapus')->count());
    }

    // =========================================================================
    // Cache Flush
    // =========================================================================

    #[Test]
    public function cache_is_flushed_when_event_is_saved(): void
    {
        Cache::put('test_cache_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::get('test_cache_key'));

        Event::factory()->create();

        $this->assertNull(Cache::get('test_cache_key'));
    }

    #[Test]
    public function cache_is_flushed_when_event_is_deleted_virtually(): void
    {
        $event = Event::factory()->create(['status_data' => 'Valid']);

        Cache::put('test_key', 'some_data', 60);
        $this->assertEquals('some_data', Cache::get('test_key'));

        $event->update(['status_data' => 'Dihapus']);

        $this->assertNull(Cache::get('test_key'));
    }

    // =========================================================================
    // Scopes via Database
    // =========================================================================

    #[Test]
    public function scope_valid_excludes_duplikat_and_review(): void
    {
        Event::factory()->create(['status_data' => 'Valid']);
        Event::factory()->create(['status_data' => 'Duplikat']);
        Event::factory()->create(['status_data' => 'Review']);
        Event::factory()->create(['status_data' => 'Valid']);

        $valids = Event::valid()->get();

        $this->assertCount(2, $valids);
        $valids->each(fn($e) => $this->assertEquals('Valid', $e->status_data));
    }

    #[Test]
    public function scope_primary_with_is_primary_row_true(): void
    {
        Event::factory()->create(['is_primary_row' => true]);
        Event::factory()->create(['is_primary_row' => true]);
        Event::factory()->create(['is_primary_row' => false]);

        $primaries = Event::primary()->get();

        $this->assertCount(2, $primaries);
    }

    #[Test]
    public function scope_by_year_filters_correctly_in_database(): void
    {
        Event::factory()->create(['tanggal' => '2023-08-01']);
        Event::factory()->create(['tanggal' => '2024-08-01']);
        Event::factory()->create(['tanggal' => '2025-08-01']);

        $result2024 = Event::byYear(2024)->get();
        $this->assertCount(1, $result2024);
        $this->assertEquals('2024', $result2024->first()->tanggal->format('Y'));
    }

    #[Test]
    public function scope_by_month_filters_correctly_in_database(): void
    {
        Event::factory()->create(['tanggal' => '2024-01-10']);
        Event::factory()->create(['tanggal' => '2024-01-25']);
        Event::factory()->create(['tanggal' => '2024-03-15']);

        $januaris = Event::byMonth(1)->get();
        $this->assertCount(2, $januaris);
    }

    #[Test]
    public function scope_by_sub_sektor_filters_correctly_in_database(): void
    {
        Event::factory()->create(['sub_sektor' => 'Film']);
        Event::factory()->create(['sub_sektor' => 'Musik']);
        Event::factory()->create(['sub_sektor' => 'Film']);

        $films = Event::bySubSektor('Film')->get();
        $this->assertCount(2, $films);
    }

    // =========================================================================
    // Key Fields
    // =========================================================================

    #[Test]
    public function event_stores_key_full_and_key_short_correctly(): void
    {
        $event = Event::factory()->create([
            'key_full'  => 'ORG||JUDUL||2024-01-01||STUDIO||2',
            'key_short' => 'ORG||JUDUL||2024-01-01',
        ]);

        $this->assertDatabaseHas('events', [
            'id'        => $event->id,
            'key_full'  => 'ORG||JUDUL||2024-01-01||STUDIO||2',
            'key_short' => 'ORG||JUDUL||2024-01-01',
        ]);
    }

    #[Test]
    public function duplicate_key_full_can_exist_in_database(): void
    {
        Event::factory()->create(['key_full' => 'SAME||KEY||FULL||LOC||2']);
        Event::factory()->create(['key_full' => 'SAME||KEY||FULL||LOC||2']);

        $count = Event::where('key_full', 'SAME||KEY||FULL||LOC||2')->count();
        $this->assertEquals(2, $count);
    }

    // =========================================================================
    // Kombinasi Scope
    // =========================================================================

    #[Test]
    public function chaining_scopes_valid_and_by_year_works(): void
    {
        Event::factory()->create(['status_data' => 'Valid',    'tanggal' => '2024-05-01']);
        Event::factory()->create(['status_data' => 'Duplikat', 'tanggal' => '2024-05-01']);
        Event::factory()->create(['status_data' => 'Valid',    'tanggal' => '2023-05-01']);

        $result = Event::valid()->byYear(2024)->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Valid', $result->first()->status_data);
        $this->assertEquals('2024', $result->first()->tanggal->format('Y'));
    }

    #[Test]
    public function scope_by_year_range_includes_boundary_years(): void
    {
        Event::factory()->create(['tanggal' => '2022-06-15']); // di luar range
        Event::factory()->create(['tanggal' => '2023-06-01']); // dalam range
        Event::factory()->create(['tanggal' => '2024-06-15']); // dalam range
        Event::factory()->create(['tanggal' => '2025-06-01']); // di luar range

        $result = Event::byYearRange(2023, 2024)->get();

        $this->assertCount(2, $result);
    }

}
