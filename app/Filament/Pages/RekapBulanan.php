<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

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
        $this->selectedYear = (string) (Event::whereNotNull('tanggal')->max(DB::raw('EXTRACT(YEAR FROM tanggal)')) ?? date('Y'));
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
                COUNT(CASE WHEN is_primary_row = true THEN 1 END) as total_event,
                SUM(CASE WHEN is_primary_row = true THEN jumlah_peserta ELSE 0 END) as total_peserta,
                COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != '' THEN UPPER(TRIM(nama_organisasi)) END) as ekraf_unik,
                COUNT(CASE WHEN is_primary_row = true AND komersial = 'Non Komersial' THEN 1 END) as non_komersial
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
        return Event::selectRaw('EXTRACT(YEAR FROM tanggal)::integer as year')
            ->whereNotNull('tanggal')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray() ?: [date('Y') => date('Y')];
    }
}