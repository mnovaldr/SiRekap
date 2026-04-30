<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class KomersialChart extends ChartWidget
{
    use InteractsWithPageFilters;

    public ?string $filter = 'snapshot';

    protected function getFilters(): ?array
    {
        return [
            'snapshot' => 'Snapshot (Periode Terpilih)',
            'monthly' => 'Tren Bulanan (Total Tahun Ini)',
            'annual' => 'Tren Tahunan (Total Seluruh Data)',
        ];
    }

    public function getHeading(): ?string
    {
        return 'Komersial vs Non-Komersial';
    }

    public function getDescription(): ?string
    {
        $year  = $this->filters['year'] ?? null;
        $month = $this->filters['month'] ?? null;
        
        $query = Event::valid();
        if ($this->filter === 'snapshot') {
            $query->byYear($year)->byMonth($month);
        } elseif ($this->filter === 'monthly') {
            $query->byYear($year);
        }

        $total = $query->count();
        return "Total: " . number_format($total) . " Event";
    }

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $year  = $this->filters['year'] ?? null;
        $month = $this->filters['month'] ?? null;

        $query = Event::valid();
        if ($this->filter === 'snapshot') {
            $query->byYear($year)->byMonth($month);
        } elseif ($this->filter === 'monthly') {
            $query->byYear($year);
        }

        $komersial = (clone $query)->where('komersial', 'Komersial')->count();
        $nonKomersial = (clone $query)->where('komersial', 'Non Komersial')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Event',
                    'data' => [$komersial, $nonKomersial],
                    'backgroundColor' => [
                        '#4f46e5', // Indigo
                        '#10b981', // Emerald
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Komersial', 'Non-Komersial'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '65%',
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'align' => 'start',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => [
                            'family' => "'Inter', 'sans-serif'",
                            'size' => 11,
                        ],
                        'color' => '#475569',
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value / total) * 100);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }"
                    ]
                ]
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
