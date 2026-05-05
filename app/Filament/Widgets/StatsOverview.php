<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $yearStart = $this->filters['year_start'] ?? null;
        $yearEnd   = $this->filters['year_end'] ?? null;
        $month     = $this->filters['month'] ?? null;
        
        $query = Event::valid()->byYearRange($yearStart, $yearEnd)->byMonth($month);
        
        $baseQuery = (clone $query);
        
        $cacheKey = "stats_overview_{$yearStart}_{$yearEnd}_{$month}";
        
        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query, $baseQuery, $month) {
            $statsRow = $query->selectRaw('
                COUNT(CASE WHEN is_primary_row = true THEN 1 END) as total_events,
                SUM(CASE WHEN is_primary_row = true THEN jumlah_peserta ELSE 0 END) as total_peserta,
                COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as ekraf_unik,
                COUNT(CASE WHEN is_primary_row = true AND komersial = \'Non Komersial\' THEN 1 END) as total_non_komersial
            ')->first();

            $ekrafUnik = $statsRow->ekraf_unik ?? 0;
            if (!$month) {
                $ekrafUnik = $baseQuery
                    ->whereNotNull('bulan')
                    ->where('bulan', '!=', '')
                    ->select('bulan', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as count'))
                    ->groupBy('bulan')
                    ->get()
                    ->sum('count');
            }

            return (object) [
                'total_events'       => $statsRow->total_events ?? 0,
                'total_peserta'      => $statsRow->total_peserta ?? 0,
                'total_non_komersial'=> $statsRow->total_non_komersial ?? 0,
                'ekraf_unik'         => $ekrafUnik,
            ];
        });

        $totalEvents         = $stats->total_events;
        $totalPeserta        = $stats->total_peserta;
        $totalNonKomersial   = $stats->total_non_komersial;
        $ekrafUnik           = $stats->ekraf_unik;

        $percentNonKomersial = $totalEvents > 0
            ? round(($totalNonKomersial / $totalEvents) * 100, 1)
            : 0;

        $periodLabel = ($month ? Event::getBulanIndonesia($month) : '');
        if ($yearStart && $yearEnd) {
            $yearLabel = ($yearStart === $yearEnd) ? $yearStart : "$yearStart - $yearEnd";
            $periodLabel .= ($periodLabel ? ' ' : '') . $yearLabel;
        }
        $periodLabel = trim($periodLabel) ?: 'Semua Periode';

        $totalKomersial = $totalEvents - $totalNonKomersial;
        $percentKomersial = $totalEvents > 0
            ? round(($totalKomersial / $totalEvents) * 100, 1)
            : 0;

        return [
            Stat::make('Total Event (Valid)', number_format($totalEvents))
                ->description('Kegiatan valid: ' . $periodLabel)
                ->descriptionIcon('heroicon-m-calendar')
                ->chart([7, 3, 4, 5, 6, 3, 5, 2, 3, 4, 5, 6])
                ->color('primary'),
                
            Stat::make('Utilitas Komersial', $percentKomersial . '%')
                ->description($totalKomersial . ' dari ' . $totalEvents . ' event')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Utilitas Non-Komersial', $percentNonKomersial . '%')
                ->description($totalNonKomersial . ' dari ' . $totalEvents . ' event')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info'),

            Stat::make('Organisasi Unik', number_format($ekrafUnik))
                ->description('Pelaku ekraf berkegiatan')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
        ];
    }
}
