<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

/**
 * Excel Import Service
 * 
 * Service ini menangani import data Excel dari berbagai tahun dengan format yang berbeda:
 * 
 * TAHUN 2023:
 * - Kolom: No, Nama Organisasi, Bidang, Kegiatan, Sub Sektor, Komersial, 
 *   Judul Kegiatan, Tanggal, Lantai, Lokasi, Jumlah Peserta
 * - TIDAK ADA: Email, No. Telf, PIC, Program SDGS
 * 
 * TAHUN 2024:
 * - Kolom: No, Nama Organisasi, Bidang, Jenis Kegiatan, Sub Sektor, Komersial,
 *   Judul Kegiatan, Tanggal, Lantai, Lokasi, Jumlah Peserta, Email, No. Telf, PIC
 * - TIDAK ADA: Program SDGS
 * 
 * TAHUN 2025:
 * - Kolom: No, Nama Organisasi, Bidang, Jenis Kegiatan, Sub Sektor, Komersial,
 *   Program SDGS (BARU), Judul Kegiatan, Tanggal, Lantai, Lokasi, 
 *   Jumlah Peserta, PIC, No. Telf, Email
 * 
 * SISTEM FLEKSIBEL:
 * - Menggunakan resolveColumns() dengan alias mapping untuk menangani perbedaan nama kolom
 * - Kolom yang tidak ada di file lama otomatis diisi NULL tanpa error
 * - Kolom baru di tahun berikutnya otomatis dipetakan jika ada
 * - Memory-efficient: chunk reading untuk file besar (2025: 12 sheet)
 */

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
     *
     * Mendukung format header multi-baris (merged cells) yang digunakan di semua tahun:
     * - Baris 1: header grup (misal "Kategori Pelaku")
     * - Baris 2: sub-header (misal "Bidang", "Jenis Kegiatan", dst)
     * - Baris 3+: data
     *
     * Juga mendukung kolom baru yang ditambahkan di tahun berikutnya (misal Program SDGS 2025)
     * tanpa menyebabkan error pada file tahun sebelumnya.
     */
    public function parseExcel(string $filePath): array
    {
        // Naikkan memory limit untuk proses import file besar (2025)
        $prevMemLimit = ini_get('memory_limit');
        ini_set('memory_limit', '2048M'); // Naikkan dari 1024M ke 2048M untuk file 2025 yang besar

        // Konfigurasi PhpSpreadsheet untuk hemat memory
        \PhpOffice\PhpSpreadsheet\Settings::setCache(
            new \PhpOffice\PhpSpreadsheet\Collection\Memory\SimpleCache3()
        );

        $allData      = [];
        $foundHeaders = [];

        try {
            // ── Tahap 1: Load spreadsheet (sekali saja) ──
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheetNames  = $spreadsheet->getSheetNames();

            // ── Tahap 2: Load setiap sheet bulan dari memory ──
            $validMonthsLower = array_map('strtolower', $this->validMonths);
            $bulanSheets = [];
            foreach ($sheetNames as $originalName) {
                $trimmedName = trim($originalName);
                if (in_array(strtolower($trimmedName), $validMonthsLower)) {
                    $bulanSheets[$trimmedName] = $originalName; // Map trimmed to original
                }
            }
            
            Log::info("Import Excel: Ditemukan " . count($bulanSheets) . " sheet bulan", [
                'sheets' => array_values($bulanSheets),
                'file' => basename($filePath)
            ]);

            foreach ($bulanSheets as $trimmedName => $originalName) {
                $sheetName = $originalName;
                $trimmedSheetName = $trimmedName;
                try {
                    Log::info("Memproses sheet: {$sheetName} (trimmed: {$trimmedSheetName})");
                    
                    $sheet = $spreadsheet->getSheetByName($sheetName);

                    if (!$sheet) {
                        continue;
                    }

                    // Dapatkan dimensi sheet
                    $highestRow = $sheet->getHighestRow();
                    $highestCol = $sheet->getHighestColumn();
                    
                    // Convert column letter to number untuk iterasi
                    $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
                    
                    // Baca hanya 10 baris pertama untuk deteksi header (hemat memory)
                    $headerSample = [];
                    for ($i = 1; $i <= min(10, $highestRow); $i++) {
                        $rowData = [];
                        // Iterasi dengan index number, bukan range()
                        for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                            $rowData[] = $sheet->getCell($colLetter . $i)->getValue();
                        }
                        $headerSample[] = $rowData;
                    }

                    [$headerRow, $dataStartRow, $parentRow] = $this->detectHeaderRow($headerSample);
                    
                    if ($headerRow === null) {
                        Log::warning("Sheet [{$sheetName}]: Header tidak ditemukan, skip sheet");
                        unset($headerSample);
                        continue;
                    }

                    $rawHeaders = $headerSample[$headerRow];

                    // Jika ada baris parent (format multi-baris header), gabungkan semuanya
                    // mergeHeaderRows() menerima baris dari atas ke bawah (parent → sub)
                    // Sub-header (baris terakhir) menimpa parent untuk kolom yang sama
                    if ($parentRow !== null && isset($headerSample[$parentRow])) {
                        // Pass: parent row dulu, lalu sub-header row
                        $rawHeaders = $this->mergeHeaderRows(
                            $headerSample[$parentRow], // Row atas: Nama Org, Judul, Tanggal, dll.
                            $headerSample[$headerRow]  // Row bawah: Bidang, Jenis Kegiatan, dll. (menimpa)
                        );
                        $blockSize = $headerRow - $parentRow + 1;
                        Log::info("Sheet [{$sheetName}]: Format {$blockSize}-baris header (parent baris " . ($parentRow+1) . ", sub baris " . ($headerRow+1) . ")");
                    } else {
                        Log::info("Sheet [{$sheetName}]: Format 1-baris header (baris " . ($headerRow+1) . ")");
                    }

                    $headers = array_map('trim', $rawHeaders);
                    $foundHeaders = array_unique(array_merge($foundHeaders, array_filter($headers)));
                    
                    // KRITIS: $dataStartRow adalah indeks 0-based dalam $headerSample
                    // (headerSample[0] = sheet row 1, headerSample[1] = sheet row 2, dst.)
                    // Saat membaca sel sheet, kita butuh 1-based row number → tambah 1
                    $sheetDataStartRow = $dataStartRow + 1;
                    
                    Log::info("Sheet [{$sheetName}]: Kolom terdeteksi: " . implode(', ', array_slice(array_filter($headers), 0, 10)) . (count(array_filter($headers)) > 10 ? '...' : ''));
                    Log::info("Sheet [{$sheetName}]: Data mulai baris {$sheetDataStartRow}");

                    // Baca data baris per baris (STREAMING - hemat memory)
                    $rowCount = 0;
                    for ($rowNum = $sheetDataStartRow; $rowNum <= $highestRow; $rowNum++) {
                        $rowData = [];
                        $hasData = false;
                        
                        // Iterasi dengan index number, bukan range()
                        for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                            $value = $sheet->getCell($colLetter . $rowNum)->getValue();
                            $rowData[] = $value;
                            if ($value !== null && $value !== '') {
                                $hasData = true;
                            }
                        }

                        if (!$hasData) continue;

                        $mapped = [];
                        foreach ($headers as $idx => $header) {
                            if (empty($header)) continue;
                            $mapped[$header] = $rowData[$idx] ?? null;
                        }
                        $mapped['_sheet'] = $trimmedSheetName;
                        $allData[] = $mapped;
                        $rowCount++;

                        // Garbage collection setiap 100 baris
                        if ($rowCount % 100 === 0) {
                            gc_collect_cycles();
                        }
                    }

                    unset($headers, $headerSample);
                    gc_collect_cycles();
                    
                    Log::info("Sheet [{$sheetName}] selesai: {$rowCount} baris data");
                    
                    // Log sample data pertama untuk debugging
                    if ($rowCount > 0) {
                        $sampleData = array_filter($allData, fn($d) => ($d['_sheet'] ?? '') === $trimmedSheetName);
                        if (!empty($sampleData)) {
                            $firstRow = reset($sampleData);
                            Log::info("Sheet [{$sheetName}] sample data:", [
                                'nama_organisasi' => $firstRow['Nama Organisasi / Komunitas'] ?? $firstRow['Nama Organisasi'] ?? 'N/A',
                                'bidang' => $firstRow['Bidang'] ?? 'N/A',
                                'judul' => $firstRow['Judul Kegiatan'] ?? 'N/A',
                                'program_sdgs' => $firstRow['Program SDGS'] ?? $firstRow['Program SDGs'] ?? 'N/A',
                            ]);
                        }
                    }

                } catch (\Throwable $sheetErr) {
                    Log::error("Gagal baca sheet [{$sheetName}]: " . $sheetErr->getMessage(), [
                        'trace' => $sheetErr->getTraceAsString()
                    ]);
                    continue;
                }
            }

            // ── Fallback: load sheet aktif jika tidak ada sheet bulan ──
            if (empty($allData)) {
                Log::info("Tidak ada sheet bulan, menggunakan sheet aktif");
                
                $sheet = $spreadsheet->getActiveSheet();
                $sheetName = trim($sheet->getTitle());
                
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                
                // Convert column letter to number
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
                
                // Baca header sample
                $headerSample = [];
                for ($i = 1; $i <= min(10, $highestRow); $i++) {
                    $rowData = [];
                    for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                        $rowData[] = $sheet->getCell($colLetter . $i)->getValue();
                    }
                    $headerSample[] = $rowData;
                }

                [$headerRow, $dataStartRow, $parentRow] = $this->detectHeaderRow($headerSample);
                $headerRow    = $headerRow ?? 0;
                $dataStartRow = $dataStartRow ?? 1;
                
                $rawHeaders = $headerSample[$headerRow];
                if ($parentRow !== null && isset($headerSample[$parentRow])) {
                    $rawHeaders = $this->mergeHeaderRows(
                        $headerSample[$parentRow],
                        $headerSample[$headerRow]
                    );
                }
                $headers      = array_map('trim', $rawHeaders);
                $foundHeaders = $headers;

                // KRITIS: Sama seperti di atas, tambah 1 untuk konversi ke 1-based sheet row
                $sheetDataStartRow = $dataStartRow + 1;

                // Baca data baris per baris (STREAMING)
                for ($rowNum = $sheetDataStartRow; $rowNum <= $highestRow; $rowNum++) {
                    $rowData = [];
                    $hasData = false;
                    
                    foreach (range('A', $highestCol) as $col) {
                        $value = $sheet->getCell($col . $rowNum)->getValue();
                        $rowData[] = $value;
                        if ($value !== null && $value !== '') {
                            $hasData = true;
                        }
                    }

                    if (!$hasData) continue;

                    $mapped = [];
                    foreach ($headers as $idx => $header) {
                        if (empty($header)) continue;
                        $mapped[$header] = $rowData[$idx] ?? null;
                    }
                    $mapped['_sheet'] = $sheetName;
                    $allData[] = $mapped;

                    // Garbage collection setiap 100 baris
                    if ($rowNum % 100 === 0) {
                        gc_collect_cycles();
                    }
                }
                unset($headers, $headerSample);
                gc_collect_cycles();
            }

            // Disconnect worksheets to free memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            gc_collect_cycles();

        } catch (\Throwable $e) {
            ini_set('memory_limit', $prevMemLimit);
            Log::error('Error parsing Excel', [
                'message' => $e->getMessage(),
                'file' => basename($filePath),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Format file tidak didukung atau file rusak: ' . $e->getMessage());
        }

        ini_set('memory_limit', $prevMemLimit);
        
        Log::info("Import Excel selesai: Total " . count($allData) . " baris data dari " . count($foundHeaders) . " kolom", [
            'headers' => $foundHeaders
        ]);

        return [
            'data'    => $allData,
            'headers' => array_values(array_filter($foundHeaders)),
        ];
    }

    /**
     * Deteksi baris header secara FLEKSIBEL menggunakan pendekatan scoring.
     *
     * Algoritma:
     * 1. Scan setiap baris (hingga 15 baris pertama)
     * 2. Hitung "skor" tiap baris = jumlah key column yang cocok
     * 3. Tandai baris sebagai "header" jika skor >= 1
     * 4. Temukan BLOK KONSEKUTIF baris header (mendukung 1, 2, bahkan 3 baris header)
     * 5. Blok pertama = header block, baris setelahnya = awal data
     *
     * Ini menangani semua format:
     * - 2023: satu baris header di baris 1 atau 2
     * - 2025: dua baris header (parent merged + sub-header) di posisi manapun
     * - File dengan judul/logo di atas header (header tidak di baris 1)
     *
     * Returns: [lastHeaderRowIdx, dataStartRowIdx, firstHeaderRowIdx|null]
     *   - lastHeaderRowIdx  : baris sub-header (paling bawah dari blok header)
     *   - dataStartRowIdx   : index 0-based ke $rows (perlu +1 untuk sheet row 1-based)
     *   - firstHeaderRowIdx : baris parent (null jika hanya 1 baris header)
     */
    protected function detectHeaderRow(array $rows): array
    {
        // Semua nama kolom yang mungkin muncul di baris header (semua tahun)
        $keyColumns = [
            // Kolom wajib — muncul di hampir semua tahun
            'Nama Organisasi',
            'Nama Organisasi / Komunitas',
            'Nama Organisasi/ Komunitas',
            'Judul Kegiatan',
            'Judul Kegiatan/Event',
            'Tanggal',
            'Tanggal Kegiatan',
            'Jumlah Peserta',
            'Lokasi',
            'Lokasi / Ruangan',
            'Lantai',
            // Sub-header 2025 (di bawah Kategori Pelaku)
            'Bidang',
            'Jenis Kegiatan',
            'Kegiatan',
            'Sub Sektor',
            'Komersial',
            'Komersial/Non Komersial',
            'Komersial/ Non Komersial',
            'Program SDGS',
            'Program SDGs',
            'SDGS',
            // Kolom kontak
            'Email',
            'No. Telf',
            'No. Telp',
            'PIC',
        ];

        // Skor setiap baris: berapa key column yang cocok
        // Cocokkan secara case-insensitive untuk toleransi variasi penulisan
        $keyLower = array_map('strtolower', $keyColumns);

        $rowScores = [];
        foreach ($rows as $idx => $row) {
            $score = 0;
            foreach ($row as $cell) {
                $cellLower = strtolower(trim((string) ($cell ?? '')));
                if ($cellLower !== '' && in_array($cellLower, $keyLower, true)) {
                    $score++;
                }
            }
            $rowScores[$idx] = $score;
        }

        // Cari row dengan score tertinggi (anchor row)
        $maxScore = -1;
        $anchorIdx = null;
        foreach ($rowScores as $idx => $score) {
            if ($score > $maxScore) {
                $maxScore = $score;
                $anchorIdx = $idx;
            }
        }

        // Header setidaknya butuh 4 key columns yang cocok
        if ($anchorIdx === null || $maxScore < 4) {
            return [null, null, null]; // Tidak ada header ditemukan
        }

        $scoreAfter = $rowScores[$anchorIdx + 1] ?? 0;
        $scoreBefore = $rowScores[$anchorIdx - 1] ?? 0;

        if ($scoreAfter >= 3) {
            // Jika row setelah anchor memiliki kecocokan tinggi (>=3), merge anchor (parent) dan row setelahnya (sub-header)
            return [$anchorIdx + 1, $anchorIdx + 2, $anchorIdx];
        } elseif ($scoreBefore >= 3) {
            // Jika row sebelum anchor memiliki kecocokan tinggi (>=3), merge row sebelum (parent) dan anchor (sub-header)
            return [$anchorIdx, $anchorIdx + 1, $anchorIdx - 1];
        } else {
            // Single-row header
            return [$anchorIdx, $anchorIdx + 1, null];
        }
    }

    /**
     * Gabungkan beberapa baris header menjadi satu array header lengkap.
     *
     * Bekerja dengan cara overlay: mulai dari baris paling atas (parent),
     * setiap baris bawah menimpa sel yang terisi (sub-header lebih spesifik).
     *
     * Group labels yang hanya sebagai judul grup (bukan nama kolom data) dibuang.
     *
     * @param array $headerRows  Array of rows, paling atas = index 0 (parent), paling bawah = last (sub)
     * @return array Merged header row
     */
    protected function mergeHeaderRows(array ...$headerRows): array
    {
        // Label grup yang bukan nama kolom data — buang saat merge
        $groupLabels = [
            'Kategori Pelaku',
            'Kategori',
            'Kelompok',
        ];

        if (empty($headerRows)) return [];

        $colCount = max(array_map('count', $headerRows));
        $merged   = array_fill(0, $colCount, '');

        // Overlay dari parent ke sub-header:
        // Tiap baris menimpa sel yang kosong dari baris sebelumnya
        // Sub-header (baris terakhir) paling tinggi prioritasnya
        foreach ($headerRows as $row) {
            for ($i = 0; $i < $colCount; $i++) {
                $val = trim((string) ($row[$i] ?? ''));
                if ($val !== '' && !in_array($val, $groupLabels, true)) {
                    // Sub-header menimpa parent (prioritas lebih tinggi)
                    $merged[$i] = $val;
                }
                // Jika sub kosong, biarkan nilai dari parent (sudah di $merged[$i])
            }
        }

        return $merged;
    }

    /**
     * Standardize raw Excel data to DB schema.
     *
     * Menggunakan sistem column resolver yang fleksibel sehingga:
     * - Kolom baru (misal Program SDGS di 2025) otomatis dipetakan jika ada
     * - Kolom yang tidak ada di file lama (2023/2024) diisi null tanpa error
     * - Perbedaan nama kolom antar tahun ditangani via alias mapping
     *
     * Multi-lokasi/lantai DI-SPLIT menjadi baris terpisah untuk menghitung event per lokasi.
     * NAMUN jumlah_peserta hanya diisi di baris PERTAMA, baris berikutnya = 0.
     */
    public function standardize(array $rawData): array
    {
        $result = [];
        $loggedColumns = false; // Flag untuk log kolom hanya sekali

        foreach ($rawData as $index => $item) {
            // Normalisasi key: trim whitespace
            $t = [];
            foreach ($item as $k => $v) {
                $t[trim((string) $k)] = is_string($v) ? trim($v) : $v;
            }

            // Resolve setiap field menggunakan alias-aware resolver
            $r = $this->resolveColumns($t);
            
            // Log kolom yang berhasil di-resolve (hanya sekali untuk efisiensi)
            if (!$loggedColumns && $index === 0) {
                $resolvedFields = array_filter($r, fn($v) => $v !== null);
                Log::info("Kolom yang berhasil di-resolve dari Excel:", [
                    'fields' => array_keys($resolvedFields),
                    'has_program_sdgs' => isset($resolvedFields['program_sdgs']),
                    'has_email' => isset($resolvedFields['email']),
                    'has_no_telepon' => isset($resolvedFields['no_telepon']),
                    'has_pic' => isset($resolvedFields['pic']),
                ]);
                $loggedColumns = true;
            }

            // Tanggal
            $finalTanggal = $this->parseDate($r['tanggal_raw']) ?? date('Y-m-d');

            // Bulan — selalu diisi agar widget yang mengandalkan kolom `bulan` tidak null
            $finalBulan = $this->getBulanIndonesia($finalTanggal);

            // Peserta
            $pesertaParsed = (int) preg_replace('/[^\d]/', '', (string) ($r['jumlah_peserta'] ?? 0));

            // Lantai — split multi
            $lantaiList = $this->splitMulti((string) ($r['lantai'] ?? '2'));
            $lantaiList = array_values(array_filter(
                array_map(fn($l) => in_array(trim($l), ['2', '3', '4', '5']) ? trim($l) : null, $lantaiList)
            ));
            if (empty($lantaiList)) $lantaiList = ['2'];

            // Lokasi — split multi
            $lokasiList = $this->splitMulti((string) ($r['lokasi'] ?? ''));
            $lokasiList = array_values(array_map([$this, 'cleanLokasi'], $lokasiList));
            if (empty($lokasiList)) $lokasiList = ['Ruang Meeting'];

            // Email, No.Telf, PIC — nullable, aman untuk data 2023 yang tidak memiliki kolom ini
            $emailVal     = $this->cleanNullableString($r['email'] ?? null, 255);
            $noTelpVal    = $this->cleanNullableString($r['no_telepon'] ?? null, 50);
            $picVal       = $this->cleanNullableString($r['pic'] ?? null, 150);

            $commonData = [
                'nama_organisasi' => $this->cleanOrganisasi($r['nama_organisasi'] ?? 'Unknown'),
                'bidang'          => $this->cleanBidang($r['bidang'] ?? ''),
                'jenis_kegiatan'  => $this->cleanKegiatan($r['jenis_kegiatan'] ?? ''),
                'sub_sektor'      => ($r['sub_sektor'] ?? null) ?: null,
                'komersial'       => $this->cleanKomersial($r['komersial'] ?? ''),
                'program_sdgs'    => $this->cleanSdgs($r['program_sdgs'] ?? null),
                'judul_kegiatan'  => $r['judul_kegiatan'] ?? 'Untitled Event',
                'tanggal'         => $finalTanggal,
                'bulan'           => $finalBulan,
                'email'           => $emailVal,
                'no_telepon'      => $noTelpVal,
                'pic'             => $picVal,
                '_sheet'          => isset($item['_sheet']) ? trim((string) $item['_sheet']) : null,
            ];

            // Cartesian product: lantai × lokasi
            $isFirst = true;
            foreach ($lantaiList as $lantai) {
                foreach ($lokasiList as $lokasi) {
                    $result[] = array_merge($commonData, [
                        'lantai'         => $lantai,
                        'lokasi'         => $lokasi,
                        'jumlah_peserta' => $pesertaParsed,
                        'is_primary_row' => $isFirst,
                    ]);
                    $isFirst = false;
                }
            }
        }

        return $result;
    }

    /**
     * Resolve kolom dari raw Excel row menggunakan alias mapping.
     *
     * Setiap field DB memiliki daftar alias nama kolom Excel yang mungkin muncul
     * di berbagai tahun/format. Sistem ini toleran terhadap:
     * - Penambahan kolom baru (2025: Program SDGS)
     * - Perbedaan nama kolom antar tahun (2023: "Kegiatan" vs 2024: "Jenis Kegiatan")
     * - Kolom yang tidak ada → diisi null
     */
    protected function resolveColumns(array $row): array
    {
        // Definisi alias per field: key = nama field DB, value = array nama kolom Excel yang mungkin
        $aliases = [
            'nama_organisasi' => [
                'Nama Organisasi / Komunitas',
                'Nama Organisasi/ Komunitas',
                'Nama Organisasi',
                'Organisasi',
            ],
            'bidang' => [
                'Bidang',
            ],
            'jenis_kegiatan' => [
                'Jenis Kegiatan',  // 2024, 2025
                'Kegiatan',        // 2023
            ],
            'sub_sektor' => [
                'Sub Sektor',
                'Subsektor',
                'Sub-Sektor',
            ],
            'komersial' => [
                'Komersial/Non Komersial',   // 2025
                'Komersial/ Non Komersial',  // 2024
                'Komersial/Non',
                'Komersial',
            ],
            'program_sdgs' => [
                'Program SDGS',      // 2025+
                'Program (SDGS)',    // variasi 2025
                'Program SDGs',
                'SDGS',
                'SDGs',
                'Program Sdgs',
            ],
            'judul_kegiatan' => [
                'Judul Kegiatan',
                'Judul Kegiatan/Event',  // variasi 2025
                'Judul Kegiatan / Event',
                'Event',
                'Judul',
            ],
            'tanggal_raw' => [
                'Tanggal Kegiatan',
                'Tanggal',
                'Tgl Kegiatan',
                'Tgl',
            ],
            'lantai' => [
                'Lantai',
                'Lt',
                'Floor',
            ],
            'lokasi' => [
                'Lokasi / Ruangan',
                'Lokasi/ Ruangan',
                'Lokasi/Ruangan',
                'Lokasi',
                'Ruangan',
                'Room',
            ],
            'jumlah_peserta' => [
                'Jumlah Peserta',
                'Jumlah  Peserta',  // double space
                'Peserta',
                'Jml Peserta',
            ],
            'email' => [
                'Email',
                'E-mail',
                'E Mail',
            ],
            'no_telepon' => [
                'No. Telf',
                'No. Telp',
                'No Telp',
                'No. Telepon',
                'Telepon',
                'No Telepon',
                'Telp',
                'Hp',
                'No. Hp',
            ],
            'pic' => [
                'PIC',
                'Pic',
                'P.I.C',
                'Person In Charge',
            ],
        ];

        $resolved = [];

        foreach ($aliases as $field => $possibleKeys) {
            $resolved[$field] = null; // default null jika kolom tidak ada

            foreach ($possibleKeys as $key) {
                // Cek exact match dulu
                if (array_key_exists($key, $row) && $row[$key] !== '' && $row[$key] !== null) {
                    $resolved[$field] = $row[$key];
                    break;
                }

                // Cek case-insensitive sebagai fallback
                foreach ($row as $rowKey => $rowVal) {
                    if (strcasecmp(trim($rowKey), trim($key)) === 0 && $rowVal !== '' && $rowVal !== null) {
                        $resolved[$field] = $rowVal;
                        break 2;
                    }
                }
            }
        }

        return $resolved;
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

    /**
     * Normalisasi nilai Program SDGS.
     *
     * Mendukung berbagai format input dari data 2025:
     * - Single: "SDGS 4", "SDGs 8", "4", "sdgs4"  → "SDGS 4"
     * - Multi:  "SDGS 4; SDGS 5; SDGS 18"          → "SDGS 4; SDGS 5; SDGS 18"
     * - Multi:  "4, 5, 10"                          → "SDGS 4; SDGS 5; SDGS 10"
     * - Null/kosong                                  → null (kolom tidak wajib, aman untuk data 2023)
     */
    private function cleanSdgs(mixed $raw): ?string
    {
        if ($raw === null || trim((string) $raw) === '') return null;

        $s = trim((string) $raw);

        // Pecah nilai multi (separator: semicolon, koma, spasi sebelum "SDGS")
        // Contoh: "SDGS 4; SDGS 5; SDGS 18" atau "4, 5, 10"
        $parts = preg_split('/[;,]|(?=\bsdgs?\s*\d)/i', $s);
        $parts = array_filter(array_map('trim', $parts), fn($p) => $p !== '');

        if (empty($parts)) return null;

        $normalized = [];
        foreach ($parts as $part) {
            $part = trim($part);

            // Format "SDGS X" atau "SDGs X" atau "sdgsx"
            if (preg_match('/^sdgs?\s*(\d{1,2})$/i', $part, $m)) {
                $num = (int) $m[1];
                if ($num >= 1 && $num <= 17) {
                    $normalized[] = 'SDGS ' . $num;
                } else {
                    $normalized[] = 'SDGS ' . $num; // tetap simpan meskipun di luar 1-17
                }
                continue;
            }

            // Hanya angka: "4" → "SDGS 4"
            if (preg_match('/^\d{1,2}$/', $part)) {
                $normalized[] = 'SDGS ' . (int) $part;
                continue;
            }

            // Format tidak dikenali — simpan apa adanya agar data tidak hilang
            if (!empty($part)) {
                $normalized[] = $part;
            }
        }

        if (empty($normalized)) return null;

        $result = implode('; ', array_unique($normalized));

        // Pastikan tidak melebihi panjang kolom database (255 char)
        return mb_substr($result, 0, 255);
    }

    /**
     * Bersihkan string nullable — kembalikan null jika kosong, potong jika terlalu panjang.
     * Mencegah error "value too long for column" untuk email/no_telepon/pic dari data 2025.
     */
    private function cleanNullableString(mixed $raw, int $maxLength = 255): ?string
    {
        if ($raw === null) return null;
        $s = trim((string) $raw);
        if ($s === '') return null;
        // Potong jika melebihi batas kolom
        return mb_substr($s, 0, $maxLength);
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
