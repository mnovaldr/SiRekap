<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ActivityStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;


    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        [$today, $thisWeek, $imports, $deletes] = Cache::remember(
            'activity_stats_widget',
            now()->addMinutes(5),
            function () {
                return [
                    ActivityLog::whereDate('created_at', today())->count(),
                    ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    ActivityLog::where('action', 'import')->count(),
                    ActivityLog::where('action', 'delete')->count(),
                ];
            }
        );

        return [
            Stat::make('Aktivitas Hari Ini', $today)
                ->description('Log tercatat hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Aktivitas Minggu Ini', $thisWeek)
                ->description('Log 7 hari terakhir')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Total Import', $imports)
                ->description('Operasi import Excel')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success'),

            Stat::make('Total Hapus', $deletes)
                ->description('Operasi delete data')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}
