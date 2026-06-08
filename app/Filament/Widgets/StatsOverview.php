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

    protected function getStats(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode] = $this->resolveFilters();

        $cacheKey = "stats_overview_v2_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$bidang}_{$jenisKegiatan}_{$komersial}_{$sdg}_{$displayMode}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode) {
            $query     = Event::valid();
            $query     = $this->applyEventFilters($query);

            
            $baseQuery = (clone $query);

            $statsRow = $query->selectRaw('
                COUNT(*) as total_events,
                COALESCE(SUM("jumlah_peserta"), 0) as total_peserta,
                COUNT(DISTINCT CASE WHEN "nama_organisasi" IS NOT NULL AND "nama_organisasi" != \'\' THEN "nama_organisasi" END) as ekraf_unik,
                COUNT(CASE WHEN "komersial" = \'Non Komersial\' THEN 1 END) as total_non_komersial
            ')->first();


            $ekrafUnik = $statsRow->ekraf_unik ?? 0;
            
            if (!$month) {
                $ekrafUnik = $baseQuery
                    ->whereNotNull('tanggal')
                    ->selectRaw('EXTRACT(MONTH FROM tanggal) as bulan_num, COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN nama_organisasi END) as count')
                    ->groupBy(DB::raw('EXTRACT(MONTH FROM tanggal)'))
                    ->get()
                    ->sum('count');
            }

            $totalEvents       = $statsRow->total_events ?? 0;
            $totalPeserta      = $statsRow->total_peserta ?? 0;
            $totalNonKomersial = $statsRow->total_non_komersial ?? 0;

            $percentNonKomersial = $totalEvents > 0 ? round(($totalNonKomersial / $totalEvents) * 100, 1) : 0;

            $yearLabel   = ($yearStart === $yearEnd) ? $yearStart : "$yearStart – $yearEnd";
            $periodLabel = ($month ? Event::getBulanIndonesia($month) . ' ' : '') . $yearLabel;
            if ($subSektor) {
                $periodLabel .= " — $subSektor";
            }
            if ($bidang) {
                $periodLabel .= " — $bidang";
            }
            if ($jenisKegiatan) {
                $periodLabel .= " — $jenisKegiatan";
            }
            if ($komersial) {
                $periodLabel .= " — $komersial";
            }
            if ($sdg) {
                $periodLabel .= " — $sdg";
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
        });
    }
}
