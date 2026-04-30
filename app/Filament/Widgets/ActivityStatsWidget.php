<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityStatsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $today     = ActivityLog::whereDate('created_at', today())->count();
        $thisWeek  = ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $imports   = ActivityLog::where('action', 'import')->count();
        $deletes   = ActivityLog::where('action', 'delete')->count();

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
