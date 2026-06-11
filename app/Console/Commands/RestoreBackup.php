<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

class RestoreBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:restore-backup {file : The filename inside Supabase/local backups folder or absolute path to .enc file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrypt and restore events from an encrypted backup file (.enc)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $encryptedContent = null;

        // 1. Cari file di local storage (folder storage/app/backups)
        if (Storage::disk('local')->exists('backups/' . $file)) {
            $encryptedContent = Storage::disk('local')->get('backups/' . $file);
            $this->info("Menemukan berkas cadangan di penyimpanan lokal (storage/app/backups).");
        } 
        // 2. Jika tidak ada di local, coba cari langsung di path eksternal/absolut
        elseif (file_exists($file)) {
            $encryptedContent = file_get_contents($file);
            $this->info("Menemukan berkas cadangan di path eksternal.");
        }
        // 3. Jika tidak ada, coba download dari Supabase Storage
        else {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_KEY');

            if ($supabaseUrl && $supabaseKey) {
                $downloadUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/backups/' . $file;
                $this->info("Mencoba mengunduh berkas cadangan dari Supabase Storage...");
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'ApiKey'        => $supabaseKey,
                ])->get($downloadUrl);

                if ($response->successful()) {
                    $encryptedContent = $response->body();
                    $this->info("Berkas cadangan berhasil diunduh dari Supabase Storage.");
                }
            }
        }

        if (!$encryptedContent) {
            $this->error("Gagal menemukan atau mengunduh berkas cadangan: {$file}");
            return 1;
        }

        // 4. Dekripsi berkas menggunakan APP_KEY (ISO 27001 Security)
        try {
            $jsonContent = Crypt::decryptString($encryptedContent);
            $events = json_decode($jsonContent, true);
        } catch (\Exception $e) {
            $this->error("Gagal mendekripsi berkas. Pastikan berkas cadangan valid dan APP_KEY aplikasi tidak berubah!");
            $this->error("Detail Error: " . $e->getMessage());
            return 1;
        }

        if (!is_array($events)) {
            $this->error("Konten berkas hasil dekripsi bukan format data event yang valid.");
            return 1;
        }

        $count = count($events);
        $this->info("Mulai memulihkan {$count} data event ke database...");

        // 5. Masukkan data kembali ke database menggunakan transaksi
        try {
            DB::transaction(function () use ($events) {
                foreach ($events as $eventData) {
                    // Konversi tanggal jika ada
                    if (isset($eventData['tanggal'])) {
                        $eventData['tanggal'] = date('Y-m-d', strtotime($eventData['tanggal']));
                    }
                    
                    // Konversi boolean agar kompatibel dengan PostgreSQL
                    if (isset($eventData['is_primary_row'])) {
                        $eventData['is_primary_row'] = filter_var($eventData['is_primary_row'], FILTER_VALIDATE_BOOLEAN) ? DB::raw('true') : DB::raw('false');
                    }

                    // Gunakan updateOrInsert agar aman dari duplikasi primary key
                    DB::table('events')->updateOrInsert(
                        ['id' => $eventData['id']],
                        $eventData
                    );
                }
            });
        } catch (\Exception $e) {
            $this->error("Gagal menulis data ke database: " . $e->getMessage());
            return 1;
        }

        // 6. Catat log pemulihan ke audit trail
        ActivityLog::create([
            'action'      => 'restore',
            'model_type'  => null,
            'model_id'    => null,
            'user_id'     => auth()->id(),
            'ip_address'  => request()->ip() ?: '127.0.0.1',
            'user_agent'  => request()->userAgent() ?: 'Console Command',
            'description' => "System memulihkan secara permanen sejumlah {$count} data event dari berkas cadangan: {$file}",
            'properties'  => [
                'nama_file'         => $file,
                'jumlah_dipulihkan' => $count,
            ]
        ]);

        \Illuminate\Support\Facades\Cache::flush();

        $this->info("Sukses! {$count} data event berhasil dipulihkan ke database.");
        return 0;
    }
}
