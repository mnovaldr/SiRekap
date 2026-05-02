<x-filament-panels::page>
<div class="space-y-8 max-w-full mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-2xl">
                <x-heroicon-o-presentation-chart-line class="w-8 h-8 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
                <h2 class="text-2xl font-black dark:text-white tracking-tight">Rekapitulasi Tahunan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Analisis performa event dan pengunjung tahun {{ $selectedYear }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 p-2 rounded-2xl border border-gray-100 dark:border-gray-700">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-2">Tahun Laporan</span>
            <select wire:model.live="selectedYear"
                class="bg-white dark:bg-gray-800 border-none rounded-xl px-4 py-2 text-sm font-bold dark:text-white outline-none focus:ring-2 focus:ring-primary-500 shadow-sm transition-all cursor-pointer">
                @foreach($this->getAvailableYears() as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Summary Stats Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Events --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <span class="text-[9px] font-black px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg uppercase tracking-widest">Tahun {{ $selectedYear }}</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Event</p>
            <div class="flex items-end gap-2 mt-1">
                <h3 class="text-3xl font-black dark:text-white tracking-tighter">{{ number_format($totals['total_event']) }}</h3>
                <span class="text-xs text-gray-400 mb-1">Kegiatan</span>
            </div>
        </div>

        {{-- Card 2: Pengunjung --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <x-heroicon-o-user-group class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <span class="text-[9px] font-black px-2 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg uppercase tracking-widest">Akumulasi</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pengunjung</p>
            <div class="flex items-end gap-2 mt-1">
                <h3 class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tighter">{{ number_format($totals['total_peserta']) }}</h3>
                <span class="text-xs text-gray-400 mb-1">Orang</span>
            </div>
        </div>

        {{-- Card 3: Non-Kom --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <x-heroicon-o-presentation-chart-line class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <span class="text-[9px] font-black px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg uppercase tracking-widest">Kategori</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Non Komersial</p>
            <div class="flex items-end gap-2 mt-1">
                <h3 class="text-3xl font-black text-indigo-600 dark:text-indigo-400 tracking-tighter">{{ number_format($totals['non_komersial']) }}</h3>
                <span class="text-xs text-gray-400 mb-1">Event</span>
            </div>
        </div>

        {{-- Card 4: Rasio --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-rose-50 dark:bg-rose-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                </div>
                <span class="text-[9px] font-black px-2 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-lg uppercase tracking-widest">{{ $totals['pct_non_kom'] }}%</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rasio Non-Kom</p>
            <div class="flex items-center gap-3 mt-1">
                <h3 class="text-3xl font-black text-rose-600 dark:text-rose-400 tracking-tighter">{{ $totals['pct_non_kom'] }}%</h3>
                <div class="flex-1 bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-rose-500 h-full rounded-full transition-all duration-1000" style="width: {{ $totals['pct_non_kom'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Section --}}
    <div class="bg-white dark:bg-gray-900 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Bulan</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Event</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Pengunjung</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Ekraf Unik</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Non-Kom</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Komersialisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($rekap as $row)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-primary-900/5 transition-all group">
                        <td class="px-8 py-4">
                            <span class="text-sm font-black dark:text-gray-200 group-hover:text-primary-600 transition-colors">{{ $row['bulan'] }}</span>
                        </td>
                        <td class="px-8 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 bg-primary-50 dark:bg-primary-900/30 rounded-lg text-xs font-black text-primary-700 dark:text-primary-400 tabular-nums">
                                {{ number_format($row['total_event']) }}
                            </span>
                        </td>
                        <td class="px-8 py-4 text-center">
                            <span class="text-sm font-black dark:text-gray-300 tabular-nums">{{ number_format($row['total_peserta']) }}</span>
                        </td>
                        <td class="px-8 py-4 text-center">
                            <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 tabular-nums">{{ number_format($row['ekraf_unik']) }}</span>
                        </td>
                        <td class="px-8 py-4 text-center">
                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400 tabular-nums">{{ number_format($row['non_komersial']) }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <div class="flex items-center justify-center gap-3">
                                <div class="w-20 bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-primary-500 h-full rounded-full transition-all duration-1000" style="width: {{ $row['pct_non_kom'] }}%"></div>
                                </div>
                                <span class="text-[10px] font-black text-gray-400 tabular-nums w-8">{{ $row['pct_non_kom'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50/50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-800">
                    <tr>
                        <td class="px-8 py-6 text-xs font-black dark:text-white uppercase tracking-widest">Grand Total</td>
                        <td class="px-8 py-6 text-center text-base font-black text-primary-600 tabular-nums">{{ number_format($totals['total_event']) }}</td>
                        <td class="px-8 py-6 text-center text-base font-black text-emerald-600 tabular-nums">{{ number_format($totals['total_peserta']) }}</td>
                        <td class="px-8 py-6 text-center text-xs font-black text-gray-400 italic">Agregat Organisasi</td>
                        <td class="px-8 py-6 text-center text-base font-black text-indigo-600 tabular-nums">{{ number_format($totals['non_komersial']) }}</td>
                        <td class="px-8 py-6">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-[10px] font-black text-gray-400 uppercase">Avg:</span>
                                <span class="px-3 py-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 text-xs font-black text-rose-600">{{ $totals['pct_non_kom'] }}%</span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</x-filament-panels::page>
