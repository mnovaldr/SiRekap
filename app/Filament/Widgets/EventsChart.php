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

    protected function getFilters(): ?array
    {
        return [
            'annual' => 'Tren Tahunan',
            'dynamic' => 'Dinamis (Dashboard)',
        ];
    }

    public function getHeading(): ?string
    {
        $year  = $this->filters['year'] ?? null;
        $month = $this->filters['month'] ?? null;

        if ($this->filter === 'annual') {
            return "Tren Performa Tahunan";
        }

        if ($month) {
            return "Tren Harian - " . Event::getBulanIndonesia($month) . " " . ($year ?: '');
        }

        if ($year) {
            return "Tren Bulanan Tahun " . $year;
        }

        return "Tren Performa Tahunan";
    }

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $year  = $this->filters['year'] ?? null;
        $month = $this->filters['month'] ?? null;

        // Force Annual if filter is 'annual' OR if no global filters are set
        if ($this->filter === 'annual' || (!$month && !$year)) {
            // VIEW TAHUNAN (Multi-Tahun)
            $eventData = Event::select(DB::raw('YEAR(tanggal) as year'), DB::raw('count(*) as count'))
                ->valid()
                ->groupBy('year')->orderBy('year')
                ->get()->pluck('count', 'year')->toArray();

            $pesertaData = Event::select(DB::raw('YEAR(tanggal) as year'), DB::raw('sum(jumlah_peserta) as total'))
                ->valid()
                ->groupBy('year')->orderBy('year')
                ->get()->pluck('total', 'year')->toArray();

            $labels = array_keys($eventData);
            if (empty($labels)) {
                $labels = [date('Y')];
            }
            
            $eventCounts = array_values($eventData);
            $pesertaCounts = array_values($pesertaData);
        } elseif ($month) {
            // VIEW HARIAN (Jika Bulan dipilih)
            $y = $year ?: date('Y');
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$y);
            
            $eventData = Event::select(DB::raw('DAY(tanggal) as day'), DB::raw('count(*) as count'))
                ->valid()->byYear($y)->byMonth($month)
                ->groupBy('day')->orderBy('day')
                ->get()->pluck('count', 'day')->toArray();

            $pesertaData = Event::select(DB::raw('DAY(tanggal) as day'), DB::raw('sum(jumlah_peserta) as total'))
                ->valid()->byYear($y)->byMonth($month)
                ->groupBy('day')->orderBy('day')
                ->get()->pluck('total', 'day')->toArray();

            $labels = [];
            $eventCounts = [];
            $pesertaCounts = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = $i;
                $eventCounts[] = $eventData[$i] ?? 0;
                $pesertaCounts[] = $pesertaData[$i] ?? 0;
            }
        } else {
            // VIEW BULANAN (Jika Tahun dipilih & filter dynamic)
            $eventData = Event::select(DB::raw('MONTH(tanggal) as month'), DB::raw('count(*) as count'))
                ->valid()->byYear($year)
                ->groupBy('month')->orderBy('month')
                ->get()->pluck('count', 'month')->toArray();

            $pesertaData = Event::select(DB::raw('MONTH(tanggal) as month'), DB::raw('sum(jumlah_peserta) as total'))
                ->valid()->byYear($year)
                ->groupBy('month')->orderBy('month')
                ->get()->pluck('total', 'month')->toArray();

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
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'color' => '#0f172a', // Slate-900
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#64748b', // Slate-500
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => false,
                    ],
                    'grid' => [
                        'color' => '#f1f5f9', // Slate-100
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'display' => false,
                    ],
                    'title' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
