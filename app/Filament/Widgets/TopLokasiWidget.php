<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\Cache;


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
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode] = $this->resolveFilters();

        $cacheKey = "top_lokasi_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$bidang}_{$jenisKegiatan}_{$komersial}_{$sdg}_{$displayMode}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode) {
            $query = Event::query()->valid();


            $query = $this->applyEventFilters($query);

            $data = $query->select('lokasi')
                ->selectRaw('count(*) as total')
                ->selectRaw('sum(jumlah_peserta) as total_peserta')
                ->groupBy('lokasi')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();


            $grandTotal = $data->sum('total') ?: 1;

            return [
                'data' => $data,
                'grandTotal' => $grandTotal,
            ];
        });
    }
}
