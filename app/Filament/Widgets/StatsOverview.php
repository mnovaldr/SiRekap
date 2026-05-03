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

    protected function getStats(): array
    {
        $year  = $this->filters['year'] ?? null;
        $month = $this->filters['month'] ?? null;
        
        $query = Event::valid()->byYear($year)->byMonth($month);
        
        $stats = $query->selectRaw('
            COUNT(*) as total_events,
            SUM(jumlah_peserta) as total_peserta,
            COUNT(DISTINCT nama_organisasi) as ekraf_unik,
            COUNT(CASE WHEN komersial = \'Non Komersial\' THEN 1 END) as total_non_komersial
        ')->first();
        
        $totalEvents         = $stats->total_events ?? 0;
        $totalPeserta        = $stats->total_peserta ?? 0;
        $ekrafUnik           = $stats->ekraf_unik ?? 0;
        $totalNonKomersial   = $stats->total_non_komersial ?? 0;

        $percentNonKomersial = $totalEvents > 0
            ? round(($totalNonKomersial / $totalEvents) * 100, 1)
            : 0;

        $periodLabel = ($month ? Event::getBulanIndonesia($month) : '') . ($year ? ' ' . $year : '');
        $periodLabel = trim($periodLabel) ?: 'Semua Periode';

        return [
            Stat::make('Total Event (Valid)', number_format($totalEvents))
                ->description('Kegiatan valid: ' . $periodLabel)
                ->descriptionIcon('heroicon-m-calendar')
                ->chart([7, 3, 4, 5, 6, 3, 5, 2, 3, 4, 5, 6])
                ->color('primary'),
                
            Stat::make('Total Peserta', number_format($totalPeserta))
                ->description('Peserta event valid')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([3, 5, 2, 7, 5, 6, 4, 8, 3, 5, 6, 7])
                ->color('success'),
                
            Stat::make('Organisasi Unik', number_format($ekrafUnik))
                ->description('Pelaku ekraf berkegiatan')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
                
            Stat::make('Non-Komersial', $percentNonKomersial . '%')
                ->description($totalNonKomersial . ' dari ' . $totalEvents . ' event')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info'),
        ];
    }
}
