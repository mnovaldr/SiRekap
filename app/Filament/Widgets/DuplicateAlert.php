<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DuplicateAlert extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $year  = $this->filters['year'] ?? null;
        $month = $this->filters['month'] ?? null;
        
        $duplicateCount = Event::byYear($year)
            ->byMonth($month)
            ->whereIn('status_data', ['Duplikat', 'Review'])
            ->count();

        if ($duplicateCount === 0) return [];

        return [
            Stat::make('Perhatian: Data Duplikat/Review', number_format($duplicateCount))
                ->description('Segera lakukan pengecekan pada menu Cek Duplikat')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
