<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\WithPagination;

class TopOrganisasiWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;
    use WithPagination;

    protected static string $view = 'filament.widgets.top-organisasi';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;

    public string $listMode = 'top10'; // 'top10' or 'all'
    public string $displayType = 'unique'; // 'unique' (Hanya Data Unik) or 'all' (Semua Data)
    public string $search = '';

    public function updatedFilters(): void
    {
        $this->resetPage('listPage');
    }

    public function updatedListMode(): void
    {
        $this->resetPage('listPage');
    }

    public function updatedDisplayType(): void
    {
        $this->resetPage('listPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('listPage');
    }

    public function getTopOrganisasiData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg] = $this->resolveFilters();

        $cacheKey = "top_organisasi_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$bidang}_{$jenisKegiatan}_{$komersial}_{$sdg}_{$this->displayType}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg) {
            $query = Event::query();

            $query = $this->applyEventFilters($query, ['primary']);

            if ($this->displayType === 'unique') {
                $query->valid()->primary();
            }

            $data = $query->select(DB::raw('UPPER(TRIM(nama_organisasi)) as nama_organisasi'))
                ->whereNotNull('nama_organisasi')
                ->where('nama_organisasi', '!=', '')
                ->selectRaw('COUNT(*) as total_event')
                ->selectRaw('COALESCE(SUM(jumlah_peserta), 0) as total_peserta')
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

    public function getDetailedRecords()
    {
        $query = Event::query()->valid();

        $query = $this->applyEventFilters($query, ['primary']);

        if ($this->displayType === 'unique') {
            $query->select(
                DB::raw('UPPER(TRIM(nama_organisasi)) as nama_organisasi'),
                DB::raw('MIN(bidang) as bidang'),
                DB::raw('COUNT(*) as total_event'),
                DB::raw('SUM(jumlah_peserta) as total_peserta'),
                DB::raw('MAX(tanggal) as tanggal_terakhir')
            )
            ->whereNotNull('nama_organisasi')
            ->where('nama_organisasi', '!=', '')
            ->groupBy(DB::raw('UPPER(TRIM(nama_organisasi))'));

            $s = trim($this->search);
            if ($s !== '') {
                $query->having('UPPER(TRIM(nama_organisasi)) ilike ?', ["%{$s}%"]);
            }

            return $query->orderBy('total_event', 'desc')->paginate(10, ['*'], 'listPage');
        } else {
            $s = trim($this->search);
            if ($s !== '') {
                $query->where(function ($q) use ($s) {
                    $q->where('nama_organisasi', 'ilike', "%{$s}%")
                      ->orWhere('judul_kegiatan', 'ilike', "%{$s}%")
                      ->orWhere('lokasi', 'ilike', "%{$s}%");
                });
            }

            return $query->orderBy('tanggal', 'desc')->paginate(10, ['*'], 'listPage');
        }
    }
}
