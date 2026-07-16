<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

class MccEventSyncService
{
    /**
     * Melakukan login dinamis dan sinkronisasi data dari portal MCC Event.
     * 
     * @param string $username
     * @param string $password
     * @return array Status report (jumlah sukses, duplikat, gagal)
     */
    public function sync(string $username, string $password): array
    {
        Log::info("Memulai sinkronisasi MCC Event untuk user: {$username}");

        // 1. Inisialisasi HTTP Client dengan cookie jar
        $client = Http::withOptions([
            'cookies' => true,
            'verify' => false, // Abaikan SSL issue jika di localhost/internal network
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            ]
        ]);

        // 2. Kirim request Login (Simulasi POST Login Form)
        // Kita mencoba mem-POST ke endpoint login MCC yang lazim.
        // Jika website menggunakan proteksi atau dinonaktifkan di local, kita sediakan fallback simulator.
        $loginUrl = 'https://mcc.malangkota.go.id/login';
        
        try {
            // Coba login asli
            $loginResponse = $client->post($loginUrl, [
                'email' => $username,
                'username' => $username, // dukung multi field name
                'password' => $password,
            ]);

            // Jika server down atau 404/403, kita gunakan simulation mode untuk demo/development jika kredensial adalah demo
            if ($loginResponse->status() === 404 || $loginResponse->status() === 403 || $username === 'demo@mcc.go.id') {
                if ($username === 'demo@mcc.go.id' && $password === 'demo123') {
                    Log::info("Menggunakan mode demo simulasi sinkronisasi.");
                    return $this->simulateSync();
                }
                throw new Exception("Koneksi ke portal MCC Event ditolak (HTTP " . $loginResponse->status() . "). Pastikan URL login aktif.");
            }

            if ($loginResponse->failed()) {
                throw new Exception("Autentikasi gagal. Username atau password MCC salah.");
            }

            // 3. Tarik data jadwal dari halaman jadwalku
            $scheduleUrl = 'https://mcc.malangkota.go.id/jadwalku/';
            $scheduleResponse = $client->get($scheduleUrl);

            if ($scheduleResponse->failed()) {
                throw new Exception("Gagal mengakses halaman jadwal (HTTP " . $scheduleResponse->status() . ")");
            }

            $html = $scheduleResponse->body();
            return $this->parseAndInsertHtml($html);

        } catch (Exception $e) {
            Log::error("Error sinkronisasi MCC: " . $e->getMessage());
            // Fallback ke simulasi jika username & password bernilai demo untuk keperluan presentasi
            if ($username === 'demo@mcc.go.id' && $password === 'demo123') {
                return $this->simulateSync();
            }
            throw $e;
        }
    }

    /**
     * Parsing HTML halaman jadwalku dan simpan ke database.
     */
    protected function parseAndInsertHtml(string $html): array
    {
        $crawler = new Crawler($html);
        $imported = 0;
        $duplicates = 0;
        $failed = 0;

        // Cari baris tabel atau kartu event. Kita asumsikan selektor umum tabel/card.
        // Jika website MCC Event menggunakan selektor tertentu, ini bisa disesuaikan.
        $events = [];

        // Deteksi baris tabel (contoh: table tr atau div.event-card)
        $crawler->filter('table tr, .event-card, .jadwal-item')->each(function (Crawler $node) use (&$events) {
            // Extractor sederhana (mencari teks kolom)
            try {
                $cells = $node->filter('td');
                if ($cells->count() >= 5) {
                    $events[] = [
                        'nama_organisasi' => trim($cells->eq(1)->text()),
                        'bidang'          => trim($cells->eq(2)->text()),
                        'sub_sektor'      => trim($cells->eq(3)->text()),
                        'judul_kegiatan'  => trim($cells->eq(4)->text()),
                        'tanggal'         => trim($cells->eq(5)->text()),
                        'lokasi'          => $cells->count() >= 7 ? trim($cells->eq(6)->text()) : 'MCC Hall',
                        'lantai'          => $cells->count() >= 8 ? trim($cells->eq(7)->text()) : '3',
                    ];
                }
            } catch (Exception $e) {
                // baris header atau format aneh, skip
            }
        });

        if (empty($events)) {
            // Coba parsing alternatif menggunakan card
            $crawler->filter('.card, .event-item')->each(function (Crawler $node) use (&$events) {
                try {
                    $title = $node->filter('.card-title, .title, h3, h4')->first()->text();
                    $organizer = $node->filter('.organizer, .komunitas, .author')->first()->text();
                    $date = $node->filter('.date, .tanggal, time')->first()->text();
                    $location = $node->filter('.location, .ruangan, .tempat')->first()->text();

                    $events[] = [
                        'nama_organisasi' => trim($organizer),
                        'bidang'          => 'Komunitas',
                        'sub_sektor'      => 'Lainnya',
                        'judul_kegiatan'  => trim($title),
                        'tanggal'         => trim($date),
                        'lokasi'          => trim($location),
                        'lantai'          => '3',
                    ];
                } catch (Exception $e) {
                    // skip jika elemen tidak lengkap
                }
            });
        }

        if (empty($events)) {
            throw new Exception("Tidak ada data event yang berhasil diekstrak dari halaman. Silakan periksa struktur HTML website.");
        }

        foreach ($events as $rawEvent) {
            $status = $this->processAndInsertEvent($rawEvent);
            if ($status === 'success') $imported++;
            elseif ($status === 'duplicate') $duplicates++;
            else $failed++;
        }

        return [
            'imported' => $imported,
            'duplicates' => $duplicates,
            'failed' => $failed
        ];
    }

    /**
     * Memproses, menormalisasi, dan memasukkan data event ke database.
     */
    protected function processAndInsertEvent(array $raw): string
    {
        try {
            // Normalisasi tanggal (Coba parse format Indonesia)
            $tanggal = $this->parseIndonesianDate($raw['tanggal']);
            if (!$tanggal) {
                return 'failed';
            }

            // Inisialisasi Model Event
            $event = new Event();
            $event->nama_organisasi = Event::toTitleCaseWithAbbreviations($raw['nama_organisasi']);
            $event->bidang = $raw['bidang'] ?: 'Komunitas';
            $event->sub_sektor = $raw['sub_sektor'] ?: 'Lainnya';
            $event->judul_kegiatan = Event::toTitleCaseWithAbbreviations($raw['judul_kegiatan']);
            $event->tanggal = $tanggal;
            $event->bulan = Event::getBulanIndonesia($tanggal);
            $event->lantai = $raw['lantai'] ?: '3';
            $event->lokasi = Event::toTitleCaseWithAbbreviations($raw['lokasi']);
            $event->komersial = 'Non Komersial'; // default
            $event->status_data = 'Valid'; // auto approved / Valid
            $event->is_primary_row = true;

            // Generate Keys
            $event->key_full = Event::makeKeyFull($event);
            $event->key_short = Event::makeKeyShort($event);

            // Cek Duplikat
            $fullDuplicate = Event::where('key_full', $event->key_full)->exists();
            if ($fullDuplicate) {
                return 'duplicate';
            }

            $event->save();
            return 'success';

        } catch (Exception $e) {
            Log::error("Gagal menyimpan event sinkronisasi: " . $e->getMessage());
            return 'failed';
        }
    }

    /**
     * Helper untuk parse tanggal Indonesia (e.g. "14 Juli 2026") menjadi "2026-07-14".
     */
    protected function parseIndonesianDate(string $rawDate): ?string
    {
        $rawDate = strtolower(trim($rawDate));
        
        $months = [
            'januari' => '01', 'jan' => '01',
            'februari' => '02', 'feb' => '02',
            'maret' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05',
            'juni' => '06', 'jun' => '06',
            'juli' => '07', 'jul' => '07',
            'agustus' => '08', 'agu' => '08', 'ags' => '08',
            'september' => '09', 'sep' => '09',
            'oktober' => '10', 'okt' => '10',
            'november' => '11', 'nov' => '11',
            'desember' => '12', 'des' => '12',
        ];

        // Format umum: "14 juli 2026" atau "14-07-2026"
        if (preg_match('/(\d{1,2})\s+([a-z]+)\s+(\d{4})/', $rawDate, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthWord = $matches[2];
            $year = $matches[3];

            $month = $months[$monthWord] ?? '01';
            return "{$year}-{$month}-{$day}";
        }

        // Coba parsing standar PHP
        $timestamp = strtotime($rawDate);
        if ($timestamp) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Simulasi Sinkronisasi untuk Keperluan Demo/Development.
     */
    protected function simulateSync(): array
    {
        // Menyusun 5 event sampel untuk disinkronisasi
        $sampleEvents = [
            [
                'nama_organisasi' => 'Dinas Koperasi dan Ekraf Malang',
                'bidang' => 'Pemerintah',
                'sub_sektor' => 'Desain Grafis',
                'judul_kegiatan' => 'Pameran Karya Poster SDGs',
                'tanggal' => date('Y-m-d'),
                'lokasi' => 'Auditorium MCC',
                'lantai' => '7',
            ],
            [
                'nama_organisasi' => 'Malang Game Dev Community',
                'bidang' => 'Komunitas',
                'sub_sektor' => 'Aplikasi & Game Developer',
                'judul_kegiatan' => 'Game Jam Ekraf Indonesia',
                'tanggal' => date('Y-m-d', strtotime('+1 day')),
                'lokasi' => 'Co-Working Space',
                'lantai' => '3',
            ],
            [
                'nama_organisasi' => 'Kriya Kayu Arema',
                'bidang' => 'Bisnis',
                'sub_sektor' => 'Kriya',
                'judul_kegiatan' => 'Workshop Kerajinan Kayu Pinus',
                'tanggal' => date('Y-m-d', strtotime('+2 days')),
                'lokasi' => 'Lobby Utama',
                'lantai' => '1',
            ],
            [
                'nama_organisasi' => 'Universitas Brawijaya',
                'bidang' => 'Akademisi',
                'sub_sektor' => 'Pendidikan',
                'judul_kegiatan' => 'Kuliah Tamu Pembangunan Berkelanjutan',
                'tanggal' => date('Y-m-d', strtotime('-1 day')),
                'lokasi' => 'Bioskop Mini',
                'lantai' => '5',
            ]
        ];

        $imported = 0;
        $duplicates = 0;
        $failed = 0;

        foreach ($sampleEvents as $raw) {
            $status = $this->processAndInsertEvent($raw);
            if ($status === 'success') $imported++;
            elseif ($status === 'duplicate') $duplicates++;
            else $failed++;
        }

        return [
            'imported' => $imported,
            'duplicates' => $duplicates,
            'failed' => $failed
        ];
    }
}
