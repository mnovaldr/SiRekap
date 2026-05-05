<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class EventsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    public ?string $filter = 'annual';
    protected static bool $isLazy = true;

    protected function getFilters(): ?array
    {
        return [
            'annual' => 'Tren Tahunan',
            'dynamic' => 'Dinamis (Dashboard)',
        ];
    }

    public function getHeading(): ?string
    {
        $yearStart = $this->filters['year_start'] ?? null;
        $yearEnd   = $this->filters['year_end'] ?? null;
        $month     = $this->filters['month'] ?? null;

        if ($this->filter === 'annual' && !$yearStart && !$yearEnd) {
            return "Tren Performa Tahunan";
        }

        if ($month) {
            return "Tren Harian - " . Event::getBulanIndonesia($month) . " " . ($yearEnd ?: '');
        }

        if ($yearStart && $yearEnd && $yearStart === $yearEnd) {
            return "Tren Bulanan Tahun " . $yearEnd;
        }

        if ($yearStart && $yearEnd) {
            return "Tren Tahunan (" . $yearStart . " - " . $yearEnd . ")";
        }

        return "Tren Performa Tahunan";
    }

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $yearStart = $this->filters['year_start'] ?? null;
        $yearEnd   = $this->filters['year_end'] ?? null;
        $month     = $this->filters['month'] ?? null;

        // Force Annual if filter is 'annual' OR if a range is selected
        if (($this->filter === 'annual' && !$yearStart) || ($yearStart && $yearEnd && $yearStart !== $yearEnd && !$month)) {
            // Jika filter tidak diisi, cari rentang dari data asli di DB
            if (!$yearStart || !$yearEnd) {
                $years = Event::valid()
                    ->selectRaw('MIN(EXTRACT(YEAR FROM tanggal))::integer as min_year, MAX(EXTRACT(YEAR FROM tanggal))::integer as max_year')
                    ->first();
                
                $sY = (int) ($yearStart ?: ($years->min_year ?: date('Y') - 4));
                $eY = (int) ($yearEnd ?: ($years->max_year ?: date('Y')));
            } else {
                $sY = (int) $yearStart;
                $eY = (int) $yearEnd;
            }

            // Batasi agar rentang tidak terlalu ekstrem
            if (!$yearStart && !$yearEnd && ($eY - $sY) > 10) {
                $sY = $eY - 9;
            }

            $labels = range($sY, $eY);

            // Query Event dengan GROUP BY yang benar untuk PostgreSQL
            $eventData = Event::valid()
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(YEAR FROM tanggal)::integer as year_val, count(*) as count')
                ->whereRaw('EXTRACT(YEAR FROM tanggal) BETWEEN ? AND ?', [$sY, $eY])
                ->groupBy(DB::raw('EXTRACT(YEAR FROM tanggal)'))
                ->get()->pluck('count', 'year_val')->toArray();

            // Query Peserta
            $pesertaData = Event::valid()
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(YEAR FROM tanggal)::integer as year_val, sum(jumlah_peserta) as total')
                ->whereRaw('EXTRACT(YEAR FROM tanggal) BETWEEN ? AND ?', [$sY, $eY])
                ->groupBy(DB::raw('EXTRACT(YEAR FROM tanggal)'))
                ->get()->pluck('total', 'year_val')->toArray();

            $eventCounts = [];
            $pesertaCounts = [];
            foreach ($labels as $y) {
                $eventCounts[] = (int) ($eventData[$y] ?? 0);
                $pesertaCounts[] = (int) ($pesertaData[$y] ?? 0);
            }
        } elseif ($month) {
            // VIEW HARIAN (Jika Bulan dipilih)
            $y = $yearEnd ?: date('Y');
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$y);
            
            $eventData = Event::valid()->byYear($y)->byMonth($month)
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(DAY FROM tanggal)::integer as day_val, count(*) as count')
                ->groupBy(DB::raw('EXTRACT(DAY FROM tanggal)'))
                ->get()->pluck('count', 'day_val')->toArray();

            $pesertaData = Event::valid()->byYear($y)->byMonth($month)
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(DAY FROM tanggal)::integer as day_val, sum(jumlah_peserta) as total')
                ->groupBy(DB::raw('EXTRACT(DAY FROM tanggal)'))
                ->get()->pluck('total', 'day_val')->toArray();

            $labels = [];
            $eventCounts = [];
            $pesertaCounts = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = $i;
                $eventCounts[] = $eventData[$i] ?? 0;
                $pesertaCounts[] = $pesertaData[$i] ?? 0;
            }
        } else {
            // VIEW BULANAN (Jika Tahun Start == Tahun End ATAU Default Tahun Sekarang)
            $y = $yearEnd ?: date('Y');
            
            $eventData = Event::valid()->byYear($y)
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(MONTH FROM tanggal)::integer as month_val, count(*) as count')
                ->groupBy(DB::raw('EXTRACT(MONTH FROM tanggal)'))
                ->get()->pluck('count', 'month_val')->toArray();

            $pesertaData = Event::valid()->byYear($y)
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(MONTH FROM tanggal)::integer as month_val, sum(jumlah_peserta) as total')
                ->groupBy(DB::raw('EXTRACT(MONTH FROM tanggal)'))
                ->get()->pluck('total', 'month_val')->toArray();

            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $eventCounts = [];
            $pesertaCounts = [];
            for ($i = 1; $i <= 12; $i++) {
                $eventCounts[] = $eventData[$i] ?? 0;
                $pesertaCounts[] = $pesertaData[$i] ?? 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Event',
                    'data' => $eventCounts,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)', // Indigo
                    'borderColor' => '#4f46e5',
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Total Peserta',
                    'data' => $pesertaCounts,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)', // Emerald
                    'borderColor' => '#10b981',
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'align' => 'center',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'font' => [
                            'family' => "'Inter', 'sans-serif'",
                            'size' => 11,
                        ],
                        'color' => '#64748b',
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => '#1e293b',
                    'padding' => 12,
                    'titleFont' => ['size' => 14],
                    'cornerRadius' => 8,
                ]
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => [
                        'color' => '#94a3b8',
                        'font' => ['family' => "'Inter', 'sans-serif'", 'size' => 10]
                    ],
                ],
                'y' => [
                    'position' => 'left',
                    'grid' => ['color' => '#f1f5f9'],
                    'ticks' => [
                        'color' => '#94a3b8',
                        'font' => ['family' => "'Inter', 'sans-serif'", 'size' => 10]
                    ],
                ],
                'y1' => [
                    'position' => 'right',
                    'grid' => ['display' => false],
                    'ticks' => [
                        'color' => '#10b981',
                        'font' => ['family' => "'Inter', 'sans-serif'", 'size' => 10]
                    ],
                ],
            ],
        ];
    }
}
