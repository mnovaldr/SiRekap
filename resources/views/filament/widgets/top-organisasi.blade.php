<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Top 10 Organisasi Paling Aktif
        </x-slot>

        <x-slot name="description">
            @php
                $year = $this->filters['year'] ?? null;
                $month = $this->filters['month'] ?? null;
                $period = ($month ? \App\Models\Event::getBulanIndonesia($month) : '') . ($year ? ' ' . $year : '');
                $period = trim($period) ?: 'Semua Periode';
            @endphp
            Organisasi dengan event terbanyak: {{ $period }}
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Rank</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nama Organisasi</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Total Event</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Jenis Event</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Total Peserta</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $maxTotal = collect($this->getTopOrganisasi())->max('total_event') ?: 1;
                    @endphp
                    @forelse($this->getTopOrganisasi() as $index => $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ $index === 1 ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}
                                {{ $index === 2 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                {{ $index > 2 ? 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400' : '' }}
                                font-bold text-sm">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            {{ $item['nama_organisasi'] }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ number_format($item['total_event']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ number_format($item['jenis_event']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">
                            {{ number_format($item['total_peserta']) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" 
                                         style="width: {{ ($item['total_event'] / $maxTotal) * 100 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 w-12 text-right">
                                    {{ round(($item['total_event'] / $maxTotal) * 100) }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada data organisasi untuk tahun {{ $this->filters['year'] ?? date('Y') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
