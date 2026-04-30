<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopOrganisasiWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.widgets.top-organisasi';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;

    protected function getListeners(): array
    {
        return [
            'updateFilter' => '$refresh',
        ];
    }

    public function getTopOrganisasi(): array
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

        return $query->select('nama_organisasi')
            ->selectRaw('count(*) as total_event')
            ->selectRaw('sum(jumlah_peserta) as total_peserta')
            ->selectRaw('count(DISTINCT judul_kegiatan) as jenis_event')
            ->groupBy('nama_organisasi')
            ->orderBy('total_event', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
