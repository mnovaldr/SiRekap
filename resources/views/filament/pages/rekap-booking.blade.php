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
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05), 0 1px 2px -1px rgba(0,0,0,0.05);
        }
        .dark .premium-table-card { background: #111827; border-color: #1f2937; }

        /* ── Table Base ── */
        .p-table { width: 100%; border-collapse: collapse; table-layout: auto; }

        .p-table thead tr { border-bottom: 1px solid #e5e7eb; }
        .dark .p-table thead tr { border-color: #374151; }

        .p-table th {
            padding: 12px 16px;
            background: #f9fafb;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            white-space: nowrap;
        }
        .dark .p-table th { background: #1f2937; color: #9ca3af; border-color: #374151; }

        .p-table td {
            padding: 12px 16px;
            font-size: 0.875rem;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .dark .p-table td { color: #d1d5db; border-color: #1f2937; }

        .p-table tbody tr:last-child td { border-bottom: none; }
        .p-table tbody tr:hover td { background: #f9fafb; }
        .dark .p-table tbody tr:hover td { background: rgba(31,41,55,0.5); }

        .p-table tfoot td {
            padding: 12px 16px;
            background: #f9fafb;
            font-size: 0.875rem;
            border-top: 1px solid #e5e7eb;
        }
        .dark .p-table tfoot td { background: #1f2937; border-color: #374151; }

        /* ── Badges ── */
        .p-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.5;
            white-space: nowrap;
        }
        .pb-indigo  { background: #eef2ff; color: #4338ca; }
        .pb-emerald { background: #ecfdf5; color: #059669; }
        .pb-rose    { background: #fff1f2; color: #e11d48; }
        .pb-amber   { background: #fffbeb; color: #d97706; }
        .pb-gray    { background: #f3f4f6; color: #374151; }
        .dark .pb-indigo  { background: #312e81; color: #a5b4fc; }
        .dark .pb-rose    { background: #4c0519; color: #fecdd3; }
        .dark .pb-emerald { background: #064e3b; color: #a7f3d0; }
        .dark .pb-amber   { background: #451a03; color: #fde68a; }
        .dark .pb-gray    { background: #374151; color: #d1d5db; }

        /* ── Search Input ── */
        .search-wrap { position: relative; }
        .search-wrap input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.875rem;
            background: #fff;
            color: #111827;
            transition: border-color 0.2s;
            outline: none;
        }
        .search-wrap input:focus { border-color: #6366f1; }
        .dark .search-wrap input {
            background: #1f2937;
            border-color: #374151;
            color: #f9fafb;
        }
        .dark .search-wrap input:focus { border-color: #6366f1; }
        .search-wrap .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        /* ── Community Cards ── */
        .community-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
            transition: all 0.2s;
        }
        .community-card:hover { border-color: #6366f1; background-color: #f9fafb; }
        .dark .community-card { background: #111827; border-color: #1f2937; }
        .dark .community-card:hover { border-color: #6366f1; background-color: rgba(31,41,55,0.5); }

        /* ── Custom Pagination (rp-*) ── */
        .rp-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 20px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            font-size: 0.8125rem;
        }
        .dark .rp-wrap { background: #111827; border-color: #1f2937; }

        .rp-info { color: #6b7280; white-space: nowrap; }
        .rp-info strong { color: #111827; font-weight: 600; }
        .dark .rp-info { color: #9ca3af; }
        .dark .rp-info strong { color: #f9fafb; }

        /* Per-page selector */
        .rp-perpage { display: flex; align-items: center; gap: 8px; }
        .rp-perpage-label { color: #6b7280; font-weight: 500; white-space: nowrap; font-size: 0.8125rem; }
        .dark .rp-perpage-label { color: #9ca3af; }
        .rp-perpage-select-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .rp-perpage-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: none !important;
            padding: 5px 26px 5px 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #f9fafb;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            outline: none;
            min-width: 65px;
            line-height: 1.4;
            transition: border-color 0.15s, background 0.15s;
        }
        .rp-perpage-select::-ms-expand {
            display: none;
        }
        .rp-perpage-select:hover { background: #f3f4f6; border-color: #9ca3af; }
        .rp-perpage-select:focus { border-color: #6366f1; background: #fff; }
        .dark .rp-perpage-select { background: #1f2937; border-color: #374151; color: #e5e7eb; }
        .dark .rp-perpage-select:hover { background: #374151; }
        .rp-chevron {
            position: absolute;
            right: 9px;
            top: 50%;
            transform: translateY(-50%);
            width: 13px;
            height: 13px;
            color: #6b7280;
            pointer-events: none;
            flex-shrink: 0;
        }
        .dark .rp-chevron { color: #9ca3af; }

        /* Month badge in community cards */
        .month-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 0.7rem;
            font-weight: 600;
            background: #f0f4ff;
            color: #4f46e5;
            white-space: nowrap;
            letter-spacing: 0.01em;
        }
        .dark .month-badge { background: #1e1b4b; color: #a5b4fc; }

        /* Page buttons */
        .rp-pages { display: flex; align-items: center; gap: 4px; }
        .rp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 6px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s;
            line-height: 1;
        }
        .rp-btn:hover:not(:disabled):not(.rp-btn-active) {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
        .rp-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .rp-btn-active {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(99,102,241,0.4);
        }
        .rp-btn-nav { border-color: #e5e7eb; }
        .dark .rp-btn { background: #1f2937; border-color: #374151; color: #d1d5db; }
        .dark .rp-btn:hover:not(:disabled):not(.rp-btn-active) { background: #374151; }
        .dark .rp-btn-active { background: #6366f1; border-color: #6366f1; color: #fff; }
        .rp-nav-icon { width: 14px; height: 14px; }
        .rp-dots { padding: 0 4px; color: #9ca3af; font-size: 0.875rem; user-select: none; }
    </style>

    @php
        $data       = $this->getRekapData();
        $options    = $this->getFilterOptions();
        $events     = $this->getEventRecords();
        $communities = $this->getCommunityRecords();
    @endphp

    <div class="rekap-wrap space-y-6 pb-12" wire:loading.class="opacity-60 transition-opacity">
        
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 bg-white dark:bg-gray-950 p-6 rounded-[20px] border border-gray-200 dark:border-gray-800 shadow-sm">
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
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Performa Booking Bulanan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Data booking per bulan &mdash; Tahun <strong>{{ $selectedYear }}</strong>
                        &bull; <em>Total Booking</em> = semua baris data &bull; <em>Event Unik</em> = 1 kegiatan (multi-ruangan dihitung 1)
                    </p>
                </div>
                <span class="text-[10px] font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider bg-primary-50 dark:bg-primary-900/20 px-3 py-1.5 rounded-lg border border-primary-100 dark:border-primary-800">
                    Statistik Agregasi
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="p-table">
                    <thead>
                        <tr>
                            <th style="width:200px">Bulan</th>
                            <th class="text-center">Total Booking</th>
                            <th class="text-center">Event Unik (Lolos)</th>
                            <th class="text-center">Booking Duplikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['monthly'] as $row)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                            <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ str_pad($row['index'], 2, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $row['bulan'] }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="p-badge pb-indigo">{{ number_format($row['total_bookings']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="p-badge pb-emerald">{{ number_format($row['unique_events']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="p-badge pb-rose">{{ number_format($row['duplicate_bookings']) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-sm font-bold text-gray-900 dark:text-white">Total Sesuai Filter</td>
                            <td class="text-center">
                                <span class="p-badge pb-indigo">{{ number_format(array_sum(array_column($data['monthly'], 'total_bookings'))) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="p-badge pb-emerald">{{ number_format(array_sum(array_column($data['monthly'], 'unique_events'))) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="p-badge pb-rose">{{ number_format(array_sum(array_column($data['monthly'], 'duplicate_bookings'))) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Community List Section --}}
        <div class="premium-table-card">
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Nama Komunitas / Pelaku Ekraf</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Daftar organisasi berkegiatan beserta bulan aktif &mdash; Tahun {{ $selectedYear }}
                    </p>
                </div>
                {{-- Search --}}
                <div class="w-full md:w-72 search-wrap flex-shrink-0">
                    <div class="search-icon"><x-heroicon-o-magnifying-glass class="w-4 h-4" /></div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="communitySearch"
                        placeholder="Cari komunitas..."
                    />
                </div>
            </div>
            {{-- Grid --}}
            <div class="p-6">
                @if($communities->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($communities as $org)
                            @php
                                $bulanArr = $org->bulan_list ? explode(',', $org->bulan_list) : [];
                                // Singkatan bulan untuk tampilan compact
                                $bulanSingkat = [
                                    'Januari'=>'Jan','Februari'=>'Feb','Maret'=>'Mar','April'=>'Apr',
                                    'Mei'=>'Mei','Juni'=>'Jun','Juli'=>'Jul','Agustus'=>'Ags',
                                    'September'=>'Sep','Oktober'=>'Okt','November'=>'Nov','Desember'=>'Des',
                                ];
                            @endphp
                            <div class="community-card flex flex-col">
                                {{-- Nama --}}
                                <div class="text-sm font-semibold text-gray-900 dark:text-white break-words leading-snug">
                                    {{ $org->org_name }}
                                </div>
                                {{-- Stats --}}
                                <div class="flex items-center gap-2 mt-3">
                                    <span class="p-badge pb-indigo">{{ number_format($org->total_bookings) }} Booking</span>
                                    <span class="p-badge pb-emerald">{{ number_format($org->unique_events) }} Unik</span>
                                </div>
                                {{-- Bulan aktif --}}
                                @if(count($bulanArr) > 0)
                                    <div class="flex flex-wrap gap-1 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                        @foreach($bulanArr as $bl)
                                            <span class="month-badge">{{ $bulanSingkat[trim($bl)] ?? trim($bl) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center text-sm text-gray-400">Tidak ada komunitas yang ditemukan.</div>
                @endif
            </div>
            {{-- Pagination komunitas --}}
            {{ $communities->links('vendor.pagination.rekap-pagination') }}
        </div>

        {{-- Detail Events Table Section --}}
        <div class="premium-table-card">
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Detail Seluruh Data Booking</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Daftar record event individual &mdash; Tipe: {{ $displayMode === 'unique' ? 'Data Unik' : 'Semua Data' }}
                    </p>
                </div>
                {{-- Search --}}
                <div class="w-full md:w-80 search-wrap flex-shrink-0">
                    <div class="search-icon"><x-heroicon-o-magnifying-glass class="w-4 h-4" /></div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="eventSearch"
                        placeholder="Cari event, organisasi, lokasi..."
                    />
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="p-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:48px">No</th>
                            <th style="min-width:160px">Nama Organisasi</th>
                            <th style="min-width:200px">Judul Kegiatan</th>
                            <th style="min-width:110px">Tanggal</th>
                            <th style="min-width:100px">Lokasi</th>
                            <th class="text-center" style="width:60px">Lt</th>
                            <th class="text-center" style="min-width:80px">Peserta</th>
                            <th class="text-center" style="min-width:110px">Tipe Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($events) > 0)
                            @foreach($events as $index => $event)
                                <tr>
                                    <td class="text-center">
                                        <span class="text-xs font-semibold text-gray-400 tabular-nums">
                                            {{ ($events->currentPage() - 1) * $events->perPage() + $index + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white break-words">
                                            {{ $event->nama_organisasi }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 break-words">
                                            {{ $event->judul_kegiatan }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm tabular-nums text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                            {{ $event->tanggal?->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="p-badge pb-indigo">{{ $event->lokasi }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            {{ $event->lantai }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-sm tabular-nums text-gray-600 dark:text-gray-400">
                                            {{ number_format($event->jumlah_peserta) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
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
                                <td colspan="8" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-heroicon-o-inbox class="w-10 h-10 text-gray-300 dark:text-gray-600" />
                                        <span class="text-sm text-gray-400">Tidak ada data booking yang ditemukan.</span>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination events --}}
            {{ $events->links('vendor.pagination.rekap-pagination') }}
        </div>

    </div>
</x-filament-panels::page>
