<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\WithPagination;

class RekapBooking extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.pages.rekap-booking';
    protected static ?string $navigationLabel = 'Rekap Booking';
    protected static ?string $navigationGroup = 'Laporan & Analitik';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Rekap Booking & Komunitas';

    public ?string $selectedYear = null;
    public ?string $selectedJenisKegiatan = null;
    public ?string $selectedEkraf = null;
    public ?string $selectedBidang = null;
    public string $displayMode = 'all'; // 'all' (Semua Data) or 'unique' (Hanya Data Unik)
    public string $communitySearch = '';
    public string $eventSearch = '';

    public function mount(): void
    {
        $this->selectedYear = (string) (Event::whereNotNull('tanggal')->max(DB::raw('EXTRACT(YEAR FROM "tanggal")')) ?? date('Y'));
    }

    public function updatedSelectedYear(): void
    {
        $this->resetPage('eventPage');
    }

    public function updatedSelectedJenisKegiatan(): void
    {
        $this->resetPage('eventPage');
    }

    public function updatedSelectedEkraf(): void
    {
        $this->resetPage('eventPage');
    }

    public function updatedSelectedBidang(): void
    {
        $this->resetPage('eventPage');
    }

    public function updatedDisplayMode(): void
    {
        $this->resetPage('eventPage');
    }

    public function updatedEventSearch(): void
    {
        $this->resetPage('eventPage');
    }

    public function getAvailableYears(): array
    {
        return Event::selectRaw('EXTRACT(YEAR FROM tanggal)::integer as year')
            ->whereNotNull('tanggal')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray() ?: [date('Y') => date('Y')];
    }

    public function getFilterOptions(): array
    {
        $year = (int) $this->selectedYear;

        $cacheKey = "rekap_booking_options_{$year}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($year) {
            $baseQuery = Event::valid()
                ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year]);

            $jenisKegiatans = (clone $baseQuery)
                ->whereNotNull('jenis_kegiatan')
                ->where('jenis_kegiatan', '!=', '')
                ->distinct()
                ->orderBy('jenis_kegiatan')
                ->pluck('jenis_kegiatan')
                ->toArray();

            $ekrafs = (clone $baseQuery)
                ->whereNotNull('sub_sektor')
                ->where('sub_sektor', '!=', '')
                ->distinct()
                ->orderBy('sub_sektor')
                ->pluck('sub_sektor')
                ->toArray();

            $bidangs = (clone $baseQuery)
                ->whereNotNull('bidang')
                ->where('bidang', '!=', '')
                ->distinct()
                ->orderBy('bidang')
                ->pluck('bidang')
                ->toArray();

            return [
                'jenis_kegiatan' => $jenisKegiatans,
                'ekraf' => $ekrafs,
                'bidang' => $bidangs,
            ];
        });
    }

    public function getRekapData(): array
    {
        $year = (int) $this->selectedYear;
        $jk = $this->selectedJenisKegiatan;
        $ekraf = $this->selectedEkraf;
        $bidang = $this->selectedBidang;

        $query = Event::valid()
            ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year])
            ->when($jk, fn($q) => $q->where('jenis_kegiatan', $jk))
            ->when($ekraf, fn($q) => $q->where('sub_sektor', $ekraf))
            ->when($bidang, fn($q) => $q->where('bidang', $bidang));

        // 1. Fetch Totals (Selalu ambil total booking & unique event untuk perbandingan di card)
        $totalsRow = (clone $query)->selectRaw('
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN "is_primary_row" = true THEN 1 END) as unique_events
        ')->first();

        $totalBookings = $totalsRow->total_bookings ?? 0;
        $uniqueEvents = $totalsRow->unique_events ?? 0;
        $duplicateBookings = $totalBookings - $uniqueEvents;

        // Base query yang sudah difilter displayMode untuk monthly & community
        $filteredQuery = (clone $query)
            ->when($this->displayMode === 'unique', fn($q) => $q->primary());

        // 2. Fetch Monthly Breakdown
        $monthsMap = [
            1 => 'Januari',   2 => 'Februari',  3 => 'Maret',     4 => 'April',
            5 => 'Mei',       6 => 'Juni',       7 => 'Juli',      8 => 'Agustus',
            9 => 'September', 10 => 'Oktober',   11 => 'November', 12 => 'Desember',
        ];

        $stats = (clone $filteredQuery)
            ->selectRaw("
                bulan,
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN \"is_primary_row\" = true THEN 1 END) as unique_events
            ")
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $monthlyData = [];
        foreach ($monthsMap as $num => $name) {
            $data = $stats->get($name);
            $totalB = $data->total_bookings ?? 0;
            $uniqueE = $data->unique_events ?? 0;
            $dupB = $totalB - $uniqueE;

            $monthlyData[] = [
                'index' => $num,
                'bulan' => $name,
                'total_bookings' => $totalB,
                'unique_events' => $uniqueE,
                'duplicate_bookings' => $dupB,
            ];
        }

        // 3. Fetch Communities (filtered by search and selected categories)
        $search = trim($this->communitySearch);

        $communitiesQuery = (clone $filteredQuery)
            ->whereNotNull('nama_organisasi')
            ->where('nama_organisasi', '!=', '')
            ->when($search !== '', fn($q) => $q->where('nama_organisasi', 'ilike', '%' . $search . '%'))
            ->selectRaw('
                UPPER(TRIM(nama_organisasi)) as org_name,
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN is_primary_row = true THEN 1 END) as unique_events
            ')
            ->groupBy(DB::raw('UPPER(TRIM(nama_organisasi))'))
            ->orderBy('total_bookings', 'desc')
            ->limit(24);

        $communities = $communitiesQuery->get();

        return [
            'totals' => [
                'total_bookings' => $totalBookings,
                'unique_events' => $uniqueEvents,
                'duplicate_bookings' => $duplicateBookings,
            ],
            'monthly' => $monthlyData,
            'communities' => $communities,
        ];
    }

    public function getEventRecords()
    {
        $year = (int) $this->selectedYear;
        $jk = $this->selectedJenisKegiatan;
        $ekraf = $this->selectedEkraf;
        $bidang = $this->selectedBidang;
        $search = trim($this->eventSearch);

        $query = Event::valid()
            ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year])
            ->when($jk, fn($q) => $q->where('jenis_kegiatan', $jk))
            ->when($ekraf, fn($q) => $q->where('sub_sektor', $ekraf))
            ->when($bidang, fn($q) => $q->where('bidang', $bidang))
            ->when($this->displayMode === 'unique', fn($q) => $q->primary())
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_organisasi', 'ilike', '%' . $search . '%')
                       ->orWhere('judul_kegiatan', 'ilike', '%' . $search . '%')
                       ->orWhere('lokasi', 'ilike', '%' . $search . '%');
                });
            })
            ->orderBy('tanggal', 'desc');

        return $query->paginate(15, ['*'], 'eventPage');
    }
}
