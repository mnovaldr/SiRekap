<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;

    protected function getListeners(): array
    {
        return [
            'updateFilter' => '$refresh',
        ];
    }

    protected function getStats(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $query     = Event::valid()
            ->byYearRange($yearStart, $yearEnd)
            ->byMonth($month)
            ->bySubSektor($subSektor);
        $baseQuery = (clone $query);

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
                ->select('bulan', DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as count'))
                ->groupBy('bulan')
                ->get()
                ->sum('count');
        }

        $stats = (object) [
            'total_events'        => $statsRow->total_events ?? 0,
            'total_peserta'       => $statsRow->total_peserta ?? 0,
            'total_non_komersial' => $statsRow->total_non_komersial ?? 0,
            'ekraf_unik'          => $ekrafUnik,
        ];

        $totalEvents       = $stats->total_events;
        $totalPeserta      = $stats->total_peserta;
        $totalNonKomersial = $stats->total_non_komersial;
        $ekrafUnik         = $stats->ekraf_unik;

        $percentNonKomersial = $totalEvents > 0 ? round(($totalNonKomersial / $totalEvents) * 100, 1) : 0;

        $yearLabel   = ($yearStart === $yearEnd) ? $yearStart : "$yearStart – $yearEnd";
        $periodLabel = ($month ? Event::getBulanIndonesia($month) . ' ' : '') . $yearLabel;
        if ($subSektor) {
            $periodLabel .= " — $subSektor";
        }

        $totalKomersial   = $totalEvents - $totalNonKomersial;
        $percentKomersial = $totalEvents > 0 ? round(($totalKomersial / $totalEvents) * 100, 1) : 0;

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
