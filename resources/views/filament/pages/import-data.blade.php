<x-filament-panels::page>
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Step Indicator --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            @php
                $steps = [
                    1 => ['label' => 'Unggah File',    'icon' => 'heroicon-o-arrow-up-tray'],
                    2 => ['label' => 'Validasi Data',  'icon' => 'heroicon-o-magnifying-glass'],
                    3 => ['label' => 'Konfirmasi',     'icon' => 'heroicon-o-clipboard-document-check'],
                    4 => ['label' => 'Selesai',        'icon' => 'heroicon-o-check-circle'],
                ];
            @endphp

            @foreach($steps as $s => $step)
                {{-- Step circle --}}
                <button wire:click="goToStep({{ $s }})"
                    class="flex items-center gap-2 shrink-0 {{ $s < $currentStep ? 'cursor-pointer' : 'cursor-default' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all
                        {{ $currentStep > $s  ? 'bg-green-500 text-white' : '' }}
                        {{ $currentStep === $s ? 'bg-primary-600 text-white ring-4 ring-primary-100 dark:ring-primary-900' : '' }}
                        {{ $currentStep < $s  ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500' : '' }}">
                        @if($currentStep > $s)
                            <x-heroicon-m-check class="w-4 h-4" />
                        @else
                            {{ $s }}
                        @endif
                    </div>
                    <span class="text-sm font-medium hidden sm:block
                        {{ $currentStep === $s ? 'text-primary-600 dark:text-primary-400' : '' }}
                        {{ $currentStep > $s  ? 'text-green-600 dark:text-green-400' : '' }}
                        {{ $currentStep < $s  ? 'text-gray-400 dark:text-gray-500' : '' }}">
                        {{ $step['label'] }}
                    </span>
                </button>

                {{-- Connector --}}
                @if(!$loop->last)
                    <div class="flex-1 mx-3 h-0.5 rounded-full transition-all duration-500
                        {{ $currentStep > $s ? 'bg-green-400' : 'bg-gray-200 dark:bg-gray-700' }}">
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ─── STEP 1: Upload ─────────────────────────────────────────────────── --}}
    @if($currentStep === 1)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Upload Area --}}
        <div class="lg:col-span-2">
            <x-filament::section>
                <x-slot name="heading">Unggah File Excel</x-slot>
                <x-slot name="description">Format yang didukung: .xlsx, .xls — Multi-sheet (Januari s/d Desember)</x-slot>

                <label for="file-upload" class="block cursor-pointer">
                    <input type="file" id="file-upload" class="sr-only" wire:model="file" accept=".xlsx,.xls">

                    <div wire:loading wire:target="file"
                        class="border-2 border-dashed border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 rounded-xl p-12 text-center">
                        <div class="flex flex-col items-center gap-4">
                            {{-- Spinner --}}
                            <div class="relative w-14 h-14">
                                <div class="absolute inset-0 rounded-full border-4 border-primary-100 dark:border-primary-800"></div>
                                <div class="absolute inset-0 rounded-full border-4 border-primary-600 border-t-transparent animate-spin"></div>
                            </div>
                            <div>
                                <p class="font-semibold text-primary-700 dark:text-primary-300">Memproses file...</p>
                                <p class="text-sm text-primary-500 dark:text-primary-400 mt-1">Membaca sheet, standarisasi data, deteksi duplikat</p>
                            </div>
                            {{-- Progress steps --}}
                            <div class="flex items-center gap-2 text-xs text-primary-600 dark:text-primary-400">
                                <span class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-primary-500 rounded-full animate-pulse"></span>
                                    Parsing Excel
                                </span>
                                <span class="text-primary-300">→</span>
                                <span class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-primary-300 rounded-full"></span>
                                    Standarisasi
                                </span>
                                <span class="text-primary-300">→</span>
                                <span class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-primary-300 rounded-full"></span>
                                    Cek Duplikat
                                </span>
                            </div>
                        </div>
                    </div>

                    <div wire:loading.remove wire:target="file"
                        class="border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 bg-gray-50 dark:bg-gray-800/50 hover:bg-primary-50 dark:hover:bg-primary-900/10 rounded-xl p-12 text-center transition-all duration-200 group">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 rounded-xl flex items-center justify-center transition-colors">
                                <x-heroicon-o-cloud-arrow-up class="w-7 h-7 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-300 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                    Klik untuk pilih file
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">atau drag & drop file Excel di sini</p>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-3 py-1 rounded-full">
                                .xlsx / .xls
                            </span>
                        </div>
                    </div>
                </label>
            </x-filament::section>
        </div>

        {{-- Info Panel --}}
        <div class="space-y-4">
            <x-filament::section>
                <x-slot name="heading">Panduan Format</x-slot>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-400">Sheet per bulan (Januari–Desember)</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-400">Baris pertama = header kolom</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-400">Multi-lokasi otomatis di-split</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-400">Duplikat terdeteksi otomatis</span>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Kolom yang Diperlukan</x-slot>
                <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                    @foreach(['Nama Organisasi', 'Bidang', 'Jenis Kegiatan', 'Judul Kegiatan', 'Tanggal', 'Lantai', 'Lokasi / Ruangan', 'Jumlah Peserta'] as $col)
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-primary-500 rounded-full shrink-0"></span>
                        {{ $col }}
                    </div>
                    @endforeach
                </div>
            </x-filament::section>
        </div>
    </div>
    @endif

    {{-- ─── STEP 2: Processing (auto-transition) ───────────────────────────── --}}
    @if($currentStep === 2 || ($currentStep === 1 && $isProcessing))
    <x-filament::section>
        <div class="py-12 flex flex-col items-center gap-6">
            <div class="relative w-20 h-20">
                <div class="absolute inset-0 rounded-full border-4 border-gray-100 dark:border-gray-700"></div>
                <div class="absolute inset-0 rounded-full border-4 border-primary-600 border-t-transparent animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <x-heroicon-o-cog-6-tooth class="w-7 h-7 text-primary-600 animate-pulse" />
                </div>
            </div>
            <div class="text-center">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">Memproses Data</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $processMessage ?: 'Mohon tunggu...' }}</p>
            </div>
            <div class="flex items-center gap-6 text-sm">
                <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400">
                    <span class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    Membaca file
                </div>
                <div class="flex items-center gap-2 text-gray-400">
                    <span class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    Standarisasi
                </div>
                <div class="flex items-center gap-2 text-gray-400">
                    <span class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    Cek duplikat
                </div>
            </div>
        </div>
    </x-filament::section>
    @endif

    {{-- ─── STEP 3: Preview & Confirm ──────────────────────────────────────── --}}
    @if($currentStep === 3)
    <div class="space-y-6">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $summaryCards = [
                    ['label' => 'Total Data',   'value' => $totalRows,    'color' => 'blue',   'icon' => 'heroicon-o-table-cells'],
                    ['label' => 'Siap Import',  'value' => $validRows,    'color' => 'green',  'icon' => 'heroicon-o-check-circle'],
                    ['label' => 'Perlu Review', 'value' => $reviewRows,   'color' => 'yellow', 'icon' => 'heroicon-o-exclamation-triangle'],
                    ['label' => 'Duplikat',     'value' => $duplikatRows, 'color' => 'red',    'icon' => 'heroicon-o-x-circle'],
                ];
            @endphp
            @foreach($summaryCards as $card)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    <x-dynamic-component :component="$card['icon']"
                        class="w-4 h-4
                            {{ $card['color'] === 'blue'   ? 'text-blue-500'   : '' }}
                            {{ $card['color'] === 'green'  ? 'text-green-500'  : '' }}
                            {{ $card['color'] === 'yellow' ? 'text-yellow-500' : '' }}
                            {{ $card['color'] === 'red'    ? 'text-red-500'    : '' }}" />
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($card['value']) }}</p>
                @if($totalRows > 0)
                <div class="mt-2 h-1 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700
                        {{ $card['color'] === 'blue'   ? 'bg-blue-500'   : '' }}
                        {{ $card['color'] === 'green'  ? 'bg-green-500'  : '' }}
                        {{ $card['color'] === 'yellow' ? 'bg-yellow-500' : '' }}
                        {{ $card['color'] === 'red'    ? 'bg-red-500'    : '' }}"
                        style="width: {{ round($card['value'] / $totalRows * 100) }}%">
                    </div>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    {{ round($card['value'] / $totalRows * 100) }}%
                </p>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Info duplikat --}}
        @if($duplikatRows > 0)
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 text-sm">
            <x-heroicon-o-information-circle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
            <p class="text-red-700 dark:text-red-300">
                <strong>{{ number_format($duplikatRows) }} data duplikat</strong> tidak akan diimpor.
                Data Review ({{ number_format($reviewRows) }}) tetap diimpor dengan status Review untuk ditinjau manual.
            </p>
        </div>
        @endif

        {{-- Preview Table --}}
        <x-filament::section>
            <x-slot name="heading">Preview Data</x-slot>
            <x-slot name="description">
                Halaman {{ $currentPage }} dari {{ max(1, ceil($totalRows / $perPage)) }}
                &nbsp;·&nbsp; Menampilkan {{ count($this->previewData) }} dari {{ number_format($totalRows) }} baris
            </x-slot>

            <div class="overflow-x-auto -mx-6">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Organisasi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Judul Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Peserta</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->previewData as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors
                            {{ $row['status_data'] === 'Duplikat' ? 'opacity-40' : '' }}">
                            <td class="px-6 py-3">
                                <p class="font-medium text-gray-900 dark:text-white truncate max-w-[180px]" title="{{ $row['nama_organisasi'] }}">
                                    {{ $row['nama_organisasi'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $row['bidang'] }}</p>
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300 truncate max-w-[200px]" title="{{ $row['judul_kegiatan'] }}">
                                {{ $row['judul_kegiatan'] }}
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $row['tanggal'] }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $row['lokasi'] }} · Lt.{{ $row['lantai'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300 text-right">
                                {{ number_format($row['jumlah_peserta']) }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $row['status_data'] === 'Valid'    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $row['status_data'] === 'Review'   ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    {{ $row['status_data'] === 'Duplikat' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                    {{ $row['status_data'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                Tidak ada data untuk ditampilkan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($totalRows > $perPage)
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ number_format(($currentPage - 1) * $perPage + 1) }}–{{ number_format(min($currentPage * $perPage, $totalRows)) }}
                    dari {{ number_format($totalRows) }} baris
                </p>
                <div class="flex items-center gap-2">
                    <x-filament::button
                        wire:click="prevPage"
                        :disabled="$currentPage === 1"
                        color="gray"
                        size="sm"
                        icon="heroicon-o-chevron-left"
                    >
                        Sebelumnya
                    </x-filament::button>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 px-2">
                        {{ $currentPage }} / {{ max(1, ceil($totalRows / $perPage)) }}
                    </span>
                    <x-filament::button
                        wire:click="nextPage"
                        :disabled="$currentPage * $perPage >= $totalRows"
                        color="gray"
                        size="sm"
                        icon="heroicon-o-chevron-right"
                        icon-position="after"
                    >
                        Berikutnya
                    </x-filament::button>
                </div>
            </div>
            @endif
        </x-filament::section>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between">
            <x-filament::button
                wire:click="resetImport"
                color="gray"
                icon="heroicon-o-arrow-left"
            >
                Ulangi Upload
            </x-filament::button>

            <x-filament::button
                wire:click="confirmImport"
                wire:loading.attr="disabled"
                color="primary"
                icon="heroicon-o-arrow-down-tray"
                icon-position="after"
                size="lg"
            >
                <span wire:loading.remove wire:target="confirmImport">
                    Import {{ number_format($validRows + $reviewRows) }} Data
                </span>
                <span wire:loading wire:target="confirmImport" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Menyimpan data...
                </span>
            </x-filament::button>
        </div>
    </div>
    @endif

    {{-- ─── STEP 4: Saving (loading state) ────────────────────────────────── --}}
    @if($isProcessing && $currentStep === 3)
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-10 max-w-sm w-full mx-4 text-center">
            <div class="relative w-16 h-16 mx-auto mb-6">
                <div class="absolute inset-0 rounded-full border-4 border-gray-100 dark:border-gray-700"></div>
                <div class="absolute inset-0 rounded-full border-4 border-primary-600 border-t-transparent animate-spin"></div>
            </div>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">Menyimpan Data</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $processMessage ?: 'Mohon jangan tutup halaman ini...' }}</p>
        </div>
    </div>
    @endif

    {{-- ─── STEP 4: Done ────────────────────────────────────────────────────── --}}
    @if($currentStep === 4)
    <x-filament::section>
        <div class="py-10 flex flex-col items-center gap-6 text-center">
            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                <x-heroicon-o-check-circle class="w-10 h-10 text-green-600 dark:text-green-400" />
            </div>

            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Import Berhasil!</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Data telah berhasil disimpan ke database</p>
            </div>

            {{-- Result Summary --}}
            <div class="grid grid-cols-3 gap-4 w-full max-w-md">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
                    <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ number_format($validRows) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">Valid</p>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4">
                    <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ number_format($reviewRows) }}</p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Review</p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4">
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($duplikatRows) }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">Dilewati</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <x-filament::button
                    tag="a"
                    :href="route('filament.admin.resources.events.index')"
                    color="primary"
                    icon="heroicon-o-table-cells"
                >
                    Lihat Data Event
                </x-filament::button>
                <x-filament::button
                    wire:click="resetImport"
                    color="gray"
                    icon="heroicon-o-arrow-up-tray"
                >
                    Import File Lain
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
    @endif

</div>
</x-filament-panels::page>
