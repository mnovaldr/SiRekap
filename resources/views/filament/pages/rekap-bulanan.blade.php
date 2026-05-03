<x-filament-panels::page>
    <div class="space-y-10 animate-in fade-in duration-1000">
        
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                    Monthly <span class="text-primary-600">Pulse</span>
                </h2>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Navigasi performa ekosistem event periode tahun {{ $selectedYear }}
                </p>
            </div>
            
            {{-- Year Selector --}}
            <div class="flex items-center p-1.5 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                @foreach($this->getAvailableYears() as $y)
                    <button 
                        wire:click="$set('selectedYear', '{{ $y }}')"
                        @class([
                            'px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300',
                            'bg-primary-600 text-white shadow-md shadow-primary-500/25' => $selectedYear == $y,
                            'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' => $selectedYear != $y,
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
                    ['title' => 'Total Events', 'val' => $totals['total_event'], 'icon' => 'heroicon-o-calendar-days', 'color' => 'primary', 'bg' => 'bg-primary-50 dark:bg-primary-500/10'],
                    ['title' => 'Total Visitors', 'val' => $totals['total_peserta'], 'icon' => 'heroicon-o-users', 'color' => 'emerald', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10'],
                    ['title' => 'Non-Commercial', 'val' => $totals['non_komersial'], 'icon' => 'heroicon-o-heart', 'color' => 'amber', 'bg' => 'bg-amber-50 dark:bg-amber-500/10'],
                    ['title' => 'Ratio', 'val' => $totals['pct_non_kom'].'%', 'icon' => 'heroicon-o-chart-pie', 'color' => 'rose', 'bg' => 'bg-rose-50 dark:bg-rose-500/10'],
                ];
            @endphp
            
            @foreach($cards as $card)
                <div class="relative overflow-hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="space-y-3">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl {{ $card['bg'] }}">
                                    <x-dynamic-component 
                                        :component="$card['icon']" 
                                        class="w-5 h-5 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400" 
                                    />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['title'] }}</p>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1 tabular-nums">
                                        {{ is_numeric($card['val']) ? number_format($card['val']) : $card['val'] }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -right-3 -bottom-3 opacity-[0.03]">
                        <x-dynamic-component :component="$card['icon']" class="w-24 h-24" />
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Monthly Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                            <th class="px-6 py-4 text-left">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Month</span>
                            </th>
                            <th class="px-6 py-4 text-center">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activities</span>
                            </th>
                            <th class="px-6 py-4 text-center">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visitors</span>
                            </th>
                            <th class="px-6 py-4 text-center">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unique Org</span>
                            </th>
                            <th class="px-6 py-4 text-right">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ratio</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($rekap as $index => $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-200">
                                {{-- Month --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                                            <span class="text-xs font-bold text-primary-600 dark:text-primary-400">
                                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $row['bulan'] }}
                                        </span>
                                    </div>
                                </td>
                                
                                {{-- Activities --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-bold text-gray-700 dark:text-gray-300">
                                        {{ number_format($row['total_event']) }}
                                    </span>
                                </td>
                                
                                {{-- Visitors --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="space-y-0.5">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white tabular-nums">
                                            {{ number_format($row['total_peserta']) }}
                                        </span>
                                        <span class="block text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                            Verified
                                        </span>
                                    </div>
                                </td>
                                
                                {{-- Unique Org --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                        {{ $row['ekraf_unik'] }}
                                    </span>
                                </td>
                                
                                {{-- Ratio --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="flex-1 max-w-[80px]">
                                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                                                <div 
                                                    @class([
                                                        'h-full rounded-full transition-all duration-700',
                                                        'bg-rose-500' => $row['pct_non_kom'] > 50,
                                                        'bg-primary-500' => $row['pct_non_kom'] <= 50,
                                                    ])
                                                    style="width: {{ $row['pct_non_kom'] }}%"
                                                ></div>
                                            </div>
                                        </div>
                                        <span @class([
                                            'text-sm font-bold tabular-nums min-w-[3rem]',
                                            'text-rose-600 dark:text-rose-400' => $row['pct_non_kom'] > 50,
                                            'text-primary-600 dark:text-primary-400' => $row['pct_non_kom'] <= 50,
                                        ])>
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

        {{-- Annual Summary Footer --}}
        <div class="relative overflow-hidden bg-gray-900 dark:bg-gray-800 rounded-2xl shadow-lg">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-500/10 to-transparent"></div>
            <div class="relative p-8 md:p-10">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-sm flex items-center justify-center">
                            <x-heroicon-o-chart-bar class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white">Annual Summary {{ $selectedYear }}</h4>
                            <p class="text-sm text-gray-400 mt-1">Consolidated performance data</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-8 md:gap-12">
                        <div class="text-center">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Events</p>
                            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($totals['total_event']) }}</p>
                        </div>
                        
                        <div class="hidden md:block w-px bg-white/10 self-stretch"></div>
                        
                        <div class="text-center">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Visitors</p>
                            <p class="text-2xl font-bold text-emerald-400 tabular-nums">{{ number_format($totals['total_peserta']) }}</p>
                        </div>
                        
                        <div class="hidden md:block w-px bg-white/10 self-stretch"></div>
                        
                        <div class="text-center">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Ratio</p>
                            <p class="text-2xl font-bold text-rose-400 tabular-nums">{{ $totals['pct_non_kom'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>