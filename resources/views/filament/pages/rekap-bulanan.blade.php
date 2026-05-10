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
        .pb-amber { background: #fffbeb; color: #d97706; }
        .dark .pb-indigo { background: #312e81; color: #a5b4fc; }

        /* ── Progress Bar ── */
        .p-progress-track { width: 100px; height: 6px; background: #f3f4f6; border-radius: 999px; overflow: hidden; }
        .dark .p-progress-track { background: #374151; }
        .p-progress-fill { height: 100%; border-radius: 999px; transition: width 1s ease-out; }
    </style>

    <div class="rekap-wrap space-y-8 pb-12" wire:loading.class="opacity-60 transition-opacity">
        
        {{-- Header Section --}}
        <div class="header-card flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex items-center gap-6">
                <div class="stat-icon bg-primary-50 dark:bg-primary-900/20 shadow-sm">
                    <x-heroicon-o-presentation-chart-line class="w-7 h-7 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        Monthly <span class="text-primary-600">Pulse</span>
                    </h1>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">
                        Rekapitulasi performa bulanan Tahun <span class="text-primary-600 font-bold">{{ $selectedYear }}</span>
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

        {{-- Summary Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $stats = [
                    ['l' => 'Total Event', 'v' => $totals['total_event'], 'i' => 'heroicon-o-calendar', 'c' => 'indigo'],
                    ['l' => 'Total Pengunjung', 'v' => $totals['total_peserta'], 'i' => 'heroicon-o-user-group', 'c' => 'emerald'],
                    ['l' => 'Non-Komersial', 'v' => $totals['non_komersial'], 'i' => 'heroicon-o-sparkles', 'c' => 'amber'],
                    ['l' => 'Engagement', 'v' => $totals['pct_non_kom'].'%', 'i' => 'heroicon-o-arrow-trending-up', 'c' => 'primary'],
                ];
            @endphp
            @foreach($stats as $s)
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-5">
                        <div class="stat-icon bg-{{ $s['c'] }}-50 dark:bg-{{ $s['c'] }}-900/20">
                            <x-dynamic-component :component="$s['i']" class="w-6 h-6 text-{{ $s['c'] }}-600 dark:text-{{ $s['c'] }}-400" />
                        </div>
                        <span class="text-[10px] font-black text-{{ $s['c'] }}-600 dark:text-{{ $s['c'] }}-400 uppercase tracking-widest bg-{{ $s['c'] }}-50/50 dark:bg-{{ $s['c'] }}-900/20 px-2 py-1 rounded-md">Real-time</span>
                    </div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $s['l'] }}</div>
                    <div class="text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">
                        {{ is_numeric($s['v']) ? number_format($s['v']) : $s['v'] }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Table Section --}}
        <div class="premium-table-card">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Breakdown Performa Bulanan</h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5 uppercase tracking-wide">Data teragregasi per bulan di tahun {{ $selectedYear }}</p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase">Valid Data Only</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="p-table">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-center">Event</th>
                            <th class="text-center">Pengunjung</th>
                            <th class="text-center">Pelaku Unik</th>
                            <th class="text-right">Rasio Non-Kom</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $index => $row)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center border border-gray-100 dark:border-gray-700">
                                            <span class="text-[10px] font-black text-gray-400">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">{{ $row['bulan'] }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="p-badge pb-indigo">{{ number_format($row['total_event']) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums">{{ number_format($row['total_peserta']) }}</span>
                                        <div class="w-8 h-1 bg-emerald-500/20 rounded-full mt-1.5 overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full" style="width: 60%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm font-bold text-gray-600 dark:text-gray-400 tabular-nums">{{ number_format($row['ekraf_unik']) }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-5">
                                        <div class="p-progress-track hidden sm:block">
                                            <div class="p-progress-fill bg-gradient-to-r from-primary-500 to-primary-600" style="width: {{ $row['pct_non_kom'] }}%"></div>
                                        </div>
                                        <span class="text-sm font-black text-primary-600 dark:text-primary-400 tabular-nums w-12">{{ $row['pct_non_kom'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Total Tahun Ini</td>
                            <td class="text-center">
                                <span class="p-badge pb-indigo" style="font-size: 0.875rem">{{ number_format($totals['total_event']) }}</span>
                            </td>
                            <td class="text-center font-black text-gray-900 dark:text-white tabular-nums text-base">
                                {{ number_format($totals['total_peserta']) }}
                            </td>
                            <td class="text-center font-bold text-gray-600 dark:text-gray-400 tabular-nums">
                                {{ number_format($totals['ekraf_unik'] ?? 0) }}
                            </td>
                            <td class="text-right">
                                <span class="text-base font-black text-primary-600 dark:text-primary-400 tabular-nums">{{ $totals['pct_non_kom'] }}%</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>