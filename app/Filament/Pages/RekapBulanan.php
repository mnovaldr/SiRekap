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
        $months = [
            1 => 'January',   2 => 'February',  3 => 'March',     4 => 'April',
            5 => 'May',       6 => 'June',       7 => 'July',      8 => 'August',
            9 => 'September', 10 => 'October',   11 => 'November', 12 => 'December',
        ];

        $rekap = [];
        $grandTotal     = 0;
        $grandPeserta   = 0;
        $grandNonKom    = 0;

        foreach ($months as $num => $name) {
            $base = Event::byYear($this->selectedYear)
                ->byMonth($num)
                ->where('status_data', '!=', 'Duplikat');

            $totalEvent   = $base->count();
            $totalPeserta = $base->sum('jumlah_peserta');
            $ekrafUnik    = $base->count(\Illuminate\Support\Facades\DB::raw('DISTINCT UPPER(TRIM(nama_organisasi))'));
            $nonKom       = $base->where('komersial', 'Non Komersial')->count();
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