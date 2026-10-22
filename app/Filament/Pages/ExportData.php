<?php

namespace App\Filament\Pages;

use App\Models\Event;
use Filament\Pages\Page;
use Livewire\WithPagination;
use Filament\Notifications\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class ExportData extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string $view = 'filament.pages.export-data';
    protected static ?string $navigationLabel = 'Export Data';
    protected static ?string $navigationGroup = 'Import / Export';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Export Data Engine';

    // ── Properties ──
    public string $selectedFormat = 'xlsx';
    public string $selectedYearStart;
    public string $selectedYearEnd;
    public string $selectedMonth = 'Semua';
    public int $totalRows = 0;
    public int $perPage = 10;
    public string $exportType = 'detail'; // 'detail' or 'summary'

    // ── Constants ──
    public array $formats = [
        ['id' => 'xlsx', 'icon' => '📊', 'name' => 'Excel (.xlsx)', 'desc' => 'Spreadsheet dengan format dan styling'],
        ['id' => 'csv',  'icon' => '📄', 'name' => 'CSV (.csv)',   'desc' => 'Data mentah dalam format teks'],
        ['id' => 'json', 'icon' => '🔷', 'name' => 'JSON (.json)', 'desc' => 'Struktur data untuk API/developer'],
        ['id' => 'pdf',  'icon' => '📕', 'name' => 'PDF (.pdf)',   'desc' => 'Dokumen siap cetak dan laporan'],
    ];

    public array $exportTypes = [
        ['id' => 'detail', 'icon' => '📋', 'name' => 'Data Detail', 'desc' => 'Export semua data event per baris'],
        ['id' => 'summary', 'icon' => '📊', 'name' => 'Laporan Analitik', 'desc' => 'Export ringkasan analitik seperti Analitik Ekraf'],
    ];

    public array $months = [
        'Semua' => 'Semua Bulan (Januari - Desember)',
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    // ── Lifecycle ──
    public function mount(): void
    {
        // Get the most recent year with data, or current year as fallback
        $latestYear = Event::selectRaw('EXTRACT(YEAR FROM tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->value('year');
        
        $earliestYear = Event::selectRaw('EXTRACT(YEAR FROM tanggal) as year')
            ->distinct()
            ->orderBy('year', 'asc')
            ->value('year');
        
        $this->selectedYearStart = (string) ($earliestYear ?? date('Y'));
        $this->selectedYearEnd = (string) ($latestYear ?? date('Y'));
        $this->refreshCount();
    }

    public function updatedSelectedYearStart(): void
    {
        // Ensure start year is not greater than end year
        if ($this->selectedYearStart > $this->selectedYearEnd) {
            $this->selectedYearEnd = $this->selectedYearStart;
        }
        $this->resetPage();
        $this->refreshCount();
    }

    public function updatedSelectedYearEnd(): void
    {
        // Ensure end year is not less than start year
        if ($this->selectedYearEnd < $this->selectedYearStart) {
            $this->selectedYearStart = $this->selectedYearEnd;
        }
        $this->resetPage();
        $this->refreshCount();
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
        $this->refreshCount();
    }

    // ── Helpers ──
    protected function refreshCount(): void
    {
        if ($this->exportType === 'summary') {
            // For summary, count is based on year range
            $this->totalRows = 1; // Summary always has data if there are events
        } else {
            [$start, $end] = $this->getDateRange();
            
            $this->totalRows = Event::valid()
                ->whereBetween('tanggal', [$start, $end])
                ->count();
        }
    }

    protected function getDateRange(): array
    {
        $yearStart = $this->selectedYearStart;
        $yearEnd = $this->selectedYearEnd;
        
        if ($this->selectedMonth === 'Semua') {
            return ["{$yearStart}-01-01", "{$yearEnd}-12-31"];
        }
        
        $month = $this->selectedMonth;
        $lastDay = date('t', mktime(0, 0, 0, (int) $month, 1, (int) $yearEnd));
        
        // For specific month, use only end year
        return ["{$yearEnd}-{$month}-01", "{$yearEnd}-{$month}-{$lastDay}"];
    }

    protected function getBaseQuery()
    {
        [$start, $end] = $this->getDateRange();
        
        return Event::valid()
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'desc');
    }

    protected function getExportColumns(): array
    {
        return [
            'nama_organisasi',
            'bidang',
            'jenis_kegiatan',
            'sub_sektor',
            'komersial',
            'program_sdgs',
            'judul_kegiatan',
            'tanggal',
            'bulan',
            'lantai',
            'lokasi',
            'jumlah_peserta',
            'email',
            'no_telepon',
            'pic',
            'status_data',
        ];
    }

    protected function getColumnLabels(): array
    {
        return [
            'nama_organisasi' => 'Nama Organisasi',
            'bidang'          => 'Bidang Ekraf',
            'jenis_kegiatan'  => 'Jenis Kegiatan',
            'sub_sektor'      => 'Sub Sektor',
            'komersial'       => 'Komersial',
            'program_sdgs'    => 'Program SDGs',
            'judul_kegiatan'  => 'Judul Kegiatan',
            'tanggal'         => 'Tanggal Kegiatan',
            'bulan'           => 'Bulan',
            'lantai'          => 'Lantai',
            'lokasi'          => 'Lokasi Kegiatan',
            'jumlah_peserta'  => 'Jumlah Peserta',
            'email'           => 'Email PIC',
            'no_telepon'      => 'No WhatsApp PIC',
            'pic'             => 'Nama PIC',
            'status_data'     => 'Status Data',
        ];
    }

    protected function generateFilename(): string
    {
        $prefix = 'MCC_Export';
        $timestamp = date('Ymd_His');
        
        $typeLabel = $this->exportType === 'summary' ? 'Analitik' : 'Detail';
        
        if ($this->selectedMonth === 'Semua') {
            if ($this->selectedYearStart === $this->selectedYearEnd) {
                return "{$prefix}_{$typeLabel}_Tahun_{$this->selectedYearEnd}_{$timestamp}";
            } else {
                return "{$prefix}_{$typeLabel}_{$this->selectedYearStart}-{$this->selectedYearEnd}_{$timestamp}";
            }
        }
        
        // Get month name without spaces for filename
        $monthName = str_replace(' ', '_', $this->months[$this->selectedMonth]);
        return "{$prefix}_{$typeLabel}_{$monthName}_{$this->selectedYearEnd}_{$timestamp}";
    }

    // ── Public Methods ──
    public function getAvailableYears(): array
    {
        $years = Event::selectRaw('EXTRACT(YEAR FROM tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        // If no years found, return current year as fallback
        if (empty($years)) {
            $currentYear = date('Y');
            return [$currentYear => $currentYear];
        }

        return $years;
    }

    public function getPreviewData()
    {
        if ($this->exportType === 'summary') {
            // For summary, show a preview message
            return collect([]);
        }
        
        return $this->getBaseQuery()->paginate($this->perPage);
    }

    protected function getAnalyticsData(): array
    {
        $yearStart = (int) $this->selectedYearStart;
        $yearEnd = (int) $this->selectedYearEnd;

        // Base query for year range
        $baseQuery = fn() => Event::whereBetween(DB::raw('EXTRACT(YEAR FROM tanggal)'), [$yearStart, $yearEnd])
            ->where('status_data', 'Valid');

        // 1. Sub Sektor
        $kategoriEkraf = $baseQuery()
            ->primary()
            ->whereNotNull('sub_sektor')
            ->where('sub_sektor', '!=', '')
            ->select('sub_sektor as label', DB::raw('count(*) as total'))
            ->groupBy('sub_sektor')
            ->orderBy('total', 'desc')
            ->get();

        // 2. Jenis Kegiatan
        $kategoriKegiatan = $baseQuery()
            ->primary()
            ->select('jenis_kegiatan as label', DB::raw('count(*) as total'))
            ->groupBy('jenis_kegiatan')
            ->orderBy('total', 'desc')
            ->get();

        // 3. Bidang Pelaku
        $kategoriPelaku = $baseQuery()
            ->primary()
            ->select('bidang as label', DB::raw('count(*) as total'))
            ->groupBy('bidang')
            ->orderBy('total', 'desc')
            ->get();

        // 4. Rekap Bulanan per Tahun
        $bulanan = [];
        foreach (range($yearStart, $yearEnd) as $year) {
            // Gunakan clone agar query tidak saling bertabrakan
            $baseYearQuery = Event::whereYear('tanggal', $year)->where('status_data', 'Valid');
            
            $yearData = (clone $baseYearQuery)
                ->whereNotNull('bulan')
                ->select(
                    'bulan',
                    DB::raw('COUNT(CASE WHEN is_primary_row = true THEN 1 END) as total_event'),
                    DB::raw('SUM(CASE WHEN is_primary_row = true THEN jumlah_peserta ELSE 0 END) as total_pengunjung'),
                    DB::raw('COUNT(CASE WHEN is_primary_row = true AND komersial = \'Komersial\' THEN 1 END) as total_komersil'),
                    DB::raw('COUNT(CASE WHEN is_primary_row = true AND komersial = \'Non Komersial\' THEN 1 END) as total_non_komersil'),
                    DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as total_pelaku')
                )
                ->groupBy('bulan')
                ->orderBy(DB::raw('ARRAY_POSITION(ARRAY[\'Januari\',\'Februari\',\'Maret\',\'April\',\'Mei\',\'Juni\',\'Juli\',\'Agustus\',\'September\',\'Oktober\',\'November\',\'Desember\'], bulan)'))
                ->get();

            // Hitung total tahunan yang sebenarnya (Unique per year)
            $yearlyUniquePelaku = (clone $baseYearQuery)
                ->select(DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as total'))
                ->value('total') ?? 0;
            
            $yearlyTotalEvents = $yearData->sum('total_event');
            $yearlyTotalPengunjung = $yearData->sum('total_pengunjung');
            
            $bulanan[$year] = [
                'data' => $yearData,
                'totals' => [
                    'event' => $yearlyTotalEvents,
                    'pengunjung' => $yearlyTotalPengunjung,
                    'pelaku' => $yearlyUniquePelaku,
                ]
            ];
        }

        // 5. Total Pelaku Unik dalam periode
        $totalPelaku = $baseQuery()
            ->select(DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as total'))
            ->value('total') ?? 0;

        // 6. Total Events (Primary Only)
        $totalEvents = $baseQuery()->primary()->count();

        // 7. Total Pengunjung (Primary Only)
        $totalPengunjung = $baseQuery()->primary()->sum('jumlah_peserta');

        // 8. Komersial vs Non Komersial (Primary Only)
        $komersial = $baseQuery()
            ->primary()
            ->select('komersial', DB::raw('count(*) as total'))
            ->groupBy('komersial')
            ->get();

        // 9. SDGs Counts (Primary Events only for consistency)
        $sdgsList = $baseQuery()
            ->primary()
            ->whereNotNull('program_sdgs')
            ->where('program_sdgs', '!=', '')
            ->pluck('program_sdgs');

        $sdgsCounts = [];
        for ($i = 1; $i <= 17; $i++) {
            $sdgsCounts["SDGS $i"] = 0;
        }

        foreach ($sdgsList as $sdgsString) {
            $parts = preg_split('/[;,&]|\bdan\b|\band\b|(?=\bsdgs?\s*\d)/i', $sdgsString);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                if (preg_match('/sdgs?\s*(\d{1,2})/i', $part, $m)) {
                    $num = (int)$m[1];
                    if ($num >= 1 && $num <= 17) {
                        $sdgsCounts["SDGS $num"]++;
                    }
                } elseif (preg_match('/^\d{1,2}$/', $part)) {
                    $num = (int)$part;
                    if ($num >= 1 && $num <= 17) {
                        $sdgsCounts["SDGS $num"]++;
                    }
                }
            }
        }

        return [
            'kategoriEkraf'       => $kategoriEkraf,
            'kategoriKegiatan'    => $kategoriKegiatan,
            'kategoriPelaku'      => $kategoriPelaku,
            'bulanan'             => $bulanan,
            'totalPelaku'         => $totalPelaku,
            'totalEvents'         => $totalEvents,
            'totalPengunjung'     => $totalPengunjung,
            'komersial'           => $komersial,
            'yearStart'           => $yearStart,
            'yearEnd'             => $yearEnd,
            'sdgsCounts'          => $sdgsCounts,
        ];
    }

    // ── Export Handler ──
    public function export(): StreamedResponse|null
    {
        try {
            if ($this->exportType === 'detail' && $this->totalRows === 0) {
                Notification::make()
                    ->title('Tidak Ada Data')
                    ->body('Tidak ada event yang dapat diekspor untuk periode yang dipilih.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = $this->generateFilename();

            if ($this->exportType === 'summary') {
                // Export summary/analytics
                $response = match($this->selectedFormat) {
                    'xlsx' => $this->exportSummaryXlsx($filename),
                    'pdf'  => $this->exportSummaryPdf($filename),
                    'csv'  => $this->exportSummaryCsv($filename),
                    'json' => $this->exportSummaryJson($filename),
                    default => null,
                };
            } else {
                // Export detail
                $response = match($this->selectedFormat) {
                    'pdf'  => $this->exportPdf($filename),
                    'csv'  => $this->exportCsv($filename),
                    'json' => $this->exportJson($filename),
                    'xlsx' => $this->exportXlsx($filename),
                    default => null,
                };
            }

            if ($response) {
                Notification::make()
                    ->title('Export Berhasil')
                    ->body("File {$filename}.{$this->selectedFormat} berhasil diunduh.")
                    ->success()
                    ->send();
            }

            return $response;

        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Gagal')
                ->body('Terjadi kesalahan saat mengekspor data: ' . $e->getMessage())
                ->danger()
                ->send();
            
            \Log::error('Export Error: ' . $e->getMessage(), [
                'format' => $this->selectedFormat,
                'type' => $this->exportType,
                'yearStart' => $this->selectedYearStart,
                'yearEnd' => $this->selectedYearEnd,
                'month' => $this->selectedMonth,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return null;
        }
    }

    // ── PDF Export ──
    protected function exportPdf(string $filename): StreamedResponse
    {
        // Batasi PDF Detail maksimal 1.000 baris untuk mencegah memory leak & rendering lambat di DomPDF.
        // Untuk data masif (10.000+ baris), gunakan format Excel (.xlsx) atau CSV (.csv).
        $data = $this->getBaseQuery()->take(1000)->get($this->getExportColumns())->toArray();
        
        $periodLabel = $this->selectedMonth !== 'Semua'
            ? $this->months[$this->selectedMonth] . ' ' . $this->selectedYearEnd
            : 'Tahun ' . ($this->selectedYearStart == $this->selectedYearEnd ? $this->selectedYearEnd : $this->selectedYearStart . ' - ' . $this->selectedYearEnd);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.events-pdf', [
            'data' => $data,
            'period' => $periodLabel,
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false)
        ->setOption('defaultFont', 'Helvetica');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "{$filename}.pdf",
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}.pdf\"",
            ]
        );
    }

    /**
     * Sanitasi nilai string terhadap CSV / Spreadsheet Formula Injection (OWASP).
     * Mencegah eksekusi formula berbahaya (=, +, -, @, Tab, CR) saat dibuka di Excel.
     */
    protected function sanitizeSpreadsheetValue(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        $firstChar = $value[0];
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    // ── CSV Export ──
    protected function exportCsv(string $filename): StreamedResponse
    {
        $data = $this->getBaseQuery()->get($this->getExportColumns())->toArray();
        $columnLabels = $this->getColumnLabels();

        return response()->streamDownload(function () use ($data, $columnLabels) {
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 compatibility (Excel support)
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            if (!empty($data)) {
                // Write headers with proper labels
                $headers = array_map(
                    fn($key) => $columnLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                    array_keys($data[0])
                );
                fputcsv($output, $headers);
                
                // Write data with formatting & formula injection sanitization
                foreach ($data as $row) {
                    $formattedRow = [];
                    foreach ($row as $key => $value) {
                        // Format specific columns
                        if ($key === 'tanggal' && $value) {
                            $value = date('d/m/Y', strtotime($value));
                        } elseif ($key === 'jumlah_peserta' && is_numeric($value)) {
                            $value = number_format($value, 0, ',', '.');
                        }
                        $formattedRow[] = $this->sanitizeSpreadsheetValue($value ?? '');
                    }
                    fputcsv($output, $formattedRow);
                }
            } else {
                // Write empty message
                fputcsv($output, ['Tidak ada data untuk periode yang dipilih']);
            }
            
            fclose($output);
        }, "{$filename}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    // ── JSON Export ──
    protected function exportJson(string $filename): StreamedResponse
    {
        $data = $this->getBaseQuery()
            ->get($this->getExportColumns())
            ->map(function ($item) {
                // Format data for JSON
                return [
                    'nama_organisasi' => $item->nama_organisasi,
                    'bidang' => $item->bidang,
                    'jenis_kegiatan' => $item->jenis_kegiatan,
                    'sub_sektor' => $item->sub_sektor,
                    'komersial' => $item->komersial,
                    'judul_kegiatan' => $item->judul_kegiatan,
                    'tanggal' => $item->tanggal ? date('Y-m-d', strtotime($item->tanggal)) : null,
                    'bulan' => $item->bulan,
                    'lantai' => $item->lantai,
                    'lokasi' => $item->lokasi,
                    'jumlah_peserta' => (int) $item->jumlah_peserta,
                    'email' => $item->email,
                    'no_telepon' => $item->no_telepon,
                    'pic' => $item->pic,
                    'status_data' => $item->status_data,
                ];
            })
            ->toArray();

        $periodLabel = $this->selectedMonth !== 'Semua'
            ? $this->months[$this->selectedMonth] . ' ' . $this->selectedYearEnd
            : 'Tahun ' . $this->selectedYearEnd;

        $export = [
            'metadata' => [
                'title' => 'Export Data Event MCC',
                'exported_at' => now()->toIso8601String(),
                'exported_by' => auth()->user()->name ?? 'System',
                'period' => $periodLabel,
                'year' => $this->selectedYearEnd,
                'month' => $this->selectedMonth !== 'Semua' ? $this->selectedMonth : null,
                'total_records' => count($data),
                'format_version' => '1.0',
            ],
            'columns' => $this->getColumnLabels(),
            'data' => $data,
            'summary' => [
                'total_events' => count($data),
                'total_participants' => array_sum(array_column($data, 'jumlah_peserta')),
            ],
        ];

        return response()->streamDownload(
            fn() => print (json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            "{$filename}.json",
            [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
            ]
        );
    }

    // ── XLSX Export ──
    protected function exportXlsx(string $filename): StreamedResponse
    {
        $data = $this->getBaseQuery()->get($this->getExportColumns())->toArray();
        $spreadsheet = $this->createXlsxSpreadsheet($data);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            "{$filename}.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    protected function createXlsxSpreadsheet(array $data): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Event MCC');

        // Add Logo
        $this->addLogoToSheet($sheet);
        
        // Add Header Info
        $this->addHeaderInfoToSheet($sheet);
        
        // Add Data Table
        if (!empty($data)) {
            $this->addDataTableToSheet($sheet, $data);
            
            // Freeze panes (freeze header row)
            $sheet->freezePane('A8');
            
            // Enable auto filter on header row
            $lastCol = $this->getColumnLetter(count($this->getExportColumns()) - 1);
            $sheet->setAutoFilter("A7:{$lastCol}7");
        }

        // Set print settings
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        
        // Set margins
        $sheet->getPageMargins()
            ->setTop(0.75)
            ->setRight(0.5)
            ->setLeft(0.5)
            ->setBottom(0.75);

        return $spreadsheet;
    }

    protected function addLogoToSheet($sheet): void
    {
        $logoPath = base_path('public/images/logo.png');
        
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('MCC Logo');
            $drawing->setDescription('Malang Creative Center Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(50);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }
    }

    protected function addHeaderInfoToSheet($sheet): void
    {
        // Title
        $sheet->setCellValue('C1', 'LAPORAN REKAPITULASI EVENT');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        
        // Institution
        $sheet->setCellValue('C2', 'MALANG CREATIVE CENTER (MCC)');
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(11);

        // Address
        $sheet->setCellValue('C3', 'Jl. A. Yani No. 53, Blimbing, Malang, Jawa Timur 65118');
        $sheet->getStyle('C3')->getFont()->setSize(9);
        
        // Period
        $periodLabel = $this->selectedMonth !== 'Semua'
            ? $this->months[$this->selectedMonth] . ' ' . $this->selectedYearStart
            : 'Tahun ' . ($this->selectedYearStart == $this->selectedYearEnd ? $this->selectedYearStart : $this->selectedYearStart . ' - ' . $this->selectedYearEnd);
        
        $sheet->setCellValue('C4', 'Periode: ' . $periodLabel);
        $sheet->getStyle('C4')->getFont()->setSize(10)->setBold(true);
        
        // Export info
        $sheet->setCellValue('C5', 'Dicetak pada: ' . date('d/m/Y, H:i') . ' WIB');
        $sheet->getStyle('C5')->getFont()->setSize(8)->setItalic(true);
    }

    protected function addDataTableToSheet($sheet, array $data): void
    {
        $startRow = 7;
        $columnLabels = $this->getColumnLabels();
        $columns = $this->getExportColumns();
        
        // Write Headers
        $headers = array_map(fn($col) => $columnLabels[$col] ?? $col, $columns);
        $sheet->fromArray([$headers], null, "A{$startRow}");
        
        // Style Headers (Simple Table)
        $lastCol = $this->getColumnLetter(count($headers) - 1);
        $headerRange = "A{$startRow}:{$lastCol}{$startRow}";
        
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Set header row height
        $sheet->getRowDimension($startRow)->setRowHeight(30);

        // Write Data
        $dataStartRow = $startRow + 1;
        $formattedData = [];
        
        foreach ($data as $row) {
            $formattedRow = [];
            foreach ($columns as $col) {
                // Jangan gunakan '-' untuk nilai kosong agar tidak merusak data training
                $value = isset($row[$col]) && $row[$col] !== '' ? $row[$col] : '';
                
                // Format specific columns
                if ($col === 'tanggal' && $value !== '') {
                    $value = date('d/m/Y', strtotime($value));
                } elseif ($col === 'jumlah_peserta' && is_numeric($value)) {
                    $value = number_format($value, 0, ',', '.');
                }
                
                $formattedRow[] = $this->sanitizeSpreadsheetValue($value);
            }
            $formattedData[] = $formattedRow;
        }
        
        if (!empty($formattedData)) {
            $sheet->fromArray($formattedData, null, "A{$dataStartRow}");
            
            // Style Data
            $dataEndRow = $dataStartRow + count($formattedData) - 1;
            $dataRange = "A{$dataStartRow}:{$lastCol}{$dataEndRow}";
            
            // Apply general data styling
            $sheet->getStyle($dataRange)->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);
            
            // Alternate row colors (Light Gray)
            for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                if (($row - $dataStartRow) % 2 == 0) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FAFAFA'],
                        ],
                    ]);
                }
            }
            
            // Add borders
            $borderRange = "A{$startRow}:{$lastCol}{$dataEndRow}";
            $sheet->getStyle($borderRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            
            // Add summary row
            $summaryRow = $dataEndRow + 1;
            $sheet->setCellValue("A{$summaryRow}", 'Total Data:');
            $sheet->setCellValue("B{$summaryRow}", count($formattedData));
            $sheet->getStyle("A{$summaryRow}:B{$summaryRow}")->getFont()->setBold(true);
        }
        
        // Set column widths
        $columnWidths = [
            'A' => 25,  // Organisasi
            'B' => 15,  // Bidang
            'C' => 18,  // Jenis Kegiatan
            'D' => 18,  // Sub Sektor
            'E' => 12,  // Komersial
            'F' => 35,  // Judul Kegiatan
            'G' => 12,  // Tanggal
            'H' => 12,  // Bulan
            'I' => 8,   // Lantai
            'J' => 20,  // Lokasi
            'K' => 10,  // Peserta
            'L' => 25,  // Email
            'M' => 15,  // No Telepon
            'N' => 20,  // PIC
            'O' => 12,  // Status
        ];
        
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    protected function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = floor($index / 26) - 1;
        }
        return $letter;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SUMMARY/ANALYTICS EXPORT FUNCTIONS
    // ══════════════════════════════════════════════════════════════════════════

    protected function exportSummaryXlsx(string $filename): StreamedResponse
    {
        $data = $this->getAnalyticsData();
        $spreadsheet = $this->createSummaryXlsxSpreadsheet($data);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            "{$filename}.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    protected function createSummaryXlsxSpreadsheet(array $data): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Ringkasan
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');
        $this->addSummaryOverviewSheet($sheet, $data);
        
        // Sheet 2: Sub Sektor
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Sub Sektor');
        $this->addCategorySheet($sheet2, $data['kategoriEkraf'], 'Sub Sektor Ekonomi Kreatif');
        
        // Sheet 3: Jenis Kegiatan
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Jenis Kegiatan');
        $this->addCategorySheet($sheet3, $data['kategoriKegiatan'], 'Jenis Kegiatan');
        
        // Sheet 4: Bidang Pelaku
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Bidang Pelaku');
        $this->addCategorySheet($sheet4, $data['kategoriPelaku'], 'Bidang Pelaku');
        
        // Sheet 5: Rekap Bulanan
        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('Rekap Bulanan');
        $this->addMonthlyRecapSheet($sheet5, $data);

        return $spreadsheet;
    }

    protected function addSummaryOverviewSheet($sheet, array $data): void
    {
        $row = 1;
        
        // Header
        $sheet->setCellValue("A{$row}", 'LAPORAN ANALITIK EKONOMI KREATIF');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F2937']],
        ]);
        $row += 2;
        
        $periodLabel = $data['yearStart'] === $data['yearEnd'] 
            ? "Tahun {$data['yearEnd']}"
            : "Periode {$data['yearStart']} - {$data['yearEnd']}";
        
        $sheet->setCellValue("A{$row}", "Periode: {$periodLabel}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '4F46E5']],
        ]);
        $row += 2;
        
        // Summary Stats
        $stats = [
            ['Metrik', 'Nilai'],
            ['Total Event', number_format($data['totalEvents'])],
            ['Total Pengunjung', number_format($data['totalPengunjung'])],
            ['Total Pelaku Unik', number_format($data['totalPelaku'])],
        ];
        
        foreach ($data['komersial'] as $kom) {
            $stats[] = [$kom->komersial, number_format($kom->total)];
        }
        
        $sheet->fromArray($stats, null, "A{$row}");
        
        // Style header
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
    }

    protected function addCategorySheet($sheet, $data, string $title): void
    {
        $row = 1;
        
        // Header
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F2937']],
        ]);
        $row += 2;
        
        // Data
        $tableData = [['No', 'Kategori', 'Jumlah Event', 'Persentase']];
        $total = $data->sum('total');
        
        foreach ($data as $index => $item) {
            $percentage = $total > 0 ? ($item->total / $total * 100) : 0;
            $tableData[] = [
                $index + 1,
                $this->sanitizeSpreadsheetValue($item->label),
                number_format($item->total),
                number_format($percentage, 2) . '%'
            ];
        }
        
        $sheet->fromArray($tableData, null, "A{$row}");
        
        // Style header
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(15);
    }

    protected function addMonthlyRecapSheet($sheet, array $data): void
    {
        $row = 1;
        
        // Header
        $sheet->setCellValue("A{$row}", 'Rekapitulasi Bulanan');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F2937']],
        ]);
        $row += 2;
        
        foreach ($data['bulanan'] as $year => $yearGroup) {
            $monthlyData = $yearGroup['data'];
            $totals = $yearGroup['totals'];

            $sheet->setCellValue("A{$row}", "Tahun {$year}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
            ]);
            $row++;
            
            // Table header
            $headers = ['Bulan', 'Total Event', 'Pengunjung', 'Komersial', 'Non Komersial', 'Pelaku Unik'];
            $sheet->fromArray([$headers], null, "A{$row}");
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            ]);
            $row++;
            
            // Data
            foreach ($monthlyData as $month) {
                $sheet->fromArray([[
                    $month->bulan,
                    (int) $month->total_event,
                    (int) $month->total_pengunjung,
                    (int) $month->total_komersil,
                    (int) $month->total_non_komersil,
                    (int) $month->total_pelaku,
                ]], null, "A{$row}");
                $row++;
            }

            // TOTAL ROW (PENTING: Menghitung Unique Pelaku per Tahun)
            $sheet->setCellValue("A{$row}", 'TOTAL ' . $year);
            $sheet->setCellValue("B{$row}", $totals['event']);
            $sheet->setCellValue("C{$row}", $totals['pengunjung']);
            $sheet->setCellValue("F{$row}", $totals['pelaku']);
            
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THICK],
                ],
            ]);
            
            $row += 3;
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(15);
    }

    protected function exportSummaryPdf(string $filename): StreamedResponse
    {
        $data = $this->getAnalyticsData();
        
        $periodLabel = $data['yearStart'] === $data['yearEnd'] 
            ? "Tahun {$data['yearEnd']}"
            : "Periode {$data['yearStart']} - {$data['yearEnd']}";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.analytics-pdf', [
            'data' => $data,
            'period' => $periodLabel,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false)
        ->setOption('defaultFont', 'DejaVu Sans');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "{$filename}.pdf",
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}.pdf\"",
            ]
        );
    }

    protected function exportSummaryCsv(string $filename): StreamedResponse
    {
        $data = $this->getAnalyticsData();

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Summary
            fputcsv($output, ['RINGKASAN ANALITIK']);
            fputcsv($output, ['Metrik', 'Nilai']);
            fputcsv($output, ['Total Event', $data['totalEvents']]);
            fputcsv($output, ['Total Pengunjung', $data['totalPengunjung']]);
            fputcsv($output, ['Total Pelaku', $data['totalPelaku']]);
            fputcsv($output, []);
            
            // Sub Sektor
            fputcsv($output, ['SUB SEKTOR']);
            fputcsv($output, ['No', 'Kategori', 'Jumlah']);
            foreach ($data['kategoriEkraf'] as $index => $item) {
                fputcsv($output, [$index + 1, $this->sanitizeSpreadsheetValue($item->label), $item->total]);
            }
            fputcsv($output, []);
            
            fclose($output);
        }, "{$filename}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function exportSummaryJson(string $filename): StreamedResponse
    {
        $data = $this->getAnalyticsData();
        
        $export = [
            'metadata' => [
                'title' => 'Laporan Analitik Ekonomi Kreatif MCC',
                'exported_at' => now()->toIso8601String(),
                'period_start' => $data['yearStart'],
                'period_end' => $data['yearEnd'],
            ],
            'summary' => [
                'total_events' => $data['totalEvents'],
                'total_visitors' => $data['totalPengunjung'],
                'total_organizers' => $data['totalPelaku'],
            ],
            'sub_sektor' => $data['kategoriEkraf'],
            'jenis_kegiatan' => $data['kategoriKegiatan'],
            'bidang_pelaku' => $data['kategoriPelaku'],
            'monthly_recap' => $data['bulanan'],
        ];

        return response()->streamDownload(
            fn() => print(json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            "{$filename}.json",
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}