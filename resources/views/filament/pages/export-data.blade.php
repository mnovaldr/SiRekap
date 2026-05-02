<x-filament-panels::page>
<div class="space-y-10 max-w-full mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-5">
            <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-[1.5rem]">
                <x-heroicon-o-arrow-down-tray class="w-10 h-10 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white uppercase">Export Data Engine</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1">Ekstraksi data terstruktur untuk laporan dan integrasi sistem.</p>
            </div>
        </div>
        <div class="shrink-0">
            <div class="bg-indigo-50 dark:bg-indigo-900/30 px-6 py-4 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                <span class="text-xs font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 tabular-nums">{{ number_format($totalRows) }} Data Terdeteksi</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        {{-- Configuration Panel --}}
        <div class="lg:col-span-4 space-y-8">
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] p-10 shadow-sm border border-gray-100 dark:border-gray-800">
                <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-8 italic">Export Configuration</h3>
                
                <div class="space-y-8">
                    {{-- Year Picker --}}
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 block">Tahun Anggaran</label>
                        <select wire:model.live="selectedYear" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white py-4 px-6 shadow-sm">
                            @foreach($this->getAvailableYears() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Month Picker --}}
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 block">Periode Bulan</label>
                        <select wire:model.live="selectedMonth" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white py-4 px-6 shadow-sm">
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Format Picker --}}
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 block">Format Output</label>
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($formats as $format)
                                <label class="relative flex items-center p-5 rounded-[1.5rem] border-2 cursor-pointer transition-all duration-300
                                    {{ $selectedFormat === $format['id'] ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 hover:border-gray-200 dark:hover:border-gray-700' }}">
                                    <input type="radio" wire:model.live="selectedFormat" value="{{ $format['id'] }}" class="hidden">
                                    <div class="text-2xl mr-4">{{ $format['icon'] }}</div>
                                    <div class="flex-1">
                                        <p class="text-xs font-black dark:text-white uppercase tracking-tight">{{ $format['name'] }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 mt-0.5 leading-tight italic">{{ $format['desc'] }}</p>
                                    </div>
                                    @if($selectedFormat === $format['id'])
                                        <div class="w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center animate-in zoom-in">
                                            <x-heroicon-o-check class="w-3 h-3 text-white stroke-[4]" />
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button wire:click="export" wire:loading.attr="disabled"
                        class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[2rem] text-xs font-black uppercase tracking-[0.3em] shadow-xl hover:shadow-indigo-500/20 hover:-translate-y-1 transition-all duration-300 mt-6 disabled:opacity-50">
                        <span wire:loading.remove>Generate Download ↓</span>
                        <span wire:loading class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Preview Panel --}}
        <div class="lg:col-span-8 space-y-8">
            <div class="bg-white dark:bg-gray-900 rounded-[3rem] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col min-h-[600px]">
                <div class="px-10 py-8 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex justify-between items-center">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 italic">Data Pipeline Preview</h3>
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Realtime Sync</span>
                    </div>
                </div>

                <div class="flex-1 overflow-x-auto">
                    @php $data = $this->getPreviewData(); @endphp
                    @if($data->count() > 0)
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-50 dark:border-gray-800">
                                    <th class="px-10 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Event Identity</th>
                                    <th class="px-10 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Spatial Data</th>
                                    <th class="px-10 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Temporal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @foreach($data as $row)
                                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/5 transition-colors group">
                                        <td class="px-10 py-6">
                                            <p class="text-xs font-black text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors truncate max-w-[250px]">{{ $row->judul_kegiatan }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-tight">{{ $row->nama_organisasi }}</p>
                                        </td>
                                        <td class="px-10 py-6">
                                            <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-xl border border-indigo-100 dark:border-indigo-800">
                                                {{ $row->lokasi }}
                                            </span>
                                        </td>
                                        <td class="px-10 py-6">
                                            <p class="text-[11px] font-black text-gray-900 dark:text-white uppercase tracking-tighter tabular-nums">{{ date('d M Y', strtotime($row->tanggal)) }}</p>
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
                            <p class="text-xs text-gray-400 max-w-xs leading-relaxed font-medium italic">Tidak ada rekaman event ditemukan untuk kriteria filter yang Anda pilih.</p>
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
