<x-filament-widgets::widget>
    <x-filament::section class="rounded-[2rem] shadow-sm border-gray-100 dark:border-gray-800">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <x-heroicon-o-map-pin class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                </div>
                <span class="text-lg font-black tracking-tight">Top 10 Lokasi Paling Aktif</span>
            </div>
        </x-slot>

        <x-slot name="description">
            @php
                $year = $this->filters['year'] ?? null;
                $month = $this->filters['month'] ?? null;
                $period = ($month ? \App\Models\Event::getBulanIndonesia($month) : '') . ($year ? ' ' . $year : '');
                $period = trim($period) ?: 'Semua Periode';
            @endphp
            <span class="text-xs text-gray-500">Data penggunaan ruangan: {{ $period }}</span>
        </x-slot>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Rank</th>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Lokasi</th>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Event</th>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Peserta</th>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Utilitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @php
                        $res = $this->getTopLokasiData();
                        $items = $res['data'];
                        $grandTotal = $res['grandTotal'];
                    @endphp
                    @forelse($items as $index => $item)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-primary-900/5 transition-all group">
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-black text-xs
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : '' }}
                                {{ $index === 1 ? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : '' }}
                                {{ $index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400' : '' }}
                                {{ $index > 2 ? 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400' : '' }}">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm font-black dark:text-gray-200 group-hover:text-primary-600 transition-colors">{{ $item['lokasi'] }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 bg-primary-50 dark:bg-primary-900/30 rounded-lg text-xs font-black text-primary-700 dark:text-primary-400 tabular-nums">
                                {{ number_format($item['total']) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-sm font-black dark:text-gray-300 tabular-nums">{{ number_format($item['total_peserta']) }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-primary-500 h-full rounded-full transition-all duration-1000" 
                                         style="width: {{ ($item['total'] / $grandTotal) * 100 }}%"></div>
                                </div>
                                <span class="text-[10px] font-black text-gray-400 w-8 text-right tabular-nums">
                                    {{ round(($item['total'] / $grandTotal) * 100, 1) }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm italic">Tidak ada data ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
