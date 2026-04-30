<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopLokasiWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.widgets.top-lokasi';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;

    protected function getListeners(): array
    {
        return [
            'updateFilter' => '$refresh',
        ];
    }

    public function getTopLokasi(): array
    {
        $year  = $this->filters['year'] ?? null;
        $month = $this->filters['month'] ?? null;

        $query = Event::query()->valid();

        if ($year) {
            $query->whereYear('tanggal', $year);
        }

        if ($month) {
            $query->whereMonth('tanggal', $month);
        }

        return $query->select('lokasi')
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(jumlah_peserta) as total_peserta')
            ->groupBy('lokasi')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
