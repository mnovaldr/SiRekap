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
        Notification::make()->title("{$count} data review berhasil divalidasi")->success()->send();
    }

    // Hapus semua data (truncate)
    public function hapusSemuaData(): void
    {
        $count = Event::count();
        Event::truncate();
        ActivityLog::logActivity('delete', null, "Hapus SEMUA data ({$count} baris)");
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
        Notification::make()
            ->title("Selesai: {$dupCount} Duplikat, {$reviewCount} Review terdeteksi")
            ->success()
            ->send();
    }

    public function getAvailableYears(): array
    {
        return Event::selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }
}
