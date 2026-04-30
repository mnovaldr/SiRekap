<x-filament-panels::page>
<div class="space-y-10 max-w-[95rem] mx-auto">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="space-y-1">
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white uppercase tracking-tighter">Export Engine</h2>
            <p class="text-gray-500 dark:text-gray-400 font-medium">Ekstraksi data terstruktur untuk laporan dan integrasi sistem.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-blue-50 dark:bg-blue-900/20 px-6 py-3 rounded-2xl border border-blue-100 dark:border-blue-900/30">
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 italic">Target Data: {{ number_format($totalRows) }} Baris</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        {{-- Configuration Panel --}}
        <div class="lg:col-span-4 space-y-8">
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] p-10 shadow-sm border border-gray-100 dark:border-gray-800">
                <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-8 italic">Data Pipeline Configuration</h3>
                
                <div class="space-y-8">
                    {{-- Year Picker --}}
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 block">Tahun Anggaran</label>
                        <select wire:model.live="selectedYear" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-blue-500 transition-all dark:text-white py-4 px-6">
                            @foreach($this->getAvailableYears() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Month Picker --}}
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 block">Periode Bulan</label>
                        <select wire:model.live="selectedMonth" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-blue-500 transition-all dark:text-white py-4 px-6">
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Format Picker --}}
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 block">Format Output</label>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($formats as $format)
                                <label class="relative flex items-center p-6 rounded-3xl border-2 cursor-pointer transition-all duration-300
                                    {{ $selectedFormat === $format['id'] ? 'border-blue-600 bg-blue-50/30 dark:bg-blue-900/10' : 'border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 hover:border-gray-200 dark:hover:border-gray-700' }}">
                                    <input type="radio" wire:model.live="selectedFormat" value="{{ $format['id'] }}" class="hidden">
                                    <div class="text-3xl mr-5">{{ $format['icon'] }}</div>
                                    <div class="flex-1">
                                        <p class="text-xs font-black dark:text-white uppercase tracking-tight">{{ $format['name'] }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 mt-0.5 leading-tight italic">{{ $format['desc'] }}</p>
                                    </div>
                                    @if($selectedFormat === $format['id'])
                                        <div class="w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center animate-in zoom-in">
                                            <x-heroicon-o-check class="w-3 h-3 text-white stroke-[4]" />
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button wire:click="export" wire:loading.attr="disabled"
                        class="w-full py-6 bg-gray-900 dark:bg-blue-600 text-white rounded-[2rem] text-xs font-black uppercase tracking-[0.3em] shadow-2xl hover:shadow-blue-500/20 hover:-translate-y-1 transition-all duration-300 mt-6 disabled:opacity-50">
                        <span wire:loading.remove>Generate Download ↓</span>
                        <span wire:loading>Processing Data...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Preview Panel --}}
        <div class="lg:col-span-8 space-y-8">
            <div class="bg-white dark:bg-gray-900 rounded-[3rem] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col min-h-[700px]">
                <div class="px-10 py-8 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex justify-between items-center">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 italic">Live Pipeline Preview</h3>
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Realtime Sync</span>
                    </div>
                </div>

                <div class="flex-1 overflow-x-auto">
                    @php $data = $this->getPreviewData(); @endphp
                    @if($data->count() > 0)
                        <table class="w-full text-left">
                            <thead class="bg-white dark:bg-gray-900">
                                <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-50 dark:border-gray-800">
                                    <th class="px-10 py-6">Event Identity</th>
                                    <th class="px-10 py-6">Spatial Data</th>
                                    <th class="px-10 py-6">Temporal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @foreach($data as $row)
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors group">
                                        <td class="px-10 py-6">
                                            <p class="text-xs font-black text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors truncate max-w-[300px]">{{ $row->judul_kegiatan }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-tight">{{ $row->nama_organisasi }}</p>
                                        </td>
                                        <td class="px-10 py-6">
                                            <span class="text-[10px] font-black text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                                                {{ $row->lokasi }}
                                            </span>
                                        </td>
                                        <td class="px-10 py-6">
                                            <p class="text-[11px] font-black text-gray-900 dark:text-white uppercase tracking-tighter">{{ date('d M Y', strtotime($row->tanggal)) }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="flex flex-col items-center justify-center h-full py-40 text-center px-10">
                            <div class="w-24 h-24 bg-gray-50 dark:bg-gray-800 rounded-[2rem] flex items-center justify-center mb-8 rotate-12 group-hover:rotate-0 transition-transform duration-500">
                                <x-heroicon-o-magnifying-glass-circle class="w-12 h-12 text-gray-200 dark:text-gray-700" />
                            </div>
                            <h4 class="text-xl font-black text-gray-900 dark:text-white mb-3 italic">Data Stream Offline</h4>
                            <p class="text-xs text-gray-400 max-w-xs leading-relaxed font-medium italic">Tidak ada rekaman event ditemukan untuk kriteria filter yang Anda pilih. Silakan sesuaikan parameter tahun atau bulan.</p>
                        </div>
                    @endif
                </div>

                {{-- Pagination Footer --}}
                @if($data->count() > 0)
                    <div class="px-10 py-8 border-t border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                        {{ $data->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-filament-panels::page>
