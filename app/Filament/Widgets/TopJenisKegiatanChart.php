<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class TopJenisKegiatanChart extends ChartWidget
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
        return 'Kategori Kegiatan';
    }

    public function getDescription(): ?string
    {
        // Reuse getData() result to avoid a separate COUNT query
        $data = $this->getData();
        $total = array_sum($data['datasets'][0]['data'] ?? []);
        return "Total: " . number_format($total) . " Event";
    }

    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $cacheKey = "top_jenis_kegiatan_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$this->filter}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor) {
            $query = Event::select('jenis_kegiatan')
                ->where('is_primary_row', true)
                ->selectRaw('count(*) as count')
                ->valid()
                ->bySubSektor($subSektor)
                ->groupBy('jenis_kegiatan')
                ->orderBy('count', 'desc');

            if ($this->filter === 'snapshot') {
                $query->byYearRange($yearStart, $yearEnd)
                    ->byMonth($month);
            } elseif ($this->filter === 'monthly') {
                $query->byYearRange($yearStart, $yearEnd);
            }

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
                'labels' => $data->map(fn($item) => $item->jenis_kegiatan . ' (' . number_format($item->count) . ')')->toArray(),
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
            '#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#8b5cf6', '#6366f1',
            '#14b8a6', '#f97316', '#3b82f6', '#d946ef', '#f43f5e', '#84cc16', '#0ea5e9', '#475569'
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
