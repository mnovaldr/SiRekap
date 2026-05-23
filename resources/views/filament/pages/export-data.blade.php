<x-filament-panels::page>
<div class="space-y-6 pb-8" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800/80 rounded-[2rem] shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4">
                <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100/50 dark:border-indigo-900/30 rounded-2xl">
                    <x-heroicon-o-arrow-down-tray class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        Export Data Engine
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                        Ekstraksi data terstruktur untuk laporan dan integrasi sistem
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         LAYOUT CONTAINER WITH LOADING OVERLAY
    ══════════════════════════════════════════ --}}
    <div class="relative">
        <!-- Premium Glassmorphism Loading Overlay -->
        <div wire:loading wire:target="export" class="absolute inset-0 bg-white/75 dark:bg-slate-950/75 backdrop-blur-md z-50 flex flex-col items-center justify-center rounded-[2rem] transition-all duration-300">
            <div class="flex flex-col items-center gap-5 text-center max-w-md p-8 bg-white dark:bg-slate-900 shadow-2xl rounded-3xl border border-gray-100/80 dark:border-slate-800">
                <div class="relative w-20 h-20">
                    <div class="absolute inset-0 border-4 border-indigo-100 dark:border-indigo-950 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-t-indigo-600 dark:border-t-indigo-400 rounded-full animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl">📥</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Menyiapkan Berkas Ekspor</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Sedang mengompilasi data ke dalam format <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold rounded text-[10px]">{{ strtoupper($selectedFormat) }}</span>. Proses ini dapat memakan waktu beberapa detik tergantung pada rentang data yang dipilih.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- ══════════════════════════════════════════
                 CONFIGURATION PANEL (4/12)
            ══════════════════════════════════════════ --}}
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800/80 rounded-[2rem] shadow-sm overflow-hidden lg:sticky lg:top-24">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800/50">
                        <div class="text-base font-black text-gray-900 dark:text-white">Export Configuration</div>
                        <div class="text-xs text-gray-400 dark:text-slate-500 mt-1">Pilih parameter export</div>
                    </div>
                    
                    <div class="p-6 flex flex-col gap-6">
                        {{-- Export Type Picker --}}
                        <div class="flex flex-col gap-2.5">
                            <label class="text-[0.68rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Tipe Export</label>
                            <div class="flex flex-col gap-2">
                                @foreach($exportTypes as $type)
                                    <label class="flex items-center p-4 border-2 rounded-2xl cursor-pointer transition-all duration-300 hover:border-indigo-400 dark:hover:border-indigo-500 {{ $exportType === $type['id'] ? 'border-indigo-600 bg-indigo-50/40 dark:border-indigo-500 dark:bg-indigo-950/20' : 'border-gray-100 bg-gray-50/30 dark:border-slate-800/50 dark:bg-slate-900/50' }}">
                                        <input type="radio" wire:model.live="exportType" value="{{ $type['id'] }}" class="hidden">
                                        <span class="text-2xl mr-4 flex-shrink-0">{{ $type['icon'] }}</span>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $type['name'] }}</div>
                                            <div class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5 truncate leading-tight">{{ $type['desc'] }}</div>
                                        </div>
                                        @if($exportType === $type['id'])
                                            <div class="w-5 h-5 bg-indigo-600 dark:bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                                                <x-heroicon-o-check class="w-3.5 h-3.5 text-white stroke-[3]" />
                                            </div>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Year Range Picker --}}
                        <div class="flex flex-col gap-2.5">
                            <label class="text-[0.68rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Rentang Periode</label>
                            <div class="grid grid-cols-5 gap-2 items-center">
                                <div class="col-span-2">
                                    <select wire:model.live="selectedYearStart" 
                                        class="w-full px-3 py-2.5 bg-gray-50/50 dark:bg-slate-950 border border-gray-100 dark:border-slate-800/80 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                        @foreach($this->getAvailableYears() as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-1 text-center text-xs font-bold text-gray-400 dark:text-slate-500">—</div>
                                <div class="col-span-2">
                                    <select wire:model.live="selectedYearEnd" 
                                        class="w-full px-3 py-2.5 bg-gray-50/50 dark:bg-slate-950 border border-gray-100 dark:border-slate-800/80 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                        @foreach($this->getAvailableYears() as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-1 text-[11px] text-gray-400 dark:text-slate-500 font-semibold mt-1">
                                <span>📅</span>
                                <span>
                                    @if($selectedYearStart === $selectedYearEnd)
                                        Tahun {{ $selectedYearEnd }}
                                    @else
                                        Periode {{ $selectedYearStart }} - {{ $selectedYearEnd }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Month Picker (only for detail export) --}}
                        @if($exportType === 'detail')
                        <div class="flex flex-col gap-2.5">
                            <label class="text-[0.68rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Filter Bulan (Opsional)</label>
                            <select wire:model.live="selectedMonth"
                                class="w-full px-3 py-2.5 bg-gray-50/50 dark:bg-slate-950 border border-gray-100 dark:border-slate-800/80 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                @foreach($months as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Format Picker --}}
                        <div class="flex flex-col gap-2.5">
                            <label class="text-[0.68rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Format Output</label>
                            <div class="flex flex-col gap-2">
                                @foreach($formats as $format)
                                    <label class="flex items-center p-4 border-2 rounded-2xl cursor-pointer transition-all duration-300 hover:border-indigo-400 dark:hover:border-indigo-500 {{ $selectedFormat === $format['id'] ? 'border-indigo-600 bg-indigo-50/40 dark:border-indigo-500 dark:bg-indigo-950/20' : 'border-gray-100 bg-gray-50/30 dark:border-slate-800/50 dark:bg-slate-900/50' }}">
                                        <input type="radio" wire:model.live="selectedFormat" value="{{ $format['id'] }}" class="hidden">
                                        <span class="text-2xl mr-4 flex-shrink-0">{{ $format['icon'] }}</span>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $format['name'] }}</div>
                                            <div class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5 truncate leading-tight">{{ $format['desc'] }}</div>
                                        </div>
                                        @if($selectedFormat === $format['id'])
                                            <div class="w-5 h-5 bg-indigo-600 dark:bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                                                <x-heroicon-o-check class="w-3.5 h-3.5 text-white stroke-[3]" />
                                            </div>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Export Button --}}
                        <button wire:click="export" wire:loading.attr="disabled"
                            class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl text-xs font-extrabold uppercase tracking-wider transition-all duration-300 hover:from-indigo-500 hover:to-violet-500 hover:shadow-lg hover:shadow-indigo-500/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 active:scale-95 disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-2">
                            <span wire:loading.remove>Generate Download</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-4.5 w-4.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
                 PREVIEW PANEL (8/12)
            ══════════════════════════════════════════ --}}
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800/80 rounded-[2rem] shadow-sm overflow-hidden flex flex-col min-height-[650px] min-h-[650px]">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800/50 flex justify-between items-center flex-wrap gap-3">
                        <div>
                            <div class="text-base font-black text-gray-900 dark:text-white">
                                {{ $exportType === 'summary' ? 'Laporan Analitik' : 'Data Pipeline Preview' }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                                @if($exportType === 'summary')
                                    Ringkasan analitik lengkap ekosistem ekraf
                                @else
                                    Pratinjau berkas ekspor • 
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($totalRows) }} records</span> tersedia
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ ($exportType === 'summary' || $totalRows > 0) ? 'bg-emerald-500' : 'bg-rose-500' }} inline-block animate-pulse"></span>
                            <span class="text-xs font-bold text-gray-400 dark:text-slate-500">{{ ($exportType === 'summary' || $totalRows > 0) ? 'Ready' : 'Empty' }}</span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-x-auto flex flex-col justify-start">
                        @if($exportType === 'summary')
                            {{-- Summary Preview --}}
                            <div class="flex flex-col items-center justify-center p-12 text-center my-auto">
                                <div class="w-20 h-20 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100/50 dark:border-indigo-900/30 rounded-3xl flex items-center justify-center mb-6 shadow-md shadow-indigo-500/5">
                                    <x-heroicon-o-chart-bar class="w-10 h-10 text-indigo-600 dark:text-indigo-400 animate-pulse" />
                                </div>
                                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Laporan Analitik Siap Diekspor</h4>
                                <p class="text-xs text-gray-500 dark:text-slate-400 max-w-sm mb-8 leading-relaxed">
                                    Format ini akan menghasilkan rekapitulasi data analitik kinerja bulanan, sub-sektor, dan jenis kegiatan secara ringkas.
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-lg text-left">
                                    <div class="flex items-center gap-3 p-4 bg-gray-50/50 dark:bg-slate-800/30 border border-gray-100/50 dark:border-slate-800 rounded-2xl">
                                        <span class="text-lg">📈</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-slate-300">Ringkasan Statistik Utama</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-4 bg-gray-50/50 dark:bg-slate-800/30 border border-gray-100/50 dark:border-slate-800 rounded-2xl">
                                        <span class="text-lg">🎨</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-slate-300">Distribusi 17 Sub Sektor</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-4 bg-gray-50/50 dark:bg-slate-800/30 border border-gray-100/50 dark:border-slate-800 rounded-2xl">
                                        <span class="text-lg">🎭</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-slate-300">Statistik Kategori Kegiatan</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-4 bg-gray-50/50 dark:bg-slate-800/30 border border-gray-100/50 dark:border-slate-800 rounded-2xl">
                                        <span class="text-lg">📆</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-slate-300">Rekap Bulanan per Tahun</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Detail Preview --}}
                            @php $previewData = $this->getPreviewData(); @endphp
                            @if($previewData->count() > 0)
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50/50 dark:bg-slate-800/40 border-b border-gray-100 dark:border-slate-800/50">
                                            <th class="px-6 py-4 text-[0.7rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Event Identity</th>
                                            <th class="px-6 py-4 text-[0.7rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Spatial Data</th>
                                            <th class="px-6 py-4 text-[0.7rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider text-right">Temporal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100/50 dark:divide-slate-800/50">
                                        @foreach($previewData as $row)
                                            <tr class="hover:bg-indigo-50/10 dark:hover:bg-indigo-950/10 transition-colors duration-200">
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-white max-w-[250px] truncate" title="{{ $row->judul_kegiatan }}">{{ $row->judul_kegiatan }}</div>
                                                    <div class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ $row->nama_organisasi }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-full text-xs font-bold">
                                                        {{ $row->lokasi }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white text-right">
                                                    {{ date('d M Y', strtotime($row->tanggal)) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="flex flex-col items-center justify-center p-12 text-center my-auto">
                                    <div class="w-20 h-20 bg-amber-50 dark:bg-amber-950/20 border border-amber-100/50 dark:border-amber-900/30 rounded-3xl flex items-center justify-center mb-6 shadow-md shadow-amber-500/5">
                                        <x-heroicon-o-exclamation-triangle class="w-10 h-10 text-amber-600 dark:text-amber-500" />
                                    </div>
                                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Tidak Ada Data Ditemukan</h4>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 max-w-sm mb-6 leading-relaxed">
                                        Tidak ditemukan event berstatus <strong class="text-gray-900 dark:text-white">Valid</strong> pada rentang periode yang dipilih.
                                    </p>
                                    <div class="inline-flex items-center gap-2.5 px-4 py-3 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100/50 dark:border-indigo-900/30 rounded-2xl text-[11px] font-bold text-indigo-700 dark:text-indigo-400 max-w-sm">
                                        <span>💡</span>
                                        <span>Coba pilih bulan atau tahun lain, atau pastikan data event sudah di-import.</span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Pagination --}}
                    @if($exportType === 'detail' && $previewData->count() > 0)
                        <div class="px-6 py-4 bg-gray-50/50 dark:bg-slate-800/30 border-t border-gray-100 dark:border-slate-800/50">
                            {{ $previewData->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
</x-filament-panels::page>
