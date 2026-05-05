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
        $yearStart = $this->filters['year_start'] ?? null;
        $yearEnd   = $this->filters['year_end'] ?? null;
        $month     = $this->filters['month'] ?? null;
        
        $query = Event::valid()->where('is_primary_row', true);
        if ($this->filter === 'snapshot') {
            $query->byYearRange($yearStart, $yearEnd)->byMonth($month);
        } elseif ($this->filter === 'monthly') {
            $query->byYearRange($yearStart, $yearEnd);
        }

        $total = $query->where('is_primary_row', true)->count();
        return "Total: " . number_format($total) . " Event";
    }

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $yearStart = $this->filters['year_start'] ?? null;
        $yearEnd   = $this->filters['year_end'] ?? null;
        $month     = $this->filters['month'] ?? null;

        $query = Event::valid()->where('is_primary_row', true);
        if ($this->filter === 'snapshot') {
            $query->byYearRange($yearStart, $yearEnd)->byMonth($month);
        } elseif ($this->filter === 'monthly') {
            $query->byYearRange($yearStart, $yearEnd);
        }

        $komersial = (clone $query)->where('komersial', 'Komersial')->count();
        $nonKomersial = (clone $query)->where('komersial', 'Non Komersial')->count();

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
