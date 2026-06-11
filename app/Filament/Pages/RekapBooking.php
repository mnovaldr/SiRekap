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
    public string $displayMode = 'all'; // 'all' or 'unique'
    public string $communitySearch = '';
    public string $eventSearch = '';

    // Per-page controls
    public int $eventPagePerPage = 15;
    public int $communityPagePerPage = 12;

    public function mount(): void
    {
        $this->selectedYear = (string) (Event::whereNotNull('tanggal')->max(DB::raw('EXTRACT(YEAR FROM "tanggal")')) ?? date('Y'));
    }

    // ── Reset helpers ──────────────────────────────────────────────────────────

    public function updatedSelectedYear(): void        { $this->resetPage('eventPage'); $this->resetPage('communityPage'); }
    public function updatedSelectedJenisKegiatan(): void { $this->resetPage('eventPage'); $this->resetPage('communityPage'); }
    public function updatedSelectedEkraf(): void       { $this->resetPage('eventPage'); $this->resetPage('communityPage'); }
    public function updatedSelectedBidang(): void      { $this->resetPage('eventPage'); $this->resetPage('communityPage'); }
    public function updatedDisplayMode(): void         { $this->resetPage('eventPage'); $this->resetPage('communityPage'); }
    public function updatedEventSearch(): void         { $this->resetPage('eventPage'); }
    public function updatedCommunitySearch(): void     { $this->resetPage('communityPage'); }
    public function updatedEventPagePerPage(): void    { $this->resetPage('eventPage'); }
    public function updatedCommunityPagePerPage(): void { $this->resetPage('communityPage'); }

    // ── Filter options ─────────────────────────────────────────────────────────

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
            $baseQuery = Event::valid()->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year]);

            return [
                'jenis_kegiatan' => (clone $baseQuery)->whereNotNull('jenis_kegiatan')->where('jenis_kegiatan', '!=', '')->distinct()->orderBy('jenis_kegiatan')->pluck('jenis_kegiatan')->toArray(),
                'ekraf'          => (clone $baseQuery)->whereNotNull('sub_sektor')->where('sub_sektor', '!=', '')->distinct()->orderBy('sub_sektor')->pluck('sub_sektor')->toArray(),
                'bidang'         => (clone $baseQuery)->whereNotNull('bidang')->where('bidang', '!=', '')->distinct()->orderBy('bidang')->pluck('bidang')->toArray(),
            ];
        });
    }

    // ── Main data ──────────────────────────────────────────────────────────────

    public function getRekapData(): array
    {
        $year   = (int) $this->selectedYear;
        $jk     = $this->selectedJenisKegiatan;
        $ekraf  = $this->selectedEkraf;
        $bidang = $this->selectedBidang;

        // Base query — ALL data (no display-mode filter) for the summary cards
        $baseQuery = Event::valid()
            ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year])
            ->when($jk,     fn($q) => $q->where('jenis_kegiatan', $jk))
            ->when($ekraf,  fn($q) => $q->where('sub_sektor', $ekraf))
            ->when($bidang, fn($q) => $q->where('bidang', $bidang));

        // ── Summary card totals (always show all-data counts regardless of displayMode) ──
        $totalsRow = (clone $baseQuery)->selectRaw('
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN "is_primary_row" = true THEN 1 END) as unique_events
        ')->first();

        $totalBookings    = (int) ($totalsRow->total_bookings ?? 0);
        $uniqueEvents     = (int) ($totalsRow->unique_events  ?? 0);
        $duplicateBookings = $totalBookings - $uniqueEvents;

        // ── Filtered query respects displayMode for monthly table & communities ──
        $filteredQuery = (clone $baseQuery)
            ->when($this->displayMode === 'unique', fn($q) => $q->primary());

        // ── Monthly breakdown ──
        // Definisi kolom:
        // total_bookings  = COUNT(*) semua baris yang memenuhi filter (termasuk duplikat multi-ruangan)
        // unique_events   = COUNT(DISTINCT key_short) — 1 kegiatan di banyak ruangan dihitung 1 event unik
        // duplicate_bookings = total_bookings - unique_events (multi-ruangan extra rows)
        $monthsMap = [
            1 => 'Januari',   2 => 'Februari',  3 => 'Maret',     4 => 'April',
            5 => 'Mei',       6 => 'Juni',       7 => 'Juli',      8 => 'Agustus',
            9 => 'September', 10 => 'Oktober',  11 => 'November', 12 => 'Desember',
        ];

        // Gunakan baseQuery (tanpa displayMode filter) untuk monthly — agar total selalu menampilkan semua data,
        // dan unique berbasis nama organisasi unik per bulan
        $stats = (clone $baseQuery)
            ->selectRaw("
                bulan,
                COUNT(*) as total_bookings,
                COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != '' THEN UPPER(TRIM(nama_organisasi)) END) as unique_events
            ")
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $monthlyData = [];
        $sumUniqueEvents = 0;
        foreach ($monthsMap as $num => $name) {
            $data    = $stats->get($name);
            $totalB  = (int) ($data->total_bookings ?? 0);
            $uniqueE = (int) ($data->unique_events  ?? 0);
            $dupB    = $totalB - $uniqueE; // extra baris multi-ruangan

            $monthlyData[] = [
                'index'              => $num,
                'bulan'             => $name,
                'total_bookings'     => $totalB,
                'unique_events'      => $uniqueE,
                'duplicate_bookings' => $dupB,
            ];
            $sumUniqueEvents += $uniqueE;
        }

        $totalBookings = (int) ($totalsRow->total_bookings ?? 0);
        $uniqueEvents = $sumUniqueEvents;
        $duplicateBookings = $totalBookings - $uniqueEvents;

        return [
            'totals' => [
                'total_bookings'     => $totalBookings,
                'unique_events'      => $uniqueEvents,
                'duplicate_bookings' => $duplicateBookings,
            ],
            'monthly' => $monthlyData,
        ];
    }

    // ── Communities (paginated) ────────────────────────────────────────────────

    public function getCommunityRecords()
    {
        $year   = (int) $this->selectedYear;
        $jk     = $this->selectedJenisKegiatan;
        $ekraf  = $this->selectedEkraf;
        $bidang = $this->selectedBidang;
        $search = trim($this->communitySearch);

        // total_bookings  = semua baris (termasuk multi-ruangan)
        // unique_events   = COUNT(DISTINCT key_short) — 1 kegiatan multi-ruangan = 1 event unik
        $query = Event::valid()
            ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year])
            ->when($jk,     fn($q) => $q->where('jenis_kegiatan', $jk))
            ->when($ekraf,  fn($q) => $q->where('sub_sektor', $ekraf))
            ->when($bidang, fn($q) => $q->where('bidang', $bidang))
            ->whereNotNull('nama_organisasi')
            ->where('nama_organisasi', '!=', '')
            ->when($search !== '', fn($q) => $q->where('nama_organisasi', 'ilike', '%' . $search . '%'))
            ->selectRaw("
                UPPER(TRIM(nama_organisasi)) as org_name,
                COUNT(*) as total_bookings,
                COUNT(DISTINCT key_short) as unique_events
            ")
            ->groupBy(DB::raw('UPPER(TRIM(nama_organisasi))'))
            ->orderBy('total_bookings', 'desc');

        $paginator = $query->paginate($this->communityPagePerPage, ['*'], 'communityPage');

        $orgNames = collect($paginator->items())->pluck('org_name')->toArray();

        if (!empty($orgNames)) {
            $monthsData = Event::valid()
                ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year])
                ->when($jk,     fn($q) => $q->where('jenis_kegiatan', $jk))
                ->when($ekraf,  fn($q) => $q->where('sub_sektor', $ekraf))
                ->when($bidang, fn($q) => $q->where('bidang', $bidang))
                ->whereRaw('UPPER(TRIM(nama_organisasi)) IN (' . implode(',', array_fill(0, count($orgNames), '?')) . ')', $orgNames)
                ->selectRaw('UPPER(TRIM(nama_organisasi)) as org_name, bulan')
                ->distinct()
                ->get();

            $monthsMap = [
                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
                'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
                'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
            ];

            $grouped = $monthsData->groupBy('org_name');

            foreach ($paginator->items() as $item) {
                $orgEvents = $grouped->get($item->org_name, collect());
                $sortedMonths = $orgEvents->pluck('bulan')
                    ->filter()
                    ->unique()
                    ->sortBy(fn($m) => $monthsMap[$m] ?? 99)
                    ->implode(',');

                $item->bulan_list = $sortedMonths;
            }
        }

        return $paginator;
    }

    // ── Events (paginated) ────────────────────────────────────────────────────

    public function getEventRecords()
    {
        $year   = (int) $this->selectedYear;
        $jk     = $this->selectedJenisKegiatan;
        $ekraf  = $this->selectedEkraf;
        $bidang = $this->selectedBidang;
        $search = trim($this->eventSearch);

        $query = Event::valid()
            ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [$year])
            ->when($jk,     fn($q) => $q->where('jenis_kegiatan', $jk))
            ->when($ekraf,  fn($q) => $q->where('sub_sektor', $ekraf))
            ->when($bidang, fn($q) => $q->where('bidang', $bidang))
            ->when($this->displayMode === 'unique', fn($q) => $q->primary())
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_organisasi', 'ilike', '%' . $search . '%')
                        ->orWhere('judul_kegiatan',  'ilike', '%' . $search . '%')
                        ->orWhere('lokasi',           'ilike', '%' . $search . '%');
                });
            })
            ->orderBy('tanggal', 'desc');

        return $query->paginate($this->eventPagePerPage, ['*'], 'eventPage');
    }
}
