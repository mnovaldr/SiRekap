<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;

class TopOrganisasiWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static string $view = 'filament.widgets.top-organisasi';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = true;



    public function getTopOrganisasiData(): array
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

        return [
            'data' => $data,
            'grandTotal' => $grandTotal,
        ];
    }
}
