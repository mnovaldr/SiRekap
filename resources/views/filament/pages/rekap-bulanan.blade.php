<x-filament-panels::page>
    <div class="space-y-8" wire:loading.class="opacity-50 pointer-events-none transition-opacity duration-300">
        
        {{-- Progress Bar for Loading --}}
        <div wire:loading.block class="fixed top-0 left-0 w-full h-1 z-[9999]">
            <div class="h-full bg-primary-600 animate-pulse w-full"></div>
        </div>

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                    <x-heroicon-o-presentation-chart-line class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        Monthly <span class="text-primary-600">Pulse</span>
                    </h2>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Rekapitulasi performa bulanan tahun {{ $selectedYear }}
                    </p>
                </div>
            </div>
            
            {{-- Year Selector --}}
            <div class="flex items-center p-1 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                @foreach($this->getAvailableYears() as $y)
                    <button 
                        wire:click="$set('selectedYear', '{{ $y }}')"
                        @class([
                            'px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300',
                            'bg-white dark:bg-gray-900 text-primary-600 shadow-sm border border-gray-200 dark:border-gray-700' => $selectedYear == $y,
                            'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-white/50' => $selectedYear != $y,
                        ])
                    >
                        {{ $y }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $cards = [
                    ['title' => 'Total Events', 'val' => $totals['total_event'], 'icon' => 'heroicon-o-calendar', 'color' => 'indigo', 'bg' => 'bg-indigo-50 dark:bg-indigo-500/10'],
                    ['title' => 'Total Visitors', 'val' => $totals['total_peserta'], 'icon' => 'heroicon-o-user-group', 'color' => 'emerald', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10'],
                    ['title' => 'Non-Commercial', 'val' => $totals['non_komersial'], 'icon' => 'heroicon-o-heart', 'color' => 'rose', 'bg' => 'bg-rose-50 dark:bg-rose-500/10'],
                    ['title' => 'Engagement Ratio', 'val' => $totals['pct_non_kom'].'%', 'icon' => 'heroicon-o-sparkles', 'color' => 'amber', 'bg' => 'bg-amber-50 dark:bg-amber-500/10'],
                ];
            @endphp
            
            @foreach($cards as $card)
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 hover:border-primary-500/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl {{ $card['bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <x-dynamic-component 
                                :component="$card['icon']" 
                                class="w-6 h-6 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400" 
                            />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $card['title'] }}</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white tabular-nums">
                                {{ is_numeric($card['val']) ? number_format($card['val']) : $card['val'] }}
                            </h3>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Monthly Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Breakdown Bulanan</h3>
                <span class="text-xs font-medium text-gray-500 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full">Tahun {{ $selectedYear }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                            <th class="px-6 py-4">Bulan</th>
                            <th class="px-6 py-4 text-center">Event</th>
                            <th class="px-6 py-4 text-center">Pengunjung</th>
                            <th class="px-6 py-4 text-center">Pelaku Ekraf</th>
                            <th class="px-6 py-4 text-right">Rasio Non-Kom</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($rekap as $index => $row)
                            <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition-all duration-200">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:bg-primary-600 transition-colors duration-300">
                                            <span class="text-xs font-black text-gray-500 group-hover:text-white">
                                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </div>
                                        <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">
                                            {{ $row['bulan'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-black bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                                        {{ number_format($row['total_event']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums">
                                            {{ number_format($row['total_peserta']) }}
                                        </span>
                                        <div class="w-8 h-0.5 bg-emerald-500 rounded-full mt-1"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-bold text-gray-600 dark:text-gray-400 tabular-nums">
                                        {{ $row['ekraf_unik'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-4">
                                        <div class="hidden sm:block flex-1 max-w-[100px]">
                                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                                <div 
                                                    class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-1000"
                                                    style="width: {{ $row['pct_non_kom'] }}%"
                                                ></div>
                                            </div>
                                        </div>
                                        <span class="text-sm font-black text-primary-600 dark:text-primary-400 tabular-nums min-w-[3rem]">
                                            {{ $row['pct_non_kom'] }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Annual Highlights Footer (Simplified for Readability) --}}
        <div class="bg-gray-900 dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden border-t-4 border-primary-500">
            <div class="p-8 md:p-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    {{-- Title Section --}}
                    <div class="lg:col-span-4 space-y-2 text-center lg:text-left">
                        <h4 class="text-3xl font-black text-white tracking-tight uppercase">
                            Summary <span class="text-primary-400">{{ $selectedYear }}</span>
                        </h4>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-widest">
                            Laporan Performa Tahunan
                        </p>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-0">
                        
                        {{-- Stat 1 --}}
                        <div class="flex flex-col items-center justify-center p-4 md:border-r md:border-gray-700/50">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-2">Total Events</span>
                            <span class="text-4xl font-black text-white tabular-nums">
                                {{ number_format($totals['total_event']) }}
                            </span>
                        </div>

                        {{-- Stat 2 --}}
                        <div class="flex flex-col items-center justify-center p-4 md:border-r md:border-gray-700/50">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-2">Total Visitors</span>
                            <span class="text-4xl font-black text-emerald-400 tabular-nums">
                                {{ number_format($totals['total_peserta']) }}
                            </span>
                        </div>

                        {{-- Stat 3 --}}
                        <div class="flex flex-col items-center justify-center p-4">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-2">Engagement Ratio</span>
                            <span class="text-4xl font-black text-rose-500 tabular-nums">
                                {{ $totals['pct_non_kom'] }}%
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>