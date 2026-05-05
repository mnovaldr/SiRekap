<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;

class SubSektorDistributionChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    public ?string $filter = 'snapshot';
    protected static bool $isLazy = false;

    protected function getFilters(): ?array
    {
        return [
            'snapshot' => 'Snapshot (Periode Terpilih)',
            'monthly' => 'Tren Bulanan (Total Tahun Ini)',
            'annual' => 'Tren Tahunan (Total Seluruh Data)',
        ];
    }

    protected function getListeners(): array
    {
        return [
            'updateFilter' => '$refresh',
        ];
    }

    public function getHeading(): ?string
    {
        return 'Kategori Ekraf (Sub Sektor)';
    }

    public function getDescription(): ?string
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();
        
        $query = Event::valid();
        if ($this->filter === 'snapshot') {
            $query->byYearRange($yearStart, $yearEnd)
                ->byMonth($month)
                ->bySubSektor($subSektor);
        } elseif ($this->filter === 'monthly') {
            $query->byYearRange($yearStart, $yearEnd)
                ->bySubSektor($subSektor);
        }

        $total = $query->where('is_primary_row', true)->count();
        return "Total: " . number_format($total) . " Event";
    }

    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $query = Event::select('sub_sektor')
            ->where('is_primary_row', true)
            ->selectRaw('count(*) as count')
            ->valid()
            ->whereNotNull('sub_sektor')
            ->where('sub_sektor', '!=', '')
            ->groupBy('sub_sektor')
            ->orderBy('count', 'desc');

        if ($this->filter === 'snapshot') {
            $query->byYearRange($yearStart, $yearEnd)
                ->byMonth($month)
                ->bySubSektor($subSektor);
        } elseif ($this->filter === 'monthly') {
            $query->byYearRange($yearStart, $yearEnd)
                ->bySubSektor($subSektor);
        }
        // annual: no extra constraints

        $data = $query->get();

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
            'labels' => $data->map(fn($item) => ($item->sub_sektor ?: 'Non Kategori') . ' (' . number_format($item->count) . ')')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getChartColors(int $count): array
    {
        // Palet warna yang luas untuk banyak kategori
        $colors = [
            '#f43f5e', '#ec4899', '#d946ef', '#a855f7', '#8b5cf6', '#6366f1', '#3b82f6', '#0ea5e9',
            '#06b6d4', '#14b8a6', '#10b981', '#22c55e', '#84cc16', '#eab308', '#f59e0b', '#f97316',
            '#ef4444', '#78716c', '#71717a', '#525252', '#4b5563', '#475569', '#334155', '#1e293b'
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
                    'align' => 'center',
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
