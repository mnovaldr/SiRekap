<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CleanEventData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcc:clean-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan data historis SDGs dan Sub Sektor Ekraf di tabel events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai proses pembersihan data...");

        $totalUpdated = 0;
        
        Event::chunk(500, function ($events) use (&$totalUpdated) {
            foreach ($events as $event) {
                $needsUpdate = false;
                
                // 1. Clean SDGs
                $cleanedSdgs = $this->cleanSdgs($event->program_sdgs);
                if ($event->program_sdgs !== $cleanedSdgs) {
                    $event->program_sdgs = $cleanedSdgs;
                    $needsUpdate = true;
                }
                
                // 2. Clean Sub Sektor
                $cleanedSubSektor = $this->cleanSubSektor($event->sub_sektor);
                if ($event->sub_sektor !== $cleanedSubSektor) {
                    $event->sub_sektor = $cleanedSubSektor;
                    $needsUpdate = true;
                }
                
                if ($needsUpdate) {
                    // Update timestamp is automatically updated by Eloquent save()
                    $event->save();
                    $totalUpdated++;
                }
            }
            $this->info("Diproses chunk... (Total diupdate sementara: $totalUpdated)");
        });

        $this->info("Pembersihan selesai! Total baris diperbarui: $totalUpdated");
    }

    private function cleanSdgs(mixed $raw): ?string
    {
        if ($raw === null) return null;
        $s = trim((string) $raw);
        if (in_array(strtolower($s), ['', '-', '0', 'tidak ada', 'null', 'n/a', 'nan'])) return null;

        // Pecah nilai multi
        $parts = preg_split('/[;,&]|\bdan\b|\band\b|(?=\bsdgs?\s*\d)/i', $s);
        $parts = array_filter(array_map('trim', $parts), fn($p) => $p !== '');

        if (empty($parts)) return null;

        $normalized = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^sdgs?\s*(\d{1,2})$/i', $part, $m)) {
                $num = (int) $m[1];
                $normalized[] = 'SDGS ' . $num;
                continue;
            }
            if (preg_match('/^\d{1,2}$/', $part)) {
                $normalized[] = 'SDGS ' . (int) $part;
                continue;
            }
            if (!empty($part)) {
                $normalized[] = $part;
            }
        }

        if (empty($normalized)) return null;

        $result = implode('; ', array_unique($normalized));
        return mb_substr($result, 0, 255);
    }

    private function cleanSubSektor(mixed $raw): ?string
    {
        if ($raw === null) return null;
        $s = strtolower(trim((string) $raw));
        if (in_array($s, ['', '-', '0', 'tidak ada', 'n/a', 'nan'])) return null;

        return match(true) {
            str_contains($s, 'aplikasi') => 'Aplikasi',
            str_contains($s, 'arsitektur') => 'Arsitektur',
            str_contains($s, 'interior') => 'Desain Interior',
            str_contains($s, 'dkv') || str_contains($s, 'komunikasi visual') => 'Desain Komunikasi Visual (DKV)',
            str_contains($s, 'produk') && !str_contains($s, 'produksi') => 'Desain Produk',
            str_contains($s, 'fashion') || str_contains($s, 'fesyen') => 'Fesyen',
            str_contains($s, 'film') || str_contains($s, 'animasi') || str_contains($s, 'video') => 'Film, Animasi, dan Video',
            str_contains($s, 'foto') => 'Fotografi',
            str_contains($s, 'kriya') || str_contains($s, 'kerajinan') => 'Kriya',
            str_contains($s, 'kulin') || str_contains($s, 'makan') || str_contains($s, 'minum') => 'Kuliner',
            str_contains($s, 'musik') => 'Musik',
            str_contains($s, 'otomotif') || str_contains($s, 'modifikasi') => 'Modifikasi Otomotif',
            str_contains($s, 'teknologi') || preg_match('/\b(ai|blockchain)\b/', $s) => 'Teknologi Baru (AI, Blockchain, dll.)',
            str_contains($s, 'konten') || str_contains($s, 'creator') || str_contains($s, 'live commerce') => 'Konten Digital (Content Creator, Live Commerce)',
            str_contains($s, 'sulih') || str_contains($s, 'suara') || str_contains($s, 'voice over') => 'Sulih Suara (Voice Over)',
            str_contains($s, 'terbit') => 'Penerbitan',
            str_contains($s, 'iklan') => 'Periklanan',
            str_contains($s, 'pertunjukan') || str_contains($s, 'tari') || str_contains($s, 'teater') => 'Seni Pertunjukan',
            str_contains($s, 'rupa') || str_contains($s, 'lukis') => 'Seni Rupa',
            str_contains($s, 'tv') || str_contains($s, 'televisi') || str_contains($s, 'radio') => 'Televisi dan Radio',
            str_contains($s, 'game') || str_contains($s, 'permainan') => 'Pengembangan Permainan (Game)',
            default => ucwords($s),
        };
    }
}
