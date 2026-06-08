<x-filament-panels::page>
    <style>
        /* ── Base ── */
        .rekap-wrap { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        
        /* ── Custom Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        .dark ::-webkit-scrollbar-thumb { background: #374151; }
        
        /* ── Header Card ── */
        .header-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .dark .header-card { background: #111827; border-color: #1f2937; }

        /* ── Filter Selects ── */
        .filter-select {
            display: block;
            width: 100%;
            border-radius: 12px;
            border-color: #e5e7eb;
            background-color: #fff;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            padding: 8px 36px 8px 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .filter-select:focus {
            border-color: #6366f1;
            ring-color: #6366f1;
        }
        .dark .filter-select {
            background-color: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        /* ── Summary Cards ── */
        .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1); }
        .dark .stat-card { background: #111827; border-color: #1f2937; }
        
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }

        /* ── Table Styling ── */
        .premium-table-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .dark .premium-table-card { background: #111827; border-color: #1f2937; }

        .p-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .p-table th {
            padding: 16px 24px;
            background: #f9fafb;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .p-table th { background: #1f2937; color: #9ca3af; border-color: #374151; }
        
        .p-table td {
            padding: 20px 24px;
            font-size: 0.875rem;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }
        .dark .p-table td { color: #d1d5db; border-color: #1f2937; }
        .p-table tr:hover td { background: #f9fafb; }
        .dark .p-table tr:hover td { background: #1f2937/40; }

        /* ── Badges ── */
        .p-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .pb-indigo { background: #eef2ff; color: #4338ca; }
        .pb-emerald { background: #ecfdf5; color: #059669; }
        .pb-rose { background: #fff1f2; color: #e11d48; }
        .pb-amber { background: #fffbeb; color: #d97706; }
        .pb-gray { background: #f3f4f6; color: #374151; }
        .dark .pb-indigo { background: #312e81; color: #a5b4fc; }
        .dark .pb-rose { background: #4c0519; color: #fecdd3; }
        .dark .pb-emerald { background: #064e3b; color: #a7f3d0; }
        .dark .pb-amber { background: #451a03; color: #fde68a; }
        .dark .pb-gray { background: #374151; color: #d1d5db; }

        /* ── Community Cards ── */
        .community-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px 20px;
            transition: all 0.2s;
        }
        .community-card:hover { border-color: #6366f1; background-color: #f9fafb; }
        .dark .community-card { background: #111827; border-color: #1f2937; }
        .dark .community-card:hover { border-color: #6366f1; background-color: #1f2937/40; }

        /* ── Pagination overrides ── */
        .pagination-container {
            padding: 16px 24px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
        }
        .dark .pagination-container {
            background: #111827;
            border-color: #1f2937;
        }
    </style>

    @php
        $data = $this->getRekapData();
        $options = $this->getFilterOptions();
        $events = $this->getEventRecords();
    @endphp

    <div class="rekap-wrap space-y-8 pb-12" wire:loading.class="opacity-60 transition-opacity">
        
        {{-- Header Section --}}
        <div class="header-card flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex items-center gap-6">
                <div class="stat-icon bg-primary-50 dark:bg-primary-900/20 shadow-sm">
                    <x-heroicon-o-book-open class="w-7 h-7 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        Rekap <span class="text-primary-600">Booking & Komunitas</span>
                    </h1>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">
                        Menampilkan seluruh data booking tervalidasi termasuk duplikasi dan event unik Tahun <span class="text-primary-600 font-bold">{{ $selectedYear }}</span>
                    </p>
                </div>
            </div>

            {{-- Year Selector --}}
            <div class="flex items-center gap-4">
                <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Pilih Tahun:</span>
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-inner">
                    @foreach($this->getAvailableYears() as $y)
                        <button 
                            wire:click="$set('selectedYear', '{{ $y }}')"
                            @class([
                                'px-6 py-2 rounded-xl text-sm font-black transition-all duration-300',
                                'bg-white dark:bg-gray-900 text-primary-600 shadow-md ring-1 ring-black/5 dark:ring-white/10 scale-105 z-10' => $selectedYear == $y,
                                'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200' => $selectedYear != $y,
                            ])
                        >
                            {{ $y }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Dynamic Filters Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 bg-white dark:bg-gray-950 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Jenis Kegiatan</label>
                <select wire:model.live="selectedJenisKegiatan" class="filter-select">
                    <option value="">Semua Jenis Kegiatan</option>
                    @foreach($options['jenis_kegiatan'] as $jk)
                        <option value="{{ $jk }}">{{ $jk }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Kategori Ekraf (Sub Sektor)</label>
                <select wire:model.live="selectedEkraf" class="filter-select">
                    <option value="">Semua Kategori Ekraf</option>
                    @foreach($options['ekraf'] as $ek)
                        <option value="{{ $ek }}">{{ $ek }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Bidang Pelaku</label>
                <select wire:model.live="selectedBidang" class="filter-select">
                    <option value="">Semua Bidang</option>
                    @foreach($options['bidang'] as $bd)
                        <option value="{{ $bd }}">{{ $bd }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Tampilkan Tipe Data</label>
                <select wire:model.live="displayMode" class="filter-select">
                    <option value="all">Semua Data (Termasuk Duplikat)</option>
                    <option value="unique">Hanya Data Unik (Lolos Duplikasi)</option>
                </select>
            </div>
        </div>

        {{-- Summary Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stat-card">
                <div class="flex items-center justify-between mb-5">
                    <div class="stat-icon bg-indigo-50 dark:bg-indigo-900/20">
                        <x-heroicon-o-calendar class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest bg-indigo-50/50 dark:bg-indigo-900/20 px-2 py-1 rounded-md">Total Booking</span>
                </div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Seluruh Booking (Termasuk Duplikat)</div>
                <div class="text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">
                    {{ number_format($data['totals']['total_bookings']) }}
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-5">
                    <div class="stat-icon bg-emerald-50 dark:bg-emerald-900/20">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest bg-emerald-50/50 dark:bg-emerald-900/20 px-2 py-1 rounded-md">Event Unik</span>
                </div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Data Unik (Lolos Duplikasi)</div>
                <div class="text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">
                    {{ number_format($data['totals']['unique_events']) }}
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-5">
                    <div class="stat-icon bg-rose-50 dark:bg-rose-900/20">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-rose-600 dark:text-rose-400" />
                    </div>
                    <span class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest bg-rose-50/50 dark:bg-rose-900/20 px-2 py-1 rounded-md">Booking Ganda</span>
                </div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Data Booking Duplikat / Review</div>
                <div class="text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">
                    {{ number_format($data['totals']['duplicate_bookings']) }}
                </div>
            </div>
        </div>

        {{-- Monthly Breakdown Table Section --}}
        <div class="premium-table-card">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Performa Booking Bulanan</h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5 uppercase tracking-wide">Jumlah data booking per bulan tahun {{ $selectedYear }} (Filter Tipe: {{ $displayMode === 'unique' ? 'Hanya Data Unik' : 'Semua Data' }})</p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-100 dark:border-primary-800">
                    <span class="text-[10px] font-black text-primary-600 dark:text-primary-400 uppercase">Statistik Agregasi</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="p-table">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-center">Total Booking</th>
                            <th class="text-center">Event Unik (Lolos)</th>
                            <th class="text-right">Booking Duplikat / Ganda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['monthly'] as $row)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center border border-gray-100 dark:border-gray-700">
                                            <span class="text-[10px] font-black text-gray-400">{{ str_pad($row['index'], 2, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">{{ $row['bulan'] }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="p-badge pb-indigo">{{ number_format($row['total_bookings']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="p-badge pb-emerald">{{ number_format($row['unique_events']) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="p-badge pb-rose font-black tabular-nums">{{ number_format($row['duplicate_bookings']) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Total Sesuai Filter</td>
                            <td class="text-center font-black text-indigo-600 dark:text-indigo-400 text-base">
                                {{ number_format(array_sum(array_column($data['monthly'], 'total_bookings'))) }}
                            </td>
                            <td class="text-center font-black text-emerald-600 dark:text-emerald-400 text-base">
                                {{ number_format(array_sum(array_column($data['monthly'], 'unique_events'))) }}
                            </td>
                            <td class="text-right font-black text-rose-600 dark:text-rose-400 text-base">
                                {{ number_format(array_sum(array_column($data['monthly'], 'duplicate_bookings'))) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Community List Section --}}
        <div class="premium-table-card p-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Nama Komunitas / Pelaku Ekraf</h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5 uppercase tracking-wide">Daftar nama organisasi yang berkegiatan sesuai kategori filter (Tipe: {{ $displayMode === 'unique' ? 'Hanya Data Unik' : 'Semua Data' }})</p>
                </div>
                
                {{-- Search Box --}}
                <div class="w-full md:w-80">
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="communitySearch" 
                            placeholder="Cari komunitas..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:border-primary-500"
                        />
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </div>
                    </div>
                </div>
            </div>

            @if(count($data['communities']) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($data['communities'] as $org)
                        <div class="community-card flex flex-col justify-between">
                            <div class="text-sm font-black text-gray-900 dark:text-white tracking-tight break-words uppercase">
                                {{ $org->org_name }}
                            </div>
                            <div class="flex items-center gap-3 mt-4 text-xs font-bold">
                                <span class="text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded">
                                    {{ $org->total_bookings }} Booking
                                </span>
                                <span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded">
                                    {{ $org->unique_events }} Event Unik
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center text-sm font-medium text-gray-400">
                    Tidak ada komunitas yang ditemukan.
                </div>
            @endif
        </div>

        {{-- Detail Events Table Section --}}
        <div class="premium-table-card">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Detail Seluruh Data Booking</h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5 uppercase tracking-wide">Daftar record event individual hasil filter (Tipe: {{ $displayMode === 'unique' ? 'Hanya Data Unik' : 'Semua Data' }})</p>
                </div>
                
                {{-- Search Box --}}
                <div class="w-full md:w-80">
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="eventSearch" 
                            placeholder="Cari event, organisasi, lokasi..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:border-primary-500"
                        />
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="p-table">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">No</th>
                            <th>Nama Organisasi</th>
                            <th>Judul Kegiatan</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th class="text-center">Lt</th>
                            <th class="text-center">Peserta</th>
                            <th class="text-right">Tipe Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($events) > 0)
                            @foreach($events as $index => $event)
                                <tr>
                                    <td class="text-center text-xs font-bold text-gray-400">
                                        {{ ($events->currentPage() - 1) * $events->perPage() + $index + 1 }}
                                    </td>
                                    <td class="font-black text-gray-900 dark:text-white uppercase tracking-tight max-w-[200px] break-words">
                                        {{ $event->nama_organisasi }}
                                    </td>
                                    <td class="text-sm text-gray-600 dark:text-gray-400 max-w-[250px] break-words">
                                        {{ $event->judul_kegiatan }}
                                    </td>
                                    <td class="text-sm tabular-nums whitespace-nowrap">
                                        {{ $event->tanggal?->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="p-badge pb-indigo">{{ $event->lokasi }}</span>
                                    </td>
                                    <td class="text-center text-sm font-bold">
                                        {{ $event->lantai }}
                                    </td>
                                    <td class="text-center text-sm tabular-nums font-medium">
                                        {{ number_format($event->jumlah_peserta) }}
                                    </td>
                                    <td class="text-right">
                                        @if($event->is_primary_row)
                                            <span class="p-badge pb-emerald">Unik / Primary</span>
                                        @else
                                            <span class="p-badge pb-rose">Duplikat / Ganda</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="py-12 text-center text-sm font-medium text-gray-400">
                                    Tidak ada data booking yang ditemukan.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if($events->hasPages())
                <div class="pagination-container">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
