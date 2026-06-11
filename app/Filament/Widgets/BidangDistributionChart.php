<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class BidangDistributionChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

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
        // Reuse getData() result to avoid a separate COUNT query
        $data = $this->getData();
        $total = array_sum($data['datasets'][0]['data'] ?? []);
        return "Total: " . number_format($total) . " Event";
    }

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode] = $this->resolveFilters();

        $cacheKey = "bidang_dist_v5_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$bidang}_{$jenisKegiatan}_{$komersial}_{$sdg}_{$displayMode}_{$this->filter}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode) {
            $query = Event::select('bidang')
                ->selectRaw('count(*) as count')
                ->valid();


            $query = $this->applyEventFilters($query, ['year', 'month']);

            if ($this->filter === 'snapshot') {
                $query->byYearRange($yearStart, $yearEnd)
                    ->byMonth($month);
            } elseif ($this->filter === 'monthly') {
                $query->byYearRange($yearStart, $yearEnd);
            }

            $data = $query->groupBy('bidang')
                ->orderBy('count', 'desc')
                ->get();


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
        });
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getChartColors(int $count): array
    {
        $colors = [
            '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#8b5cf6', '#78716c',
            '#4f46e5', '#0ea5e9', '#f43f5e', '#f97316', '#14b8a6', '#d946ef', '#84cc16', '#a855f7'
        ];

        $result = [];
        $numColors = count($colors);
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % $numColors];
        }

        return $result;
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
