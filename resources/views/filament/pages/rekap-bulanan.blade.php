<x-filament-panels::page>
<div class="space-y-10 max-w-7xl mx-auto animate-in fade-in duration-700">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800">
        <div>
            <h2 class="text-3xl font-black dark:text-white tracking-tight">Rekapitulasi Tahunan</h2>
            <p class="text-sm text-gray-400 mt-1">Laporan komprehensif performa event per bulan.</p>
        </div>
        <div class="flex items-center gap-4">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pilih Tahun</label>
            <select wire:model.live="selectedYear"
                class="bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-6 py-3 text-sm font-bold dark:text-white outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                @foreach($this->getAvailableYears() as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-[3rem] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-600 text-white">
                    <tr class="text-[11px] font-black uppercase tracking-[0.2em]">
                        <th class="px-8 py-6">Periode Bulan</th>
                        <th class="px-8 py-6 text-center">Total Event</th>
                        <th class="px-8 py-6 text-center">Total Peserta</th>
                        <th class="px-8 py-6 text-center">Ekraf Unik</th>
                        <th class="px-8 py-6 text-center">Non Komersial</th>
                        <th class="px-8 py-6 text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($rekap as $row)
                    <tr class="hover:bg-primary-50/30 dark:hover:bg-primary-900/10 transition-all group">
                        <td class="px-8 py-5 text-sm font-black dark:text-gray-200 group-hover:text-primary-600 transition-colors">{{ $row['bulan'] }}</td>
                        <td class="px-8 py-5 text-center">
                            <span class="px-4 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-xl text-xs font-bold dark:text-white tabular-nums">{{ number_format($row['total_event']) }}</span>
                        </td>
                        <td class="px-8 py-5 text-center text-sm font-black dark:text-gray-300 tabular-nums">{{ number_format($row['total_peserta']) }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-primary-600 tabular-nums">{{ number_format($row['ekraf_unik']) }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-rose-600 tabular-nums">{{ number_format($row['non_komersial']) }}</td>
                        <td class="px-8 py-5">
                            <div class="flex items-center justify-center gap-3">
                                <div class="w-20 bg-gray-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                                    <div class="bg-primary-500 h-full rounded-full transition-all duration-1000" style="width: {{ $row['pct_non_kom'] }}%"></div>
                                </div>
                                <span class="text-[10px] font-black text-gray-500 tabular-nums">{{ $row['pct_non_kom'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-primary-700 text-white font-black text-xs uppercase tracking-widest">
                    <tr class="border-t-2 border-white/10">
                        <td class="px-8 py-8">Agregat Tahunan</td>
                        <td class="px-8 py-8 text-center">{{ number_format($totals['total_event']) }}</td>
                        <td class="px-8 py-8 text-center">{{ number_format($totals['total_peserta']) }}</td>
                        <td class="px-8 py-8 text-center text-primary-300">-</td>
                        <td class="px-8 py-8 text-center text-rose-300">{{ number_format($totals['non_komersial']) }}</td>
                        <td class="px-8 py-8 text-center">
                            <div class="inline-flex items-center px-4 py-2 bg-white/10 rounded-2xl border border-white/20 backdrop-blur-md">
                                AVG: {{ $totals['pct_non_kom'] }}%
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</x-filament-panels::page>
