<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class TopOrganisasiWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static string $view = 'filament.widgets.top-organisasi';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;




    public function getTopOrganisasiData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $cacheKey = "top_organisasi_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor) {
            $query = Event::query()->valid()
                ->byYearRange($yearStart, $yearEnd)
                ->bySubSektor($subSektor);

            if ($month) {
                $query->whereMonth('tanggal', $month);
            }

            $data = $query->select(DB::raw('UPPER(TRIM(nama_organisasi)) as nama_organisasi'))
                ->whereNotNull('nama_organisasi')
                ->where('nama_organisasi', '!=', '')
                ->selectRaw('COUNT(CASE WHEN is_primary_row = true THEN 1 END) as total_event')
                ->selectRaw('SUM(CASE WHEN is_primary_row = true THEN jumlah_peserta ELSE 0 END) as total_peserta')
                ->selectRaw('COUNT(DISTINCT judul_kegiatan) as jenis_event')
                ->groupBy(DB::raw('UPPER(TRIM(nama_organisasi))'))
                ->orderBy('total_event', 'desc')
                ->limit(10)
                ->get();

            $grandTotal = $data->sum('total_event') ?: 1;

            return [
                'data' => $data,
                'grandTotal' => $grandTotal,
            ];
        });
    }
}
