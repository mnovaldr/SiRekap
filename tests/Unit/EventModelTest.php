<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit Test untuk Model Event
 *
 * Mencakup:
 * - Static helper: getBulanIndonesia(), makeKeyFull(), makeKeyShort()
 * - Instance method: checkDuplicates()
 * - Query scopes: valid, primary, review, duplikat, byYear, byYearRange, byMonth, bySubSektor
 * - Casts: tanggal, jumlah_peserta, is_primary_row
 */
class EventModelTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // getBulanIndonesia()
    // =========================================================================

    #[Test]
    public function get_bulan_indonesia_returns_correct_month_name_for_date_string(): void
    {
        $this->assertEquals('Januari',   Event::getBulanIndonesia('2024-01-15'));
        $this->assertEquals('Februari',  Event::getBulanIndonesia('2024-02-28'));
        $this->assertEquals('Maret',     Event::getBulanIndonesia('2024-03-10'));
        $this->assertEquals('April',     Event::getBulanIndonesia('2024-04-01'));
        $this->assertEquals('Mei',       Event::getBulanIndonesia('2024-05-20'));
        $this->assertEquals('Juni',      Event::getBulanIndonesia('2024-06-30'));
        $this->assertEquals('Juli',      Event::getBulanIndonesia('2024-07-17'));
        $this->assertEquals('Agustus',   Event::getBulanIndonesia('2024-08-05'));
        $this->assertEquals('September', Event::getBulanIndonesia('2024-09-11'));
        $this->assertEquals('Oktober',   Event::getBulanIndonesia('2024-10-22'));
        $this->assertEquals('November',  Event::getBulanIndonesia('2024-11-03'));
        $this->assertEquals('Desember',  Event::getBulanIndonesia('2024-12-25'));
    }

    #[Test]
    public function get_bulan_indonesia_returns_correct_month_name_for_numeric_month(): void
    {
        $this->assertEquals('Januari',  Event::getBulanIndonesia(1));
        $this->assertEquals('Juni',     Event::getBulanIndonesia(6));
        $this->assertEquals('Desember', Event::getBulanIndonesia(12));
    }

    #[Test]
    public function get_bulan_indonesia_returns_null_for_null_input(): void
    {
        $this->assertNull(Event::getBulanIndonesia(null));
    }

    #[Test]
    public function get_bulan_indonesia_returns_null_for_empty_string(): void
    {
        $this->assertNull(Event::getBulanIndonesia(''));
    }

    // =========================================================================
    // makeKeyFull() & makeKeyShort()
    // =========================================================================

    #[Test]
    public function make_key_full_generates_correct_format(): void
    {
        $event = new Event([
            'nama_organisasi' => 'komunitas kreatif',
            'judul_kegiatan'  => 'workshop desain',
            'tanggal'         => '2024-06-15',
            'lokasi'          => 'studio foto',
            'lantai'          => '3',
        ]);

        $expected = 'KOMUNITAS KREATIF||WORKSHOP DESAIN||2024-06-15||STUDIO FOTO||3';
        $this->assertEquals($expected, Event::makeKeyFull($event));
    }

    #[Test]
    public function make_key_full_trims_whitespace_and_uppercases(): void
    {
        $event = new Event([
            'nama_organisasi' => '  komunitas  ',
            'judul_kegiatan'  => '  gathering   ',
            'tanggal'         => '2024-03-20',
            'lokasi'          => ' ruang meeting ',
            'lantai'          => '2',
        ]);

        $result = Event::makeKeyFull($event);
        $this->assertStringContainsString('KOMUNITAS', $result);
        $this->assertStringContainsString('GATHERING', $result);
        $this->assertStringNotContainsString('  ', $result);
    }

    #[Test]
    public function make_key_short_generates_correct_format_without_lokasi_lantai(): void
    {
        $event = new Event([
            'nama_organisasi' => 'startup indonesia',
            'judul_kegiatan'  => 'pitch day 2024',
            'tanggal'         => '2024-09-10',
        ]);

        $expected = 'STARTUP INDONESIA||PITCH DAY 2024||2024-09-10';
        $this->assertEquals($expected, Event::makeKeyShort($event));
    }

    #[Test]
    public function make_key_full_and_key_short_share_common_prefix(): void
    {
        $event = new Event([
            'nama_organisasi' => 'hmj teknik',
            'judul_kegiatan'  => 'seminar nasional',
            'tanggal'         => '2024-11-05',
            'lokasi'          => 'amphitheater 1',
            'lantai'          => '4',
        ]);

        $full  = Event::makeKeyFull($event);
        $short = Event::makeKeyShort($event);

        $this->assertStringStartsWith($short, $full);
    }

    // =========================================================================
    // checkDuplicates()
    // =========================================================================

    #[Test]
    public function check_duplicates_sets_status_duplikat_when_full_key_exists(): void
    {
        Event::factory()->create([
            'key_full'  => 'ORG||JUDUL||2024-01-01||STUDIO||2',
            'key_short' => 'ORG||JUDUL||2024-01-01',
        ]);

        $newEvent = Event::factory()->make([
            'key_full'  => 'ORG||JUDUL||2024-01-01||STUDIO||2',
            'key_short' => 'ORG||JUDUL||2024-01-01',
        ]);
        $newEvent->save();

        $newEvent->checkDuplicates();

        $this->assertEquals('Duplikat', $newEvent->status_data);
        $this->assertEquals('Duplicate FULL', $newEvent->catatan_review);
    }

    #[Test]
    public function check_duplicates_sets_status_review_when_short_key_exists(): void
    {
        Event::factory()->create([
            'key_full'  => 'ORG||JUDUL||2024-01-01||STUDIO A||2',
            'key_short' => 'ORG||JUDUL||2024-01-01',
        ]);

        $newEvent = Event::factory()->make([
            'key_full'  => 'ORG||JUDUL||2024-01-01||STUDIO B||3',
            'key_short' => 'ORG||JUDUL||2024-01-01',
        ]);
        $newEvent->save();

        $newEvent->checkDuplicates();

        $this->assertEquals('Review', $newEvent->status_data);
        $this->assertEquals('Duplicate SHORT', $newEvent->catatan_review);
    }

    #[Test]
    public function check_duplicates_sets_status_valid_when_no_duplicate(): void
    {
        $event = Event::factory()->create([
            'key_full'    => 'UNIQUE_ORG||UNIQUE_JUDUL||2024-06-01||RUANG A||2',
            'key_short'   => 'UNIQUE_ORG||UNIQUE_JUDUL||2024-06-01',
            'status_data' => 'Review',
        ]);

        $event->checkDuplicates();

        $this->assertEquals('Valid', $event->status_data);
        $this->assertNull($event->catatan_review);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    #[Test]
    public function scope_valid_returns_only_valid_events(): void
    {
        Event::factory()->create(['status_data' => 'Valid']);
        Event::factory()->create(['status_data' => 'Duplikat']);
        Event::factory()->create(['status_data' => 'Review']);

        $results = Event::valid()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Valid', $results->first()->status_data);
    }

    #[Test]
    public function scope_duplikat_returns_only_duplikat_events(): void
    {
        Event::factory()->create(['status_data' => 'Valid']);
        Event::factory()->create(['status_data' => 'Duplikat']);
        Event::factory()->create(['status_data' => 'Duplikat']);

        $results = Event::duplikat()->get();

        $this->assertCount(2, $results);
        $results->each(fn ($e) => $this->assertEquals('Duplikat', $e->status_data));
    }

    #[Test]
    public function scope_review_returns_only_review_events(): void
    {
        Event::factory()->create(['status_data' => 'Valid']);
        Event::factory()->create(['status_data' => 'Review']);

        $results = Event::review()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Review', $results->first()->status_data);
    }

    #[Test]
    public function scope_primary_returns_only_primary_rows(): void
    {
        Event::factory()->create(['is_primary_row' => true]);
        Event::factory()->create(['is_primary_row' => false]);
        Event::factory()->create(['is_primary_row' => true]);

        $results = Event::primary()->get();

        $this->assertCount(2, $results);
        $results->each(fn ($e) => $this->assertTrue((bool) $e->is_primary_row));
    }

    #[Test]
    public function scope_by_year_filters_by_year(): void
    {
        Event::factory()->create(['tanggal' => '2024-06-01']);
        Event::factory()->create(['tanggal' => '2023-06-01']);
        Event::factory()->create(['tanggal' => '2025-06-01']);

        $results = Event::byYear(2024)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('2024-06-01', $results->first()->tanggal->format('Y-m-d'));
    }

    #[Test]
    public function scope_by_year_returns_all_when_null(): void
    {
        Event::factory()->count(3)->create();

        $results = Event::byYear(null)->get();

        $this->assertCount(3, $results);
    }

    #[Test]
    public function scope_by_year_range_filters_correctly(): void
    {
        Event::factory()->create(['tanggal' => '2022-06-01']);
        Event::factory()->create(['tanggal' => '2023-06-01']);
        Event::factory()->create(['tanggal' => '2024-06-01']);
        Event::factory()->create(['tanggal' => '2025-06-01']);

        $results = Event::byYearRange(2023, 2024)->get();

        $this->assertCount(2, $results);
    }

    #[Test]
    public function scope_by_month_filters_by_month_number(): void
    {
        Event::factory()->create(['tanggal' => '2024-03-15']);
        Event::factory()->create(['tanggal' => '2024-06-01']);
        Event::factory()->create(['tanggal' => '2024-03-28']);

        $results = Event::byMonth(3)->get();

        $this->assertCount(2, $results);
    }

    #[Test]
    public function scope_by_sub_sektor_filters_by_sub_sektor(): void
    {
        Event::factory()->create(['sub_sektor' => 'Musik']);
        Event::factory()->create(['sub_sektor' => 'Film']);
        Event::factory()->create(['sub_sektor' => 'Musik']);

        $results = Event::bySubSektor('Musik')->get();

        $this->assertCount(2, $results);
    }

    // =========================================================================
    // Casts
    // =========================================================================

    #[Test]
    public function tanggal_is_cast_to_carbon_date_instance(): void
    {
        $event = Event::factory()->create(['tanggal' => '2024-08-17']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $event->tanggal);
        $this->assertEquals('2024-08-17', $event->tanggal->format('Y-m-d'));
    }

    #[Test]
    public function jumlah_peserta_is_cast_to_integer(): void
    {
        $event = Event::factory()->create(['jumlah_peserta' => 150]);

        $this->assertIsInt($event->jumlah_peserta);
        $this->assertEquals(150, $event->jumlah_peserta);
    }

    #[Test]
    public function is_primary_row_is_cast_to_boolean(): void
    {
        $event = Event::factory()->create(['is_primary_row' => true]);

        $this->assertIsBool($event->is_primary_row);
        $this->assertTrue($event->is_primary_row);
    }

    // =========================================================================
    // Title Case & Abbreviation Logic
    // =========================================================================

    #[Test]
    public function helper_to_title_case_with_abbreviations_works_correctly(): void
    {
        $this->assertEquals('SMA Negeri 9 Malang', Event::toTitleCaseWithAbbreviations('  sma   negeri   9   malang  '));
        $this->assertEquals('CV Bunga Mekar', Event::toTitleCaseWithAbbreviations('cv bunga mekar'));
        $this->assertEquals('ITN Malang', Event::toTitleCaseWithAbbreviations('itn malang'));
        $this->assertEquals('Sosialisasi Program SDGS', Event::toTitleCaseWithAbbreviations('sosialisasi program sdgs'));
        $this->assertEquals('Sanggar Angkasa', Event::toTitleCaseWithAbbreviations('SANGGAR ANGKASA'));
        $this->assertNull(Event::toTitleCaseWithAbbreviations(null));
    }

    #[Test]
    public function model_automatically_formats_text_fields_on_save(): void
    {
        $event = Event::factory()->make([
            'nama_organisasi' => '  sma   negeri   9   malang  ',
            'judul_kegiatan' => '  sosialisasi   program  sdgs   ',
            'pic' => '  pic   kegiatan  ',
            'lokasi' => '  ruang  meeting  ',
            'bidang' => '  komunitas  ',
        ]);
        $event->save();

        $this->assertEquals('SMA Negeri 9 Malang', $event->nama_organisasi);
        $this->assertEquals('Sosialisasi Program SDGS', $event->judul_kegiatan);
        $this->assertEquals('PIC Kegiatan', $event->pic);
        $this->assertEquals('Ruang Meeting', $event->lokasi);
        $this->assertEquals('Komunitas', $event->bidang);
    }
}
