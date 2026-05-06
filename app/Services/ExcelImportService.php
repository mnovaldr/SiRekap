<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    protected array $validMonths = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    protected array $monthMap = [
        'jan' => '01', 'januari' => '01',
        'feb' => '02', 'februari' => '02',
        'mar' => '03', 'maret' => '03',
        'apr' => '04', 'april' => '04',
        'mei' => '05', 'may' => '05',
        'jun' => '06', 'juni' => '06',
        'jul' => '07', 'juli' => '07',
        'agu' => '08', 'agustus' => '08', 'aug' => '08',
        'sep' => '09', 'september' => '09',
        'okt' => '10', 'oktober' => '10', 'oct' => '10',
        'nov' => '11', 'november' => '11',
        'des' => '12', 'desember' => '12', 'dec' => '12',
    ];

    /**
     * Parse Excel file and return raw data from all bulan sheets.
     */
    public function parseExcel(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            throw new \Exception('Format file tidak didukung. Harap gunakan file .xlsx atau .xls yang valid.');
        }
        $allData = [];
        $foundHeaders = [];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (in_array($sheetName, $this->validMonths)) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray(null, true, true, false);

                if (empty($rows)) continue;

                // First row is header
                $headers = array_map('trim', $rows[0] ?? []);
                if (empty(array_filter($headers))) continue;

                $foundHeaders = array_unique(array_merge($foundHeaders, $headers));

                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (empty(array_filter($row))) continue;
                    $mapped = [];
                    foreach ($headers as $idx => $header) {
                        if (empty($header)) continue;
                        $mapped[$header] = $row[$idx] ?? '';
                    }
                    $mapped['_sheet'] = $sheetName;
                    $allData[] = $mapped;
                }
            }
        }

        // Fallback: use first sheet if no bulan sheets found
        if (empty($allData)) {
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
            if (!empty($rows)) {
                $headers = array_map('trim', $rows[0]);
                $foundHeaders = $headers;
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (empty(array_filter($row))) continue;
                    $mapped = [];
                    foreach ($headers as $idx => $header) {
                        if (empty($header)) continue;
                        $mapped[$header] = $row[$idx] ?? '';
                    }
                    $allData[] = $mapped;
                }
            }
        }

        return [
            'data' => $allData,
            'headers' => array_values(array_filter($foundHeaders))
        ];
    }

    /**
     * Standardize raw Excel data to DB schema.
     *
     * Multi-lokasi/lantai DI-SPLIT menjadi baris terpisah untuk menghitung event per lokasi.
     * NAMUN jumlah_peserta hanya diisi di baris PERTAMA, baris berikutnya = 0.
     * Ini mencegah peserta terhitung ganda saat SUM(jumlah_peserta).
     *
     * Contoh:
     *   Excel: Workshop | 50 peserta | Studio Musik & Studio Foto
     *   Hasil: baris 1 → Studio Musik | 50 peserta
     *          baris 2 → Studio Foto  |  0 peserta  ← tidak dihitung ganda
     */
    public function standardize(array $rawData): array
    {
        $result = [];

        foreach ($rawData as $item) {
            $t = [];
            foreach ($item as $k => $v) {
                $t[trim((string) $k)] = is_string($v) ? trim($v) : $v;
            }

            $n = [];
            foreach ($t as $k => $v) {
                $nk = strtolower(preg_replace('/[\s\/]+/', '_', $k));
                $n[$nk] = $v;
            }

            // Tanggal
            $rawTgl      = $t['Tanggal'] ?? $t['Tanggal Kegiatan'] ?? $n['tanggal'] ?? $n['tanggal_kegiatan'] ?? '';
            $finalTanggal = $this->parseDate($rawTgl) ?? date('Y-m-d');

            // Peserta (hanya untuk baris pertama)
            $pesertaRaw    = $t['Jumlah Peserta'] ?? $n['jumlah_peserta'] ?? 0;
            $pesertaParsed = (int) preg_replace('/[^\d]/', '', (string) $pesertaRaw);

            // Lantai — split multi
            $rawLantai  = isset($t['Lantai']) ? (string) $t['Lantai'] : '2';
            $lantaiList = $this->splitMulti($rawLantai);
            $lantaiList = array_values(array_filter(
                array_map(fn($l) => in_array(trim($l), ['2', '3', '4', '5']) ? trim($l) : null, $lantaiList)
            ));
            if (empty($lantaiList)) $lantaiList = ['2'];

            // Lokasi — split multi
            $rawLokasi  = $t['Lokasi/ Ruangan'] ?? $t['Lokasi / Ruangan'] ?? $t['Lokasi'] ?? $n['lokasi__ruangan'] ?? $n['lokasi'] ?? '';
            $lokasiList = $this->splitMulti((string) $rawLokasi);
            $lokasiList = array_values(array_map([$this, 'cleanLokasi'], $lokasiList));
            if (empty($lokasiList)) $lokasiList = ['Ruang Meeting'];

            $commonData = [
                'nama_organisasi' => $this->cleanOrganisasi($t['Nama Organisasi / Komunitas'] ?? $t['Nama Organisasi/ Komunitas'] ?? $t['Organisasi'] ?? $t['Nama Organisasi'] ?? $n['nama_organisasi'] ?? 'Unknown'),
                'bidang'          => $this->cleanBidang($t['Bidang'] ?? $n['bidang'] ?? ''),
                'jenis_kegiatan'  => $this->cleanKegiatan($t['Jenis Kegiatan'] ?? $n['jenis_kegiatan'] ?? ''),
                'sub_sektor'      => ($t['Sub Sektor'] ?? $t['Subsektor'] ?? $n['sub_sektor'] ?? null) ?: null,
                'komersial'       => $this->cleanKomersial($t['Komersial/ Non Komersial'] ?? $t['Komersial'] ?? $n['komersial'] ?? ''),
                'judul_kegiatan'  => $t['Judul Kegiatan'] ?? $t['Event'] ?? $n['judul_kegiatan'] ?? 'Untitled Event',
                'tanggal'         => $finalTanggal,
                'email'           => ($t['Email'] ?? $t['email'] ?? $n['email'] ?? null) ?: null,
                'no_telepon'      => ($t['No. Telf'] ?? $t['Telepon'] ?? $t['No Telp'] ?? $n['no_telepon'] ?? null) ?: null,
                'pic'             => ($t['PIC'] ?? $n['pic'] ?? null) ?: null,
            ];

            // Cartesian product: lantai × lokasi
            // Setiap baris hasil split tetap memiliki jumlah_peserta penuh sesuai permintaan user
            $isFirst = true;
            foreach ($lantaiList as $lantai) {
                foreach ($lokasiList as $lokasi) {
                    $result[] = array_merge($commonData, [
                        'lantai'          => $lantai,
                        'lokasi'          => $lokasi,
                        'jumlah_peserta'  => $pesertaParsed,
                        'is_primary_row'  => $isFirst,
                    ]);
                    $isFirst = false;
                }
            }
        }

        return $result;
    }

    /**
     * Detect duplicates using KEY FULL and KEY SHORT logic.
     */
    public function detectDuplicates(array $data): array
    {
        $fullCounts = [];
        $shortCounts = [];
        $batchShortKeys = [];
        $batchFullKeys = [];

        // Pre-calculate keys for the entire batch
        foreach ($data as &$item) {
            $org   = strtoupper(trim((string) ($item['nama_organisasi'] ?? '')));
            $judul = strtoupper(trim((string) ($item['judul_kegiatan'] ?? '')));
            $tgl   = trim((string) ($item['tanggal'] ?? ''));
            $lok   = strtoupper(trim((string) ($item['lokasi'] ?? '')));
            $lan   = strtoupper(trim((string) ($item['lantai'] ?? '')));

            $item['_key_full']  = "{$org}||{$judul}||{$tgl}||{$lok}||{$lan}";
            $item['_key_short'] = "{$org}||{$judul}||{$tgl}";

            $fullCounts[$item['_key_full']]   = ($fullCounts[$item['_key_full']]   ?? 0) + 1;
            $shortCounts[$item['_key_short']] = ($shortCounts[$item['_key_short']] ?? 0) + 1;
            
            $batchFullKeys[] = $item['_key_full'];
            $batchShortKeys[] = $item['_key_short'];
        }
        unset($item);

        // Fetch existing keys from DB in BULK (only 2 queries total)
        $existingFullKeys = Event::whereIn('key_full', array_unique($batchFullKeys))->pluck('key_full')->toArray();
        $existingShortKeys = Event::whereIn('key_short', array_unique($batchShortKeys))->pluck('key_short')->toArray();
        
        $existingFullKeysMap = array_flip($existingFullKeys);
        $existingShortKeysMap = array_flip($existingShortKeys);

        $processedShortKeys = [];

        foreach ($data as &$item) {
            $ks = $item['_key_short'];
            $kf = $item['_key_full'];

            // 1. Check Full Duplicate (Identical Row)
            if ($fullCounts[$kf] > 1 || isset($existingFullKeysMap[$kf])) {
                $item['status_data']    = 'Duplikat';
                $item['catatan_review'] = 'Duplikat FULL';
                $item['is_primary_row'] = false;
            } 
            // 2. Check Short Duplicate (Multi Ruangan / Event Sama)
            elseif ($shortCounts[$ks] > 1 || isset($existingShortKeysMap[$ks])) {
                $item['status_data']    = 'Review';
                $item['catatan_review'] = 'Duplikat SHORT (Multi Ruangan)';
                
                // Jika sudah ada di DB atau sudah diproses di batch ini, maka bukan primary
                if (isset($existingShortKeysMap[$ks]) || isset($processedShortKeys[$ks])) {
                    $item['is_primary_row'] = false;
                } else {
                    $item['is_primary_row'] = true;
                    $processedShortKeys[$ks] = true;
                }
            } 
            // 3. Valid
            else {
                $item['status_data']    = 'Valid';
                $item['catatan_review'] = null;
                $item['is_primary_row'] = true;
                $processedShortKeys[$ks] = true;
            }

            $item['key_full']  = $kf;
            $item['key_short'] = $ks;
            unset($item['_key_full'], $item['_key_short']);
        }
        unset($item);

        return $data;
    }

    /**
     * Parse a date string or Excel serial to YYYY-MM-DD.
     */
    public function parseDate(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') return null;

        // Excel serial number
        if (is_numeric($raw)) {
            $serial = (int) $raw;
            if ($serial > 40000 && $serial < 70000) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($serial);
                    return $dt->format('Y-m-d');
                } catch (\Exception $e) {
                    // fall through
                }
            }
        }

        $str = trim((string) $raw);

        // Already ISO: 2024-04-01 or 2024-04-01 00:00:00
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $str, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        // Strip day-of-week prefix: "Kamis, 01 Februari 2024"
        $str = preg_replace('/^(senin|selasa|rabu|kamis|jumat|sabtu|minggu),?\s*/i', '', $str);

        $hasMonthName = false;
        foreach (array_keys($this->monthMap) as $mn) {
            if (str_contains(strtolower($str), $mn)) {
                $hasMonthName = true;
                break;
            }
        }

        if ($hasMonthName) {
            $str = str_replace('.', ' ', $str);
            $str = preg_replace('/\s+/', ' ', trim($str));
            if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})/', $str, $m)) {
                $day   = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $month = $this->monthMap[strtolower($m[2])] ?? null;
                $year  = $m[3];
                if ($month) return "{$year}-{$month}-{$day}";
            }
        }

        // Slash: DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
            $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            return "{$m[3]}-{$mo}-{$d}";
        }

        // Last resort: PHP date
        $ts = strtotime($str);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function splitMulti(string $val): array
    {
        $parts = preg_split('/[,&]|\bdan\b/i', $val);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    private function cleanBidang(mixed $raw): string
    {
        if (!$raw) return 'Komunitas';
        $b = strtolower(trim((string) $raw));
        return match(true) {
            $b === 'komunitas'           => 'Komunitas',
            $b === 'bisnis'              => 'Bisnis',
            $b === 'akademisi'           => 'Akademisi',
            $b === 'pemerintah'          => 'Pemerintah',
            $b === 'institusi finansial' => 'Institusi Finansial',
            $b === 'media'               => 'Media',
            $b === 'perorangan'          => 'Perorangan',
            $b === '0' || $b === ''      => 'Komunitas',
            default => ucwords(strtolower((string) $raw)),
        };
    }

    private function cleanKegiatan(mixed $raw): string
    {
        if (!$raw) return 'Lainnya';
        $k = strtolower(trim((string) $raw));
        return match(true) {
            in_array($k, ['pertunjukan', 'pertunjukkan']) => 'Pertunjukan',
            $k === 'meeting'              => 'Meeting',
            $k === 'workshop'             => 'Workshop',
            $k === 'pelatihan'            => 'Pelatihan',
            $k === 'talkshow/podcast'     => 'Talkshow/Podcast',
            $k === 'take konten foto & video' => 'Take Konten Foto & Video',
            $k === 'produksi musik'       => 'Produksi Musik',
            $k === 'pameran'              => 'Pameran',
            $k === 'kunjungan'            => 'Kunjungan',
            $k === 'sosialisasi'          => 'Sosialisasi',
            $k === 'kompetisi'            => 'Kompetisi',
            $k === 'produksi film'        => 'Produksi Film',
            $k === 'voice over'           => 'Voice Over',
            $k === 'interview'            => 'Interview',
            $k === 'penelitian'           => 'Penelitian',
            $k === 'produksi olahan'      => 'Produksi Olahan',
            $k === 'konsultasi'           => 'Konsultasi',
            default => 'Lainnya',
        };
    }

    private function cleanKomersial(mixed $raw): string
    {
        $k = strtolower(trim((string) $raw));
        if (str_contains($k, 'non') || $k === '') return 'Non Komersial';
        if (str_contains($k, 'komers') || str_contains($k, 'komesial')) return 'Komersial';
        return 'Non Komersial';
    }

    private function cleanLokasi(string $raw): string
    {
        $l = trim($raw);
        $ll = strtolower($l);
        return match(true) {
            $ll === 'studio musik'  => 'Studio Musik',
            $ll === 'studio foto'   => 'Studio Foto',
            in_array($ll, ['coworking space 1', 'co-working space 1', 'coworking space']) => 'Co-Working Space 1',
            in_array($ll, ['coworking space 2', 'co-working space 2']) => 'Co-Working Space 2',
            in_array($ll, ['food lab', 'foodlab']) => 'Food Lab',
            in_array($ll, ['multipurpose area', 'multi purpose area']) => 'Multi Purpose Area',
            $ll === 'amphitheater 1' => 'Amphitheater 1',
            $ll === 'amphitheater 2' => 'Amphitheater 2',
            $ll === 'multifunction room' => 'Multifunction Room',
            str_contains($ll, 'barat') && str_contains($ll, 'public') => 'Open Public Space Barat',
            str_contains($ll, 'utara') && str_contains($ll, 'public') => 'Open Public Space Utara',
            $ll === 'ruang meeting'  => 'Ruang Meeting',
            $ll === 'ruang fashion'  => 'Ruang Fashion',
            $ll === 'kidzone'        => 'KIDZONE',
            default => 'Ruang Meeting',
        };
    }

    private function cleanOrganisasi(mixed $raw): string
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
        
        // 5. Kembalikan ke Title Case untuk tampilan rapi
        return ucwords($s);
    }

    public function getBulanIndonesia(string $date): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[(int) date('n', strtotime($date))] ?? '';
    }
}
