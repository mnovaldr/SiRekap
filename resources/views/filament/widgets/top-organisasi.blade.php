<x-filament-widgets::widget>
    <x-filament::section class="rounded-[2rem] shadow-sm border-gray-100 dark:border-gray-800">
        <x-slot name="heading">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                        <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <span class="text-base font-extrabold tracking-tight">Organisasi & Aktivitas Kegiatan</span>
                    </div>
                </div>
                
                @php
                    $yearStart = $this->filters['year_start'] ?? null;
                    $yearEnd = $this->filters['year_end'] ?? null;
                    $year = ($yearStart && $yearEnd && $yearStart === $yearEnd) ? $yearStart : (($yearStart && $yearEnd) ? $yearStart . ' - ' . $yearEnd : null);
                    $month = $this->filters['month'] ?? null;
                    $period = ($month ? \App\Models\Event::getBulanIndonesia($month) : '') . ($year ? ' ' . $year : '');
                    $period = trim($period) ?: 'Semua Periode';
                @endphp
                <span class="text-xs font-semibold text-gray-500 bg-gray-50 dark:bg-gray-800 px-3 py-1 rounded-full border border-gray-100 dark:border-gray-700">Periode: {{ $period }}</span>
            </div>
        </x-slot>

        <!-- Widget Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-2 mb-4 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Tipe Data Toggler -->
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipe Data</span>
                    <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1" role="group">
                        <button 
                            type="button" 
                            wire:click="$set('displayType', 'unique')"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 {{ $this->displayType === 'unique' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                        >
                            Hanya Data Unik
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('displayType', 'all')"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 {{ $this->displayType === 'all' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                        >
                            Semua Data
                        </button>
                    </div>
                </div>

                <!-- Mode Tampilan Toggler -->
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tampilan</span>
                    <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1" role="group">
                        <button 
                            type="button" 
                            wire:click="$set('listMode', 'top10')"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 {{ $this->listMode === 'top10' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                        >
                            Top 10
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('listMode', 'all')"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 {{ $this->listMode === 'all' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                        >
                            Lihat Semua
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Input (only when listMode is 'all') -->
            @if($this->listMode === 'all')
                <div class="flex flex-col gap-1 w-full sm:w-80">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pencarian</span>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400" />
                        </div>
                        <input 
                            type="search" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Cari organisasi, judul, lokasi..."
                            style="padding-left: 2.5rem !important;"
                            class="w-full pr-4 py-2 text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-gray-100 shadow-sm"
                        />
                    </div>
                </div>
            @endif
        </div>

        @if($this->listMode === 'top10')
            <!-- Top 10 Table -->
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 dark:bg-gray-800/75 border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest w-16 text-center">Rank</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Organisasi</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-28">Event</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-28">Variasi</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-28">Peserta</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                        @php
                            $res = $this->getTopOrganisasiData();
                            $items = $res['data'];
                            $grandTotal = $res['grandTotal'];
                        @endphp
                        @forelse($items as $index => $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-primary-900/5 transition-all group">
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-black text-xs
                                    {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : '' }}
                                    {{ $index === 1 ? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : '' }}
                                    {{ $index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400' : '' }}
                                    {{ $index > 2 ? 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400' : '' }}">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-black dark:text-gray-200 group-hover:text-primary-600 transition-colors">{{ $item['nama_organisasi'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 bg-primary-50 dark:bg-primary-900/30 rounded-lg text-xs font-black text-primary-700 dark:text-primary-400 tabular-nums">
                                    {{ number_format($item['total_event']) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums">{{ number_format($item['jenis_event']) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-black dark:text-gray-300 tabular-nums">{{ number_format($item['total_peserta']) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                                        <div class="bg-primary-500 h-full rounded-full transition-all duration-1000" 
                                             style="width: {{ ($item['total_event'] / $grandTotal) * 100 }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-gray-400 w-8 text-right tabular-nums">
                                        {{ round(($item['total_event'] / $grandTotal) * 100, 1) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm italic">Tidak ada data ditemukan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <!-- Detailed Table -->
            @php
                $records = $this->getDetailedRecords();
                $currentPage = $records->currentPage();
                $perPage = $records->perPage();
            @endphp

            @if($this->displayType === 'unique')
                <!-- Unique Organizations Table -->
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 dark:bg-gray-800/75 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest w-16 text-center">No</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Organisasi</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Bidang / Kategori</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-28">Total Event</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-32">Total Peserta</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-36">Kegiatan Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                            @forelse($records as $index => $record)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-primary-900/5 transition-all group">
                                <td class="px-5 py-4 text-center text-xs font-bold text-gray-400 dark:text-gray-500 tabular-nums">
                                    {{ ($currentPage - 1) * $perPage + $index + 1 }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-sm font-black dark:text-gray-200 group-hover:text-primary-600 transition-colors">{{ $record->nama_organisasi }}</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                    {{ $record->bidang ?: '-' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-primary-50 dark:bg-primary-900/30 rounded-lg text-xs font-black text-primary-700 dark:text-primary-400 tabular-nums">
                                        {{ number_format($record->total_event) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-xs font-black dark:text-gray-300 tabular-nums">{{ number_format($record->total_peserta) }}</span>
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-gray-500 dark:text-gray-400 tabular-nums font-medium">
                                    {{ $record->tanggal_terakhir ? \Carbon\Carbon::parse($record->tanggal_terakhir)->format('d M Y') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm italic">Tidak ada data ditemukan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <!-- All Events Table -->
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 dark:bg-gray-800/75 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest w-16 text-center">No</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Organisasi</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Judul Kegiatan</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-32">Tanggal</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Lokasi</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-16">Lt</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-24">Peserta</th>
                                <th class="px-5 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                            @forelse($records as $index => $record)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-primary-900/5 transition-all group">
                                <td class="px-5 py-4 text-center text-xs font-bold text-gray-400 dark:text-gray-500 tabular-nums">
                                    {{ ($currentPage - 1) * $perPage + $index + 1 }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-sm font-black dark:text-gray-200 group-hover:text-primary-600 transition-colors">{{ $record->nama_organisasi }}</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-700 dark:text-gray-300 font-semibold max-w-xs truncate" title="{{ $record->judul_kegiatan }}">
                                    {{ $record->judul_kegiatan }}
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-gray-500 dark:text-gray-400 tabular-nums font-medium">
                                    {{ $record->tanggal ? $record->tanggal->format('d M Y') : '-' }}
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate font-medium" title="{{ $record->lokasi }}">
                                    {{ $record->lokasi }}
                                </td>
                                <td class="px-5 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300">
                                    {{ $record->lantai }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-xs font-black dark:text-gray-300 tabular-nums">{{ number_format($record->jumlah_peserta) }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/30">
                                        Valid
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm italic">Tidak ada data ditemukan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Pagination Links -->
            <div class="mt-4 flex justify-between items-center px-2">
                <span class="text-xs text-gray-500 font-medium">
                    Menampilkan {{ number_format(($currentPage - 1) * $perPage + 1) }} - {{ number_format(min($currentPage * $perPage, $records->total())) }} dari {{ number_format($records->total()) }} data
                </span>
                <div>
                    {{ $records->links() }}
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
