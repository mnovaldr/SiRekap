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
    protected static bool $isLazy = false;



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

        $cacheKey = "top_jenis_keg_v5_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$this->filter}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor) {
            $query = Event::select('jenis_kegiatan')
                ->primary()
                ->selectRaw('count(*) as count')
                ->valid()
                ->bySubSektor($subSektor)
                ->groupBy('jenis_kegiatan')
                ->orderBy('count', 'desc');

            if ($this->filter === 'snapshot') {
                $query->whereRaw('EXTRACT(YEAR FROM "tanggal") BETWEEN ? AND ?', [(int)$yearStart, (int)$yearEnd])
                    ->when($month, fn($q) => $q->whereRaw('EXTRACT(MONTH FROM "tanggal") = ?', [(int)$month]));
            } elseif ($this->filter === 'monthly') {
                $query->whereRaw('EXTRACT(YEAR FROM "tanggal") BETWEEN ? AND ?', [(int)$yearStart, (int)$yearEnd]);
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
            '#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#f43f5e', 
            '#8b5cf6', '#f97316', '#06b6d4', '#ec4899', '#14b8a6', 
            '#3b82f6', '#d946ef', '#84cc16', '#ef4444', '#a855f7', 
            '#eab308', '#22c55e', '#64748b', '#38bdf8', '#fb7185',
            '#818cf8', '#34d399', '#fbcfe8', '#fbbf24', '#a78bfa'
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
