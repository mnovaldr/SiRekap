<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Event;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class KelolaDatabase extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static string $view = 'filament.pages.kelola-database';
    protected static ?string $navigationLabel = 'Kelola Database';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Kelola Database';

    public function getStats(): array
    {
        return [
            'total'    => Event::count(),
            'valid'    => Event::valid()->count(),
            'review'   => Event::review()->count(),
            'duplikat' => Event::duplikat()->count(),
            'logs'     => ActivityLog::count(),
        ];
    }

    // Hapus semua data Duplikat
    public function hapusDuplikat(): void
    {
        $count = Event::duplikat()->count();
        if ($count === 0) {
            Notification::make()->title('Tidak ada data duplikat')->warning()->send();
            return;
        }
        Event::duplikat()->delete();
        ActivityLog::logActivity('delete', null, "Hapus {$count} data Duplikat");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("{$count} data duplikat berhasil dihapus")->success()->send();
    }

    // Validasi semua data Review
    public function validasiSemuaReview(): void
    {
        $count = Event::review()->count();
        if ($count === 0) {
            Notification::make()->title('Tidak ada data review')->warning()->send();
            return;
        }
        Event::review()->update(['status_data' => 'Valid', 'catatan_review' => null]);
        ActivityLog::logActivity('validate', null, "Validasi {$count} data Review menjadi Valid");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("{$count} data review berhasil divalidasi")->success()->send();
    }

    // Hapus semua data (truncate)
    public function hapusSemuaData(): void
    {
        $count = Event::count();
        Event::truncate();
        ActivityLog::logActivity('delete', null, "Hapus SEMUA data ({$count} baris)");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("Semua data ({$count} baris) berhasil dihapus")->success()->send();
    }

    // Hapus data berdasarkan tahun
    public function hapusDataTahun(int $year): void
    {
        $count = Event::whereYear('tanggal', $year)->count();
        if ($count === 0) {
            Notification::make()->title("Tidak ada data tahun {$year}")->warning()->send();
            return;
        }
        Event::whereYear('tanggal', $year)->delete();
        ActivityLog::logActivity('delete', null, "Hapus {$count} data tahun {$year}");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("{$count} data tahun {$year} berhasil dihapus")->success()->send();
    }

    // Hapus activity logs
    public function hapusActivityLog(): void
    {
        $count = ActivityLog::count();
        ActivityLog::truncate();
        Notification::make()->title("{$count} log aktivitas berhasil dihapus")->success()->send();
    }

    // Recalculate bulan, key_full, key_short untuk semua data
    public function recalculateKeys(): void
    {
        $events = Event::all();
        $updated = 0;
        foreach ($events as $event) {
            $event->bulan     = \App\Models\Event::getBulanIndonesia($event->tanggal);
            $event->key_full  = \App\Models\Event::makeKeyFull($event);
            $event->key_short = \App\Models\Event::makeKeyShort($event);
            $event->saveQuietly(); // skip model events
            $updated++;
        }
        ActivityLog::logActivity('update', null, "Recalculate keys untuk {$updated} data");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("{$updated} data berhasil diperbarui")->success()->send();
    }

    // Re-detect duplikat untuk semua data
    public function redetectDuplikat(): void
    {
        // Reset semua ke Valid dulu
        Event::query()->update(['status_data' => 'Valid', 'catatan_review' => null]);

        // Hitung key_full duplicates
        $fullDups = DB::table('events')
            ->select('key_full', DB::raw('count(*) as cnt'))
            ->groupBy('key_full')
            ->having('cnt', '>', 1)
            ->pluck('cnt', 'key_full');

        // Hitung key_short duplicates
        $shortDups = DB::table('events')
            ->select('key_short', DB::raw('count(*) as cnt'))
            ->groupBy('key_short')
            ->having('cnt', '>', 1)
            ->pluck('cnt', 'key_short');

        $dupCount = 0;
        $reviewCount = 0;

        // Mark duplikat full
        foreach ($fullDups->keys() as $keyFull) {
            $cnt = Event::where('key_full', $keyFull)
                ->update(['status_data' => 'Duplikat', 'catatan_review' => 'Duplikat FULL']);
            $dupCount += $cnt;
        }

        // Mark review (short dup, bukan full dup)
        foreach ($shortDups->keys() as $keyShort) {
            $cnt = Event::where('key_short', $keyShort)
                ->where('status_data', 'Valid') // jangan overwrite Duplikat
                ->update(['status_data' => 'Review', 'catatan_review' => 'Multi Ruangan']);
            $reviewCount += $cnt;
        }

        ActivityLog::logActivity('update', null, "Re-detect duplikat: {$dupCount} Duplikat, {$reviewCount} Review");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()
            ->title("Selesai: {$dupCount} Duplikat, {$reviewCount} Review terdeteksi")
            ->success()
            ->send();
    }

    // Mark satu baris per event (key_short) sebagai primary_row untuk statistik murni
    public function recalculatePrimaryRows(): void
    {
        // 1. Reset semua ke false dulu
        Event::query()->update(['is_primary_row' => DB::raw('false')]);

        // 2. Ambil ID pertama untuk setiap key_short yang Valid
        $primaryIds = Event::valid()
            ->select(DB::raw('MIN(id::text) as id'))
            ->groupBy('key_short')
            ->pluck('id');

        // 3. Update baris-baris tersebut menjadi primary
        $count = 0;
        foreach ($primaryIds->chunk(500) as $chunk) {
            $count += Event::whereIn('id', $chunk)->update(['is_primary_row' => DB::raw('true')]);
        }

        ActivityLog::logActivity('update', null, "Recalculate Primary Rows: {$count} baris ditandai sebagai primary");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()
            ->title("Selesai: {$count} baris berhasil ditandai sebagai primary row.")
            ->success()
            ->send();
    }

    public function getAvailableYears(): array
    {
        return Event::selectRaw('EXTRACT(YEAR FROM tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }
}
