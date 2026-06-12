<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\WithPagination;

class TopLokasiWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;
    use WithPagination;

    protected static string $view = 'filament.widgets.top-lokasi';
    protected static ?int $sort = 11;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;

    public string $listMode = 'top10'; // 'top10' or 'all'
    public string $search = '';

    public function updatedFilters(): void
    {
        $this->resetPage('listPage');
    }

    public function updatedListMode(): void
    {
        $this->resetPage('listPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('listPage');
    }

    public function getTopLokasiData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg] = $this->resolveFilters();

        $cacheKey = "top_lokasi_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$bidang}_{$jenisKegiatan}_{$komersial}_{$sdg}_unique";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg) {
            $query = Event::query();

            $query = $this->applyEventFilters($query, ['primary']);

            $query->valid()->primary();

            $data = $query->select(DB::raw('UPPER(TRIM(lokasi)) as lokasi'))
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->selectRaw('count(*) as total')
                ->selectRaw('COALESCE(sum(jumlah_peserta), 0) as total_peserta')
                ->groupBy(DB::raw('UPPER(TRIM(lokasi))'))
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

    public function getDetailedRecords()
    {
        $query = Event::query()->valid();

        $query = $this->applyEventFilters($query, ['primary']);

        $query->primary()
        ->select(
            DB::raw('UPPER(TRIM(lokasi)) as lokasi'),
            DB::raw('MIN(lantai) as lantai'),
            DB::raw('COUNT(*) as total_event'),
            DB::raw('SUM(jumlah_peserta) as total_peserta'),
            DB::raw('MAX(tanggal) as tanggal_terakhir')
        )
        ->whereNotNull('lokasi')
        ->where('lokasi', '!=', '')
        ->groupBy(DB::raw('UPPER(TRIM(lokasi))'));

        $s = trim($this->search);
        if ($s !== '') {
            $query->where('lokasi', 'ilike', "%{$s}%");
        }

        return $query->orderBy('total_event', 'desc')->paginate(10, ['*'], 'listPage');
    }
}
