<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class BidangDistributionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    public ?string $filter = 'snapshot';
    protected static bool $isLazy = true;

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
        return 'Kategori Pelaku';
    }

    public function getDescription(): ?string
    {
        $yearStart = $this->filters['year_start'] ?? null;
        $yearEnd   = $this->filters['year_end'] ?? null;
        $month     = $this->filters['month'] ?? null;

        $query = Event::valid();
        if ($this->filter === 'snapshot') {
            $query->byYearRange($yearStart, $yearEnd)->byMonth($month);
        } elseif ($this->filter === 'monthly') {
            $query->byYearRange($yearStart, $yearEnd);
        }

        $total = $query->where('is_primary_row', true)->count();
        return "Total: " . number_format($total) . " Event";
    }

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $yearStart = $this->filters['year_start'] ?? null;
        $yearEnd   = $this->filters['year_end'] ?? null;
        $month     = $this->filters['month'] ?? null;

        $query = Event::select('bidang')
            ->where('is_primary_row', true)
            ->selectRaw('count(*) as count')
            ->valid()
            ->groupBy('bidang')
            ->orderBy('count', 'desc');

        if ($this->filter === 'snapshot') {
            $query->byYearRange($yearStart, $yearEnd)->byMonth($month);
        } elseif ($this->filter === 'monthly') {
            $query->byYearRange($yearStart, $yearEnd);
        }

        $data = $query->get();
        $totalAll = $data->sum('count');

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Event',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => $this->getChartColors(count($data)),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                    'hoverOffset' => 20,
                ],
            ],
            'labels' => $data->map(fn($item) => $item->bidang . ' (' . number_format($item->count) . ')')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getChartColors(int $count): array
    {
        $colors = [
            '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#8b5cf6', '#78716c'
        ];

        if ($count <= count($colors)) {
            return array_slice($colors, 0, $count);
        }

        return array_pad([], $count, '#94a3b8');
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
