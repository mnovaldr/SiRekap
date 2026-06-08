<x-filament-widgets::widget>
    <div x-data="{ open: false, loading: false }">
        <style>
            [x-cloak] { display: none !important; }

            /* Modal Backdrop */
            .sdg-modal-backdrop {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background-color: rgba(15, 23, 42, 0.65) !important;
                backdrop-filter: blur(12px) !important;
                -webkit-backdrop-filter: blur(12px) !important;
                z-index: 1000 !important;
                pointer-events: auto !important;
            }
            .dark .sdg-modal-backdrop {
                background-color: rgba(10, 15, 30, 0.85) !important;
            }

            /* Modal Wrapper */
            .sdg-modal-container {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                display: flex;
                align-items: center !important;
                justify-content: center !important;
                padding: 24px !important;
                z-index: 100000 !important;
                pointer-events: none !important;
            }

            /* Modal Card */
            .sdg-modal-card {
                position: relative !important;
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 24px !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3) !important;
                width: 100% !important;
                max-width: 950px !important;
                height: 560px !important; /* Fixed height to prevent jumps */
                max-height: 80vh !important;
                display: flex;
                flex-direction: column !important;
                overflow: hidden !important;
                pointer-events: auto !important;
                z-index: 2000 !important;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            .dark .sdg-modal-card {
                background-color: #111827 !important;
                border-color: #1f2937 !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
            }

            /* Ensure flex layout is maintained during loading toggles */
            .sdg-modal-card > div[wire\:loading] {
                display: flex;
                flex-direction: column;
                flex: 1;
                height: 100%;
            }
            .sdg-modal-card > div[wire\:loading\.remove] {
                display: flex;
                flex-direction: column;
                flex: 1;
                height: 100%;
            }

            /* Scrollable Content */
            .sdg-modal-body {
                flex: 1 !important;
                overflow-y: auto !important;
                padding: 20px 24px !important;
            }

            .sdg-modal-header {
                padding: 16px 24px !important;
                border-bottom: 1px solid #e2e8f0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                flex-shrink: 0 !important;
            }
            .dark .sdg-modal-header {
                border-bottom-color: #1f2937 !important;
            }

            .sdg-modal-footer {
                padding: 16px 24px !important;
                border-top: 1px solid #e2e8f0 !important;
                background-color: #f9fafb !important;
                flex-shrink: 0 !important;
            }
            .dark .sdg-modal-footer {
                border-top-color: #1f2937 !important;
                background-color: #111827 !important;
            }

            /* Search Input styling */
            .sdg-search-input {
                width: 100% !important;
                padding-left: 36px !important;
                padding-right: 36px !important;
                padding-top: 8px !important;
                padding-bottom: 8px !important;
                font-size: 12px !important;
                background-color: #f9fafb !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 10px !important;
                outline: none !important;
                color: #0f172a !important;
                transition: all 0.2s ease !important;
            }
            .sdg-search-input:focus {
                border-color: #4f46e5 !important;
                background-color: #ffffff !important;
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important;
            }
            .dark .sdg-search-input {
                background-color: #1f2937 !important;
                border-color: #374151 !important;
                color: #f3f4f6 !important;
            }
            .dark .sdg-search-input:focus {
                border-color: #6366f1 !important;
                background-color: #111827 !important;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
            }

            /* Clean up pagination layout in footer */
            .sdg-modal-footer nav {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                width: 100% !important;
            }
            .sdg-modal-footer nav p {
                margin: 0 !important;
                font-size: 11px !important;
                font-weight: 800 !important;
                color: #64748b !important;
            }
            .dark .sdg-modal-footer nav p {
                color: #94a3b8 !important;
            }
            .sdg-modal-footer nav span.relative,
            .sdg-modal-footer nav a {
                font-size: 11px !important;
                font-weight: 800 !important;
                border-radius: 8px !important;
            }
            .sdg-modal-footer a {
                padding: 4px 10px !important;
                transition: background-color 0.2s !important;
            }
            .sdg-modal-footer a:hover {
                background-color: #e2e8f0 !important;
            }
            .dark .sdg-modal-footer a:hover {
                background-color: #374151 !important;
            }
        </style>
    <x-filament::section class="rounded-[2rem] shadow-sm border-gray-100 dark:border-gray-800">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                    <x-heroicon-o-globe-alt class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <span class="text-lg font-black tracking-tight">Capaian Kategori Program SDGs</span>
            </div>
        </x-slot>

        <x-slot name="description">
            @php
                $yearStart = $this->filters['year_start'] ?? null;
                $yearEnd = $this->filters['year_end'] ?? null;
                $month = $this->filters['month'] ?? null;
                $subSektor = $this->filters['sub_sektor'] ?? null;
                
                $period = '';
                if ($month) {
                    $period .= \App\Models\Event::getBulanIndonesia($month) . ' ';
                }
                if ($yearStart && $yearEnd) {
                    $period .= $yearStart == $yearEnd ? $yearStart : "$yearStart - $yearEnd";
                } elseif ($yearStart) {
                    $period .= $yearStart;
                } elseif ($yearEnd) {
                    $period .= $yearEnd;
                }
                $period = trim($period) ?: 'Semua Periode';
                
                $res = $this->getSdgsData();
                $counts = $res['counts'];
                $totalEvents = $res['totalEvents'];
                $totalWithSdgs = $res['totalWithSdgs'];
                $percentageWithSdgs = $totalEvents > 0 ? ($totalWithSdgs / $totalEvents) * 100 : 0;
            @endphp
            <div class="flex flex-wrap gap-2 items-center mt-2">
                <!-- Periode Pill -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100/50 dark:border-indigo-900/30 rounded-full text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                    <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                    <span>Periode: {{ $period }}</span>
                </div>

                <!-- Sub Sektor Pill (if filtered) -->
                @if($subSektor)
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-sky-50/50 dark:bg-sky-950/20 border border-sky-100/50 dark:border-sky-900/30 rounded-full text-xs font-semibold text-sky-700 dark:text-sky-300">
                    <x-heroicon-o-tag class="w-3.5 h-3.5" />
                    <span>Sektor: {{ $subSektor }}</span>
                </div>
                @endif

                <!-- SDG Match Status Pill -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100/50 dark:border-emerald-900/30 rounded-full text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                    <x-heroicon-o-check-circle class="w-3.5 h-3.5 text-emerald-500" />
                    <span>{{ number_format($totalWithSdgs) }} dari {{ number_format($totalEvents) }} event ({{ round($percentageWithSdgs, 1) }}%) memiliki data SDGs</span>
                </div>
            </div>
        </x-slot>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mt-6">
            @php
                $sdgMeta = [
                    'SDGS 1' => ['title' => 'Tanpa Kemiskinan', 'color' => '#E5243B', 'text' => 'text-white'],
                    'SDGS 2' => ['title' => 'Tanpa Kelaparan', 'color' => '#DDA63A', 'text' => 'text-white'],
                    'SDGS 3' => ['title' => 'Kehidupan Sehat & Sejahtera', 'color' => '#4C9F38', 'text' => 'text-white'],
                    'SDGS 4' => ['title' => 'Pendidikan Berkualitas', 'color' => '#C5192D', 'text' => 'text-white'],
                    'SDGS 5' => ['title' => 'Kesetaraan Gender', 'color' => '#FF3A21', 'text' => 'text-white'],
                    'SDGS 6' => ['title' => 'Air Bersih & Sanitasi Layak', 'color' => '#26BDE2', 'text' => 'text-white'],
                    'SDGS 7' => ['title' => 'Energi Bersih & Terjangkau', 'color' => '#FCC30B', 'text' => 'text-gray-900'],
                    'SDGS 8' => ['title' => 'Pekerjaan Layak & Pertumbuhan Ekonomi', 'color' => '#A21942', 'text' => 'text-white'],
                    'SDGS 9' => ['title' => 'Industri, Inovasi & Infrastruktur', 'color' => '#FD6925', 'text' => 'text-white'],
                    'SDGS 10' => ['title' => 'Berkurangnya Kesenjangan', 'color' => '#DD1367', 'text' => 'text-white'],
                    'SDGS 11' => ['title' => 'Kota & Pemukiman Berkelanjutan', 'color' => '#FD9D24', 'text' => 'text-white'],
                    'SDGS 12' => ['title' => 'Konsumsi & Produksi Bertanggung Jawab', 'color' => '#C9992D', 'text' => 'text-white'],
                    'SDGS 13' => ['title' => 'Penanganan Perubahan Iklim', 'color' => '#3F7E44', 'text' => 'text-white'],
                    'SDGS 14' => ['title' => 'Ekosistem Lautan', 'color' => '#0A97D9', 'text' => 'text-white'],
                    'SDGS 15' => ['title' => 'Ekosistem Daratan', 'color' => '#56C02B', 'text' => 'text-white'],
                    'SDGS 16' => ['title' => 'Perdamaian, Keadilan & Institusi Tangguh', 'color' => '#00689D', 'text' => 'text-white'],
                    'SDGS 17' => ['title' => 'Kemitraan untuk Mencapai Tujuan', 'color' => '#19486A', 'text' => 'text-white'],
                ];
            @endphp

            @foreach($counts as $code => $count)
                @php
                    $meta = $sdgMeta[$code] ?? ['title' => 'Program SDGs', 'color' => '#6b7280', 'text' => 'text-white'];
                    $num = str_replace('SDGS ', '', $code);
                    $percentage = $totalEvents > 0 ? ($count / $totalEvents) * 100 : 0;
                    $isInactive = $count === 0;
                @endphp
                
                <div 
                    @if(!$isInactive)
                        wire:click="showEvents('{{ $code }}')"
                        @click="open = true; loading = true"
                        role="button"
                        tabindex="0"
                    @endif
                    class="relative bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm transition-all duration-300 group overflow-hidden flex flex-col justify-between min-h-[140px] {{ $isInactive ? 'opacity-40 saturate-50' : 'cursor-pointer hover:shadow-md hover:-translate-y-1 dark:hover:shadow-indigo-950/20' }}"
                >
                    <!-- SDG Color Accent Bar -->
                    <div class="absolute top-0 left-0 w-full h-[4px]" style="background-color: {{ $meta['color'] }};"></div>
                    
                    <div class="flex justify-between items-start gap-2 mt-1">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-black px-2 py-0.5 rounded {{ $meta['text'] }} transition-transform group-hover:scale-105" style="background-color: {{ $meta['color'] }};">
                                    {{ $num }}
                                </span>
                                <span class="text-[9px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    SDG
                                </span>
                            </div>
                            <h4 class="text-[11px] font-black text-gray-700 dark:text-gray-200 mt-2 line-clamp-2 leading-snug group-hover:text-primary-600 transition-colors" title="{{ $meta['title'] }}">
                                {{ $meta['title'] }}
                            </h4>
                        </div>
                        
                        <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-50 dark:bg-gray-800/80 rounded text-[11px] font-black text-gray-700 dark:text-gray-300 tabular-nums">
                            {{ number_format($count) }}
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="flex justify-between items-center text-[9px] font-black text-gray-400 dark:text-gray-500 mb-1">
                            <span>Utilitas</span>
                            <span class="tabular-nums">{{ round($percentage, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000" 
                                 style="width: {{ $percentage }}%; background-color: {{ $meta['color'] }};"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

        <!-- Modal Events Detail -->
        <template x-teleport="body">
            <div 
                x-show="open"
                class="sdg-modal-container"
                role="dialog"
                aria-modal="true"
                x-cloak
            >
                <!-- Backdrop with fade transition -->
                <div 
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="open = false; $wire.closeEvents(); loading = false"
                    class="sdg-modal-backdrop"
                ></div>

                <!-- Modal Panel with scale/fade transition -->
                <div 
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="sdg-modal-card"
                >
                    <!-- Loading Skeleton -->
                    <div x-show="loading" class="w-full flex-grow flex flex-col" style="height: 100%;">
                        <!-- Skeleton Header -->
                        <div class="sdg-modal-header animate-pulse">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                                <div class="h-5 w-48 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                            </div>
                        </div>

                        <!-- Skeleton Content (Table) -->
                        <div class="sdg-modal-body">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 16px;">
                                <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                                <div class="h-8 w-64 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                            </div>
                            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="w-full text-left border-collapse border-0">
                                    <thead>
                                        <tr class="bg-gray-50/60 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Organisasi</th>
                                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Judul Kegiatan</th>
                                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Lokasi & Lantai</th>
                                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Peserta</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                        @for($i = 0; $i < 5; $i++)
                                        <tr class="animate-pulse">
                                            <td class="px-4 py-3"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-32"></div></td>
                                            <td class="px-4 py-3"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-48"></div></td>
                                            <td class="px-4 py-3"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16"></div></td>
                                            <td class="px-4 py-3"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24"></div></td>
                                            <td class="px-4 py-3"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-8 mx-auto"></div></td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Real Content -->
                    <div x-show="!loading" class="w-full flex-grow flex flex-col" style="height: 100%;">
                        @if($selectedSdg)
                            <div x-init="loading = false"></div>
                            @php
                                $events = $this->getSdgEvents();
                                $selectedSdgMeta = $sdgMeta[$selectedSdg] ?? ['title' => 'Program SDGs', 'color' => '#6b7280', 'text' => 'text-white'];
                                $selectedSdgNum = str_replace('SDGS ', '', $selectedSdg);
                            @endphp

                            <!-- Color bar accent -->
                            <div class="h-1.5 w-full shrink-0" style="background-color: {{ $selectedSdgMeta['color'] }};"></div>

                            <!-- Modal Header -->
                            <div class="sdg-modal-header">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-black px-2.5 py-1 rounded {{ $selectedSdgMeta['text'] }}" style="background-color: {{ $selectedSdgMeta['color'] }};">
                                        SDG {{ $selectedSdgNum }}
                                    </span>
                                    <h3 class="text-base font-black text-gray-800 dark:text-gray-100 leading-tight">
                                        {{ $selectedSdgMeta['title'] }}
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 bg-gray-50 dark:bg-gray-800 rounded-full text-[11px] font-black text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-800">
                                        {{ number_format($events->total()) }} Event
                                    </span>
                                </div>
                                
                                <button 
                                    @click="open = false; $wire.closeEvents(); loading = false" 
                                    type="button" 
                                    class="p-1.5 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-full border border-gray-100 dark:border-gray-800 transition-colors"
                                    title="Tutup"
                                >
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Modal Content (Table) -->
                            <div class="sdg-modal-body">
                                <!-- Search Bar & Info -->
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 16px; flex-wrap: wrap;">
                                    <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;" class="dark:text-gray-400">
                                        Daftar Event Terkait SDGs
                                    </div>
                                    <div style="position: relative; width: 100%; max-width: 320px; display: flex; align-items: center;">
                                        <div style="position: absolute; left: 12px; display: flex; align-items: center; justify-content: center; pointer-events: none;">
                                            <x-heroicon-o-magnifying-glass style="width: 14px; height: 14px; color: #94a3b8;" />
                                        </div>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.300ms="sdgSearch"
                                            placeholder="Cari organisasi atau kegiatan..."
                                            class="sdg-search-input"
                                            style="width: 100% !important;"
                                        >
                                        @if($sdgSearch)
                                            <button 
                                                wire:click="resetSearch" 
                                                type="button" 
                                                style="position: absolute; right: 12px; display: flex; align-items: center; justify-content: center; color: #94a3b8; border: none; background: transparent; cursor: pointer; padding: 0;"
                                            >
                                                <x-heroicon-o-x-mark style="width: 14px; height: 14px;" />
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                                    <table class="w-full text-left border-collapse border-0">
                                        <thead>
                                            <tr class="bg-gray-50/60 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Organisasi</th>
                                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Judul Kegiatan</th>
                                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Lokasi & Lantai</th>
                                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Peserta</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                            @forelse($events as $event)
                                            <tr class="hover:bg-gray-50/30 dark:hover:bg-primary-900/5 transition-colors group">
                                                <td class="px-4 py-3 text-xs font-black text-gray-800 dark:text-gray-200 group-hover:text-primary-600 transition-colors">
                                                    {{ $event->nama_organisasi }}
                                                </td>
                                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                                    {{ $event->judul_kegiatan }}
                                                </td>
                                                <td class="px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                    {{ $event->tanggal ? $event->tanggal->format('d M Y') : '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                    <div class="flex items-center gap-1">
                                                        <x-heroicon-o-map-pin class="w-3.5 h-3.5 text-gray-400" />
                                                        <span>{{ $event->lokasi ?: '-' }} ({{ $event->lantai ?: '-' }})</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-xs font-black text-gray-700 dark:text-gray-300 text-center tabular-nums">
                                                    {{ number_format($event->jumlah_peserta ?? 0) }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm italic">
                                                    Tidak ada detail event yang cocok dengan kata kunci atau filter
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Modal Footer with Pagination -->
                            @if($events && $events->hasPages())
                            <div class="sdg-modal-footer">
                                {{ $events->links(data: ['scrollTo' => false]) }}
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-filament-widgets::widget>
