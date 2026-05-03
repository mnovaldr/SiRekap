<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Event;

class RekapBulanan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static string $view = 'filament.pages.rekap-bulanan';
    protected static ?string $navigationLabel = 'Rekap Bulanan';
    protected static ?string $navigationGroup = 'Laporan & Analitik';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Monthly Pulse';

    public string $selectedYear;
    public array $rekap = [];
    public array $totals = [];

    public function mount(): void
    {
        $this->selectedYear = (string) date('Y');
        $this->loadRekap();
    }

    public function updatedSelectedYear(): void
    {
        $this->loadRekap();
    }

    protected function loadRekap(): void
    {
        $monthsMap = [
            1 => 'Januari',   2 => 'Februari',  3 => 'Maret',     4 => 'April',
            5 => 'Mei',       6 => 'Juni',       7 => 'Juli',      8 => 'Agustus',
            9 => 'September', 10 => 'Oktober',   11 => 'November', 12 => 'Desember',
        ];

        // Ambil semua data dalam satu query agregasi (Sangat Cepat!)
        $stats = Event::byYear($this->selectedYear)
            ->where('status_data', '!=', 'Duplikat')
            ->selectRaw("
                bulan,
                COUNT(*) as total_event,
                SUM(jumlah_peserta) as total_peserta,
                COUNT(DISTINCT UPPER(TRIM(nama_organisasi))) as ekraf_unik,
                COUNT(CASE WHEN komersial = 'Non Komersial' THEN 1 END) as non_komersial
            ")
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $rekap = [];
        $grandTotal     = 0;
        $grandPeserta   = 0;
        $grandNonKom    = 0;

        foreach ($monthsMap as $num => $name) {
            $data = $stats->get($name);
            
            $totalEvent   = $data->total_event ?? 0;
            $totalPeserta = $data->total_peserta ?? 0;
            $ekrafUnik    = $data->ekraf_unik ?? 0;
            $nonKom       = $data->non_komersial ?? 0;
            $pctNonKom    = $totalEvent > 0 ? round($nonKom / $totalEvent * 100) : 0;

            $rekap[] = [
                'bulan'          => $name,
                'total_event'    => $totalEvent,
                'total_peserta'  => $totalPeserta,
                'ekraf_unik'     => $ekrafUnik,
                'non_komersial'  => $nonKom,
                'pct_non_kom'    => $pctNonKom,
            ];

            $grandTotal   += $totalEvent;
            $grandPeserta += $totalPeserta;
            $grandNonKom  += $nonKom;
        }

        $this->rekap  = $rekap;
        $this->totals = [
            'total_event'   => $grandTotal,
            'total_peserta' => $grandPeserta,
            'non_komersial' => $grandNonKom,
            'pct_non_kom'   => $grandTotal > 0 ? round($grandNonKom / $grandTotal * 100) : 0,
        ];
    }

    public function getAvailableYears(): array
    {
        $currentYear = (int) date('Y');
        $years = range(2022, $currentYear + 1);
        return array_combine($years, $years);
    }
}