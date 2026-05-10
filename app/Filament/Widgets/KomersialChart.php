<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class KomersialChart extends ChartWidget
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
        return 'Komersial vs Non-Komersial';
    }

    public function getDescription(): ?string
    {
        // Reuse getData() result to avoid a separate COUNT query
        $data = $this->getData();
        $total = array_sum($data['datasets'][0]['data'] ?? []);
        return "Total: " . number_format($total) . " Event";
    }

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $cacheKey = "komersial_chart_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$this->filter}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor) {
            $query = Event::valid()
                ->primary()
                ->bySubSektor($subSektor);
            
            if ($this->filter === 'snapshot') {
                $query->byYearRange($yearStart, $yearEnd)
                    ->byMonth($month);
            } elseif ($this->filter === 'monthly') {
                $query->byYearRange($yearStart, $yearEnd);
            }

            // Single query instead of 3 separate queries
            $row = $query->selectRaw("
                COUNT(CASE WHEN komersial = 'Komersial' THEN 1 END) as komersial,
                COUNT(CASE WHEN komersial = 'Non Komersial' THEN 1 END) as non_komersial
            ")->first();

            $komersial    = (int) ($row->komersial ?? 0);
            $nonKomersial = (int) ($row->non_komersial ?? 0);

            $total = $komersial + $nonKomersial;
            $pctKomersial = $total > 0 ? round(($komersial / $total) * 100, 1) : 0;
            $pctNonKomersial = $total > 0 ? round(($nonKomersial / $total) * 100, 1) : 0;

            return [
                'datasets' => [
                    [
                        'label' => 'Event',
                        'data' => [$komersial, $nonKomersial],
                        'backgroundColor' => [
                            '#4f46e5', // Indigo
                            '#10b981', // Emerald
                        ],
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                        'hoverOffset' => 20,
                    ],
                ],
                'labels' => [
                    'Komersial: ' . number_format($komersial) . ' (' . $pctKomersial . '%)',
                    'Non Komersial: ' . number_format($nonKomersial) . ' (' . $pctNonKomersial . '%)',
                ],
            ];
        });
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
