<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Livewire\WithPagination;
use Filament\Notifications\Notification;
use App\Models\Event;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportData extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string $view = 'filament.pages.export-data';
    protected static ?string $navigationLabel = 'Export Data';
    protected static ?string $navigationGroup = 'Import / Export';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Export Data Event';

    public string $selectedFormat = 'xlsx';
    public string $selectedYear;
    public string $selectedMonth = 'Semua';
    public int $totalRows = 0;
    public int $perPage = 10;

    public array $formats = [
        ['id' => 'xlsx', 'icon' => '📊', 'name' => 'Excel (.xlsx)', 'desc' => 'Standard spreadsheet format.'],
        ['id' => 'csv',  'icon' => '📄', 'name' => 'CSV (.csv)',     'desc' => 'Plain text data format.'],
        ['id' => 'json', 'icon' => '🔷', 'name' => 'JSON (.json)',   'desc' => 'Structured data for API.'],
    ];

    public array $months = [
        'Semua' => 'Semua Bulan (Jan-Des)',
        '01' => 'Januari',   '02' => 'Februari', '03' => 'Maret',
        '04' => 'April',     '05' => 'Mei',       '06' => 'Juni',
        '07' => 'Juli',      '08' => 'Agustus',   '09' => 'September',
        '10' => 'Oktober',   '11' => 'November',  '12' => 'Desember',
    ];

    public function mount(): void
    {
        $this->selectedYear = (string) date('Y');
        $this->refreshCount();
    }

    public function updatedSelectedYear(): void { $this->refreshCount(); $this->resetPage(); }
    public function updatedSelectedMonth(): void { $this->refreshCount(); $this->resetPage(); }

    protected function refreshCount(): void
    {
        [$start, $end] = $this->getDateRange();
        $this->totalRows = Event::whereBetween('tanggal', [$start, $end])->count();
    }

    protected function getDateRange(): array
    {
        $y = $this->selectedYear;
        if ($this->selectedMonth === 'Semua') {
            return ["{$y}-01-01", "{$y}-12-31"];
        }
        $m = $this->selectedMonth;
        $last = date('t', mktime(0, 0, 0, (int)$m, 1, (int)$y));
        return ["{$y}-{$m}-01", "{$y}-{$m}-{$last}"];
    }

    public function getPreviewData()
    {
        [$start, $end] = $this->getDateRange();
        return Event::whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'desc')
            ->paginate($this->perPage);
    }

    public function export(): StreamedResponse|null
    {
        if ($this->totalRows === 0) {
            Notification::make()->title('Tidak ada data untuk diekspor')->warning()->send();
            return null;
        }

        [$start, $end] = $this->getDateRange();
        $data = Event::whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal')
            ->get([
                'nama_organisasi','bidang','jenis_kegiatan','sub_sektor','komersial',
                'judul_kegiatan','tanggal','bulan','lantai','lokasi',
                'jumlah_peserta','email','no_telepon','pic','status_data'
            ])
            ->toArray();

        $filename = 'MCC_Export_' . ($this->selectedMonth === 'Semua' ? "Tahun_{$this->selectedYear}" : "{$this->selectedMonth}_{$this->selectedYear}");

        if ($this->selectedFormat === 'json') {
            return response()->streamDownload(function () use ($data) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }, "{$filename}.json", ['Content-Type' => 'application/json']);
        }

        if ($this->selectedFormat === 'csv') {
            return response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');
                if (!empty($data)) {
                    fputcsv($out, array_keys($data[0]));
                    foreach ($data as $row) fputcsv($out, $row);
                }
                fclose($out);
            }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        // XLSX
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Event');

        if (!empty($data)) {
            $headers = array_keys($data[0]);
            $sheet->fromArray([$headers], null, 'A1');
            foreach ($data as $i => $row) {
                $sheet->fromArray([array_values($row)], null, 'A' . ($i + 2));
            }
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        }

        $writer = new XlsxWriter($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "{$filename}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function getAvailableYears(): array
    {
        $years = Event::selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        return !empty($years) ? $years : [date('Y') => date('Y')];
    }
}
