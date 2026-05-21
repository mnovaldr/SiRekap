<x-filament-widgets::widget>
    <x-filament::section class="rounded-[2rem] shadow-sm border-gray-100 dark:border-gray-800">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                    <x-heroicon-o-globe-asia-pacific class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
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
            @endphp
            <span class="text-xs text-gray-500">
                Filter: {{ $period }} | Terdeteksi {{ number_format($totalWithSdgs) }} dari {{ number_format($totalEvents) }} event memiliki program SDGs.
            </span>
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
                @endphp
                
                <div class="relative bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm hover:shadow-md dark:hover:shadow-indigo-950/20 transition-all duration-300 group overflow-hidden flex flex-col justify-between min-h-[140px]">
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
</x-filament-widgets::widget>
