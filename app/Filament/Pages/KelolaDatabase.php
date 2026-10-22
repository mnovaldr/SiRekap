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
        Event::duplikat()->update([
            'status_data' => 'Dihapus',
            'alasan_dihapus' => 'Pembersihan database: Hapus data Duplikat'
        ]);
        ActivityLog::logActivity('delete', null, "Hapus {$count} data Duplikat (Status)");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("{$count} data duplikat berhasil dipindahkan ke Sampah")->success()->send();
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

    // Hapus semua data (Ubah Status ke Dihapus)
    public function hapusSemuaData(): void
    {
        $count = Event::where('status_data', '!=', 'Dihapus')->count();
        if ($count === 0) {
            Notification::make()->title('Tidak ada data aktif yang dapat dihapus')->warning()->send();
            return;
        }
        Event::where('status_data', '!=', 'Dihapus')->update([
            'status_data' => 'Dihapus',
            'alasan_dihapus' => 'Pembersihan database: Hapus semua data'
        ]);
        ActivityLog::logActivity('delete', null, "Hapus SEMUA data aktif ({$count} baris) (Status)");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("Semua data aktif ({$count} baris) berhasil dipindahkan ke Sampah")->success()->send();
    }

    // Hapus data berdasarkan tahun
    public function hapusDataTahun(int $year): void
    {
        $count = Event::whereYear('tanggal', $year)->where('status_data', '!=', 'Dihapus')->count();
        if ($count === 0) {
            Notification::make()->title("Tidak ada data aktif tahun {$year}")->warning()->send();
            return;
        }
        Event::whereYear('tanggal', $year)->where('status_data', '!=', 'Dihapus')->update([
            'status_data' => 'Dihapus',
            'alasan_dihapus' => "Pembersihan database: Hapus data tahun {$year}"
        ]);
        ActivityLog::logActivity('delete', null, "Hapus {$count} data tahun {$year} (Status)");
        \Illuminate\Support\Facades\Cache::flush();
        Notification::make()->title("{$count} data tahun {$year} berhasil dipindahkan ke Sampah")->success()->send();
    }

    // Hapus activity logs (Dinonaktifkan demi keamanan)
    public function hapusActivityLog(): void
    {
        Notification::make()->title("Log aktivitas tidak dapat dihapus demi keamanan data!")->danger()->send();
    }

    // Hapus PERMANEN semua data (termasuk yang sudah Dihapus) berdasarkan tahun
    // Digunakan untuk membersihkan data import yang salah (misal: tanggal 2026 dari bug parsing)
    public function forceDeleteByYear(int $year): void
    {
        // Hitung total data yang akan dihapus (termasuk Dihapus)
        $count = DB::table('events')
            ->whereRaw('EXTRACT(YEAR FROM tanggal)::integer = ?', [$year])
            ->count();

        if ($count === 0) {
            Notification::make()->title("Tidak ada data tahun {$year}")->warning()->send();
            return;
        }

        // 1. Ambil data mentah yang akan dihapus untuk dibackup
        $rawEvents = DB::table('events')
            ->whereRaw('EXTRACT(YEAR FROM tanggal)::integer = ?', [$year])
            ->get();
        
        // 2. Konversi ke JSON dan Enkripsi (ISO 27001 Security)
        $jsonContent = $rawEvents->toJson();
        $encryptedContent = \Illuminate\Support\Facades\Crypt::encryptString($jsonContent);
        
        // 3. Generate Nama Berkas Cadangan
        $fileName = "pemusnahan_{$year}_" . time() . ".enc";
        
        // 4. Periksa kredensial Supabase (gunakan config agar tetap berjalan saat config:cache aktif)
        $supabaseUrl = config('services.supabase.url');
        $supabaseKey = config('services.supabase.key');
        $storageMethod = "Lokal (Offline Fallback)";
        
        if ($supabaseUrl && $supabaseKey) {
            try {
                // Endpoint API Supabase Storage Upload
                $uploadUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/backups/' . $fileName;

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'ApiKey'        => $supabaseKey,
                ])->withBody($encryptedContent, 'application/octet-stream')
                  ->post($uploadUrl);

                if ($response->successful()) {
                    $storageMethod = "Cloud (Supabase Storage: backups/{$fileName})";
                } else {
                    // Fallback ke penyimpanan lokal jika upload ke Supabase gagal
                    \Illuminate\Support\Facades\Storage::disk('local')->put('backups/' . $fileName, $encryptedContent);
                    $storageMethod = "Lokal (Fallback karena Upload Gagal: HTTP " . $response->status() . ")";
                }
            } catch (\Exception $e) {
                // Fallback ke penyimpanan lokal jika ada kesalahan jaringan
                \Illuminate\Support\Facades\Storage::disk('local')->put('backups/' . $fileName, $encryptedContent);
                $storageMethod = "Lokal (Fallback karena Error Koneksi: " . $e->getMessage() . ")";
            }
        } else {
            // Penyimpanan lokal jika tidak ada kredensial
            \Illuminate\Support\Facades\Storage::disk('local')->put('backups/' . $fileName, $encryptedContent);
        }

        // 5. Dapatkan user aktif
        $user = auth()->user();
        $userName = $user ? $user->name : 'System';
        $userId = $user ? $user->id : 0;
        
        // 6. Generate Nomor Berita Acara Pemusnahan (Urutan unik berbasis count log force_delete)
        $currentYear = date('Y');
        $baCount = DB::table('activity_logs')
            ->where('action', 'force_delete')
            ->count() + 1;
        $baNumber = sprintf("MCC-BA-%s-%03d", $currentYear, $baCount);
        
        // 7. Format timestamp pemusnahan
        $timestamp = now()->format('d-m-Y H:i:s');
        
        // 8. Buat pesan deskripsi log tanpa menyertakan konten data sensitif
        $logDescription = "User ID: {$userId} ({$userName}) memusnahkan secara permanen {$count} record data event Tahun {$year} pada {$timestamp}. Nomor Berita Acara Pemusnahan: {$baNumber}.";

        // 9. Hapus permanen dari database (bypass model events dengan DB::table)
        DB::table('events')
            ->whereRaw('EXTRACT(YEAR FROM tanggal)::integer = ?', [$year])
            ->delete();

        // 10. Catat ke Activity Log dengan metadata ringkasan saja (tidak ada data sensitif)
        ActivityLog::create([
            'action'      => 'force_delete',
            'model_type'  => null,
            'model_id'    => null,
            'user_id'     => $userId ?: null,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'description' => $logDescription,
            'properties'  => [
                'tahun_data'         => $year,
                'jumlah_dimusnahkan' => $count,
                'nomor_berita_acara' => $baNumber,
                'tanggal_pemusnahan' => $timestamp,
                'metode_cadangan'    => $storageMethod,
                'nama_file_cadangan' => $fileName,
            ],
        ]);

        \Illuminate\Support\Facades\Cache::flush();

        Notification::make()
            ->title("{$count} data tahun {$year} berhasil dihapus permanen. BA: {$baNumber}")
            ->success()
            ->send();
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
        // Reset semua yang bukan Dihapus ke Valid dulu
        Event::query()->where('status_data', '!=', 'Dihapus')->update(['status_data' => 'Valid', 'catatan_review' => null]);

        // Hitung key_full duplicates (abaikan data Dihapus)
        $fullDups = DB::table('events')
            ->where('status_data', '!=', 'Dihapus')
            ->select('key_full', DB::raw('count(*) as cnt'))
            ->groupBy('key_full')
            ->having('cnt', '>', 1)
            ->pluck('cnt', 'key_full');

        // Hitung key_short duplicates (abaikan data Dihapus)
        $shortDups = DB::table('events')
            ->where('status_data', '!=', 'Dihapus')
            ->select('key_short', DB::raw('count(*) as cnt'))
            ->groupBy('key_short')
            ->having('cnt', '>', 1)
            ->pluck('cnt', 'key_short');

        $dupCount = 0;
        $reviewCount = 0;

        // Mark duplikat full (abaikan data Dihapus)
        foreach ($fullDups->keys() as $keyFull) {
            $cnt = Event::where('key_full', $keyFull)
                ->where('status_data', '!=', 'Dihapus')
                ->update(['status_data' => 'Duplikat', 'catatan_review' => 'Duplikat FULL']);
            $dupCount += $cnt;
        }

        // Mark review (short dup, bukan full dup, abaikan data Dihapus)
        foreach ($shortDups->keys() as $keyShort) {
            $cnt = Event::where('key_short', $keyShort)
                ->where('status_data', 'Valid') // jangan overwrite Duplikat dan abaikan Dihapus
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
