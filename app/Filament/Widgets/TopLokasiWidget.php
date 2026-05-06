<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;

class TopLokasiWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static string $view = 'filament.widgets.top-lokasi';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = true;



    public function getTopLokasiData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $query = Event::query()->valid();
        $query->byYearRange($yearStart, $yearEnd)->bySubSektor($subSektor);

        if ($month) {
            $query->whereMonth('tanggal', $month);
        }

        // Hitung Grand Total Event Unik (Primary Row)
        $grandTotal = (clone $query)
            ->where('is_primary_row', true)
            ->count() ?: 1;

        $data = $query->select('lokasi')
            ->where('is_primary_row', true)
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(jumlah_peserta) as total_peserta')
            ->groupBy('lokasi')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return [
            'data' => $data,
            'grandTotal' => $grandTotal,
        ];
    }
}
