<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CleanOrganizationNames extends Command
{
    protected $signature = 'mcc:clean-orgs';
    protected $description = 'Clean and standardize organization names in the events table';

    public function handle()
    {
        $this->info('Standardizing organization names...');
        
        $total = Event::count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Event::chunk(500, function ($events) use ($bar) {
            foreach ($events as $event) {
                $oldName = $event->nama_organisasi;
                $newName = $this->normalize($oldName);

                if ($oldName !== $newName) {
                    // Update secara langsung tanpa memicu event model jika memungkinkan untuk kecepatan
                    DB::table('events')->where('id', $event->id)->update([
                        'nama_organisasi' => $newName
                    ]);
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Cleanup completed successfully!');
    }

    private function normalize($raw)
    {
        if (!$raw) return 'Unknown';
        
        $s = trim((string) $raw);
        $s = strtolower($s);

        // 1. Standarisasi Singkatan Sekolah & Instansi
        $s = preg_replace('/\bsman\b|\bsma\b/i', 'SMAN', $s);
        $s = preg_replace('/\bsmkn\b|\bsmk\b/i', 'SMKN', $s);
        $s = preg_replace('/\buniv\b/i', 'Universitas', $s);
        
        // 2. Bersihkan karakter spesial yang tidak perlu
        $s = str_replace(['.', ',', '(', ')', '-'], ' ', $s);
        
        // 3. Normalisasi Prefiks (PT, CV, dll)
        $s = preg_replace('/^(pt|cv|yayasan|komunitas|perkumpulan|himpunan|hmj|hmt|hima)\s+/i', '', $s);
        
        // 4. Hapus spasi ganda
        $s = preg_replace('/\s+/', ' ', $s);
        $s = trim($s);
        
        // 5. Kembalikan ke Title Case
        return ucwords($s);
    }
}
