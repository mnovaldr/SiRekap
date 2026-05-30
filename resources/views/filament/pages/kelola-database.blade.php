<x-filament-panels::page>
<div class="space-y-6">
    @php $stats = $this->getStats(); @endphp

    <style>
    .custom-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .custom-stats-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
    .custom-stat-card {
        background-color: #fff;
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.02);
        border: 1px solid #e5e7eb;
    }
    .dark .custom-stat-card {
        background-color: #1f2937;
        border-color: #374151;
    }
    @media (max-width: 767px) {
        .custom-stat-card-span-2 {
            grid-column: span 2 / span 2;
        }
    }
    </style>

    {{-- Stats --}}
    <div class="custom-stats-grid">
        <div class="custom-stat-card">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Data</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="custom-stat-card">
            <p class="text-xs text-gray-500 dark:text-gray-400">Valid</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($stats['valid']) }}</p>
        </div>
        <div class="custom-stat-card">
            <p class="text-xs text-gray-500 dark:text-gray-400">Review</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ number_format($stats['review']) }}</p>
        </div>
        <div class="custom-stat-card">
            <p class="text-xs text-gray-500 dark:text-gray-400">Duplikat</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($stats['duplikat']) }}</p>
        </div>
        <div class="custom-stat-card custom-stat-card-span-2">
            <p class="text-xs text-gray-500 dark:text-gray-400">Activity Logs</p>
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-300 mt-1">{{ number_format($stats['logs']) }}</p>
        </div>
    </div>

    {{-- Maintenance Actions --}}
    <x-filament::section heading="Pemeliharaan Data" description="Perbaiki dan sinkronkan data yang ada di database">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Recalculate Keys --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-arrow-path class="w-6 h-6 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">Recalculate Keys</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 text-xs">
                            Hitung ulang bulan, key_full, dan key_short. Jalankan setelah edit data manual.
                        </p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('Recalculate Keys', 'Hitung ulang bulan, key_full, dan key_short untuk semua data?')) { $wire.recalculateKeys() }"
                                color="info"
                                size="sm"
                                icon="heroicon-o-arrow-path"
                            >
                                Recalculate
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Re-detect Duplikat --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-magnifying-glass class="w-6 h-6 text-purple-600 dark:text-purple-400 mt-0.5 shrink-0" />
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">Re-detect Duplikat</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 text-xs">
                            Scan ulang duplikat. Reset semua status lalu tandai ulang Duplikat & Review.
                        </p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('Re-detect Duplikat', 'Scan ulang duplikat? Semua status akan di-reset.')) { $wire.redetectDuplikat() }"
                                color="warning"
                                size="sm"
                                icon="heroicon-o-magnifying-glass"
                            >
                                Scan Duplikat
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recalculate Primary Rows --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-check-badge class="w-6 h-6 text-green-600 dark:text-green-400 mt-0.5 shrink-0" />
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">Recalculate Primary</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 text-xs">
                            Set ulang baris utama. Penting agar statistik dan chart dashboard tidak kosong.
                        </p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('Fix Chart Dashboard', 'Set ulang baris primary? Ini akan memperbaiki data pada chart dashboard.')) { $wire.recalculatePrimaryRows() }"
                                color="success"
                                size="sm"
                                icon="heroicon-o-check-badge"
                            >
                                Fix Chart
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </x-filament::section>

    {{-- Cleanup Actions --}}
    <x-filament::section heading="Bersihkan Data" description="Hapus atau validasi data berdasarkan status">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Hapus Duplikat --}}
            <div class="border border-red-200 dark:border-red-800 rounded-lg p-4 bg-red-50 dark:bg-red-900/10">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-trash class="w-6 h-6 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">Hapus Semua Duplikat</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Hapus semua data dengan status <span class="font-medium text-red-600">Duplikat</span> ({{ number_format($stats['duplikat']) }} data).
                        </p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('Hapus Duplikat', 'Hapus {{ $stats['duplikat'] }} data duplikat? Tindakan ini tidak bisa dibatalkan.')) { $wire.hapusDuplikat() }"
                                color="danger"
                                size="sm"
                                icon="heroicon-o-trash"
                            >
                                Hapus Duplikat ({{ number_format($stats['duplikat']) }})
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Validasi Semua Review --}}
            <div class="border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 bg-yellow-50 dark:bg-yellow-900/10">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mt-0.5 shrink-0" />
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">Validasi Semua Review</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Ubah semua data <span class="font-medium text-yellow-600">Review</span> menjadi Valid ({{ number_format($stats['review']) }} data).
                        </p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('Validasi Review', 'Validasi {{ $stats['review'] }} data review? Semua akan diubah menjadi Valid.', 'info')) { $wire.validasiSemuaReview() }"
                                color="warning"
                                size="sm"
                                icon="heroicon-o-check-circle"
                            >
                                Validasi Review ({{ number_format($stats['review']) }})
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </x-filament::section>

    {{-- Hapus Per Tahun --}}
    <x-filament::section heading="Hapus Data Per Tahun" description="Hapus semua data event untuk tahun tertentu">
        @php $years = $this->getAvailableYears(); @endphp
        @if(empty($years))
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada data tersedia.</p>
        @else
            <div class="flex flex-wrap gap-3">
                @foreach($years as $year)
                    @php $yearCount = \App\Models\Event::whereYear('tanggal', $year)->count(); @endphp
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 min-w-[160px]">
                        <p class="font-bold text-gray-900 dark:text-white text-lg">{{ $year }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($yearCount) }} data</p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('Hapus Data Tahun', 'Hapus semua {{ $yearCount }} data tahun {{ $year }}? Tindakan ini tidak bisa dibatalkan.')) { $wire.hapusDataTahun({{ $year }}) }"
                                color="danger"
                                size="sm"
                                icon="heroicon-o-trash"
                            >
                                Hapus {{ $year }}
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>

    {{-- Danger Zone --}}
    <x-filament::section heading="⚠️ Danger Zone" description="Tindakan berbahaya yang tidak bisa dibatalkan">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Hapus Activity Log --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-document-minus class="w-6 h-6 text-gray-600 dark:text-gray-400 mt-0.5 shrink-0" />
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">Hapus Activity Log</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Hapus semua riwayat aktivitas ({{ number_format($stats['logs']) }} log).
                        </p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('Hapus Activity Log', 'Hapus semua activity log? Riwayat aktivitas akan hilang permanen.')) { $wire.hapusActivityLog() }"
                                color="gray"
                                size="sm"
                                icon="heroicon-o-trash"
                            >
                                Hapus Log ({{ number_format($stats['logs']) }})
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hapus Semua Data --}}
            <div class="border border-red-300 dark:border-red-700 rounded-lg p-4 bg-red-50 dark:bg-red-900/20">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
                    <div class="flex-1">
                        <p class="font-semibold text-red-700 dark:text-red-400">Hapus SEMUA Data Event</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Hapus seluruh {{ number_format($stats['total']) }} data event dari database. <strong>Tidak bisa dibatalkan!</strong>
                        </p>
                        <div class="mt-3">
                            <x-filament::button
                                x-on:click.prevent="if(await window.siConfirm('⚠️ HAPUS SEMUA DATA', 'PERINGATAN! Ini akan menghapus SEMUA {{ $stats['total'] }} data event. Tindakan ini permanen!')) { $wire.hapusSemuaData() }"
                                color="danger"
                                size="sm"
                                icon="heroicon-o-exclamation-triangle"
                            >
                                Hapus Semua Data
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </x-filament::section>
</div>
</x-filament-panels::page>
