<x-filament-panels::page>
<div class="import-wrap space-y-8 pb-10">

    <style>
    /* ── Import Design System ── */
    .import-wrap { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    
    /* Cards */
    .card-premium {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1.5rem;
        transition: all 0.3s ease;
    }
    .dark .card-premium { background: #1f2937; border-color: #374151; }
    .card-premium:hover { box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1); }

    /* Stat Cards */
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    .dark .stat-card { background: #1f2937; border-color: #374151; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px -12px rgba(0,0,0,0.12); border-color: #4f46e5; }
    
    .stat-card .icon-wrap {
        width: 3rem; height: 3rem;
        border-radius: 1rem;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1.25rem;
    }
    .stat-card .stat-value {
        font-size: 2rem; font-weight: 800;
        line-height: 1; letter-spacing: -0.04em;
        color: #111827;
    }
    .dark .stat-card .stat-value { color: #f9fafb; }
    .stat-card .stat-label { 
        font-size: 0.65rem; font-weight: 700; 
        color: #6b7280; text-transform: uppercase; 
        letter-spacing: 0.1em; 
        margin-top: 0.5rem;
    }

    /* Step Indicator */
    .steps-container {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        width: 100%;
        max-width: 56rem;
        margin: 0 auto;
    }
    .step-item {
        position: relative;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .step-line {
        position: absolute;
        top: 1.25rem;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #f3f4f6;
        z-index: 0;
    }
    .dark .step-line { background: #374151; }
    .step-line-active {
        height: 100%;
        background: #4f46e5;
        transition: width 0.5s ease;
        width: 0%;
    }
    
    .step-circle {
        position: relative;
        z-index: 10;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 2px solid #e5e7eb;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 700;
        color: #9ca3af;
    }
    .dark .step-circle { background: #111827; border-color: #374151; }
    .step-circle.active { 
        background: #4f46e5; 
        border-color: #4f46e5; 
        color: #fff; 
        box-shadow: 0 0 0 6px rgba(79, 70, 229, 0.15), 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        transform: scale(1.1);
    }
    .step-circle.completed { 
        background: #10b981; 
        border-color: #10b981; 
        color: #fff; 
        box-shadow: 0 4px 10px -2px rgba(16, 185, 129, 0.3);
    }
    .step-label {
        margin-top: 1rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: color 0.3s ease;
    }
    .step-label-active { color: #4f46e6; font-weight: 800; }
    .dark .step-label-active { color: #818cf8; }
    .step-label-inactive { color: #9ca3af; }

    /* Tables */
    .perf-table { width: 100%; border-collapse: collapse; }
    .perf-table th {
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: #6b7280;
        padding: 0.75rem 1rem; text-align: left;
        background: #f9fafb; border-bottom: 1px solid #e5e7eb;
    }
    .dark .perf-table th { background: #374151; color: #9ca3af; border-color: #4b5563; }
    .perf-table td { 
        padding: 0.875rem 1rem; 
        border-bottom: 1px solid #f3f4f6; 
        font-size: 0.875rem; 
        color: #374151;
    }
    .dark .perf-table td { color: #d1d5db; border-color: #374151; }
    .perf-table tr:hover td { background: #f9fafb; }
    .dark .perf-table tr:hover td { background: #1f2937; }

    /* Badges */
    .badge-premium {
        display: inline-flex; 
        align-items: center; 
        padding: 0.25rem 0.75rem; 
        border-radius: 0.625rem;
        font-size: 0.7rem; 
        font-weight: 700; 
        letter-spacing: 0.02em;
    }
    .badge-primary { background: #eef2ff; color: #4338ca; }
    .dark .badge-primary { background: #312e81; color: #a5b4fc; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .dark .badge-success { background: #064e3b; color: #6ee7b7; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .dark .badge-warning { background: #78350f; color: #fcd34d; }
    .badge-danger  { background: #fee2e2; color: #991b1b; }
    .dark .badge-danger  { background: #7f1d1d; color: #fca5a5; }
    .badge-secondary { background: #f3f4f6; color: #4b5563; }
    .dark .badge-secondary { background: #374151; color: #9ca3af; }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    /* Upload Area */
    .upload-zone {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fcfcfc;
    }
    .dark .upload-zone { background: #1a222e; }
    .upload-zone:hover {
        border-color: #4f46e5;
        background: #fff;
    }
    .upload-zone-dropping {
        border-color: #4f46e5 !important;
        background: rgba(79, 70, 229, 0.08) !important;
        transform: scale(1.01);
        box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.1);
    }
    </style>

    {{-- HEADER & STEPS --}}
    <div class="card-premium p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-600 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none">
                    <x-heroicon-o-arrow-up-tray class="w-7 h-7 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Import Data Event</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Integrasi data Excel ke dalam ekosistem MCC</p>
                </div>
            </div>
            
            <div>
                <x-filament::button wire:click="resetImport" wire:loading.attr="disabled" color="gray" variant="ghost" icon="heroicon-o-arrow-path" size="sm" class="rounded-lg">
                    Reset Proses
                </x-filament::button>
            </div>
        </div>

        {{-- Step Indicator --}}
        <div class="steps-container">
            @php
                $steps = [
                    1 => ['label' => 'Unggah', 'icon' => 'heroicon-s-cloud-arrow-up'],
                    2 => ['label' => 'Validasi', 'icon' => 'heroicon-s-magnifying-glass'],
                    3 => ['label' => 'Konfirmasi', 'icon' => 'heroicon-s-clipboard-document-check'],
                    4 => ['label' => 'Selesai', 'icon' => 'heroicon-s-check-circle'],
                ];
            @endphp

            @foreach($steps as $s => $step)
                <div class="step-item">
                    @if(!$loop->last)
                        <div class="step-line">
                            <div class="step-line-active" style="width: {{ $currentStep > $s ? '100%' : '0%' }}"></div>
                        </div>
                    @endif
                    
                    <div class="step-circle 
                        {{ $currentStep === $s ? 'active' : '' }} 
                        {{ $currentStep > $s ? 'completed' : '' }}">
                        @if($currentStep > $s)
                            <x-heroicon-m-check class="w-5 h-5" />
                        @elseif($currentStep === $s)
                            <x-dynamic-component :component="$step['icon']" class="w-5 h-5" />
                        @else
                            <x-dynamic-component :component="$step['icon']" class="w-5 h-5" />
                        @endif
                    </div>
                    <span class="step-label {{ $currentStep === $s ? 'step-label-active' : 'step-label-inactive' }}">
                        {{ $step['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- STEP 1: UPLOAD AREA --}}
    @if($currentStep === 1)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card-premium p-6 h-full">
                <label for="file-upload" 
                    x-data="{ isDragging: false }"
                    x-on:dragover.prevent="isDragging = true"
                    x-on:dragleave.prevent="isDragging = false"
                    x-on:drop.prevent="
                        isDragging = false;
                        if ($event.dataTransfer.files.length) {
                            $wire.upload('file', $event.dataTransfer.files[0])
                        }
                    "
                    x-bind:class="{ 'upload-zone-dropping': isDragging }"
                    class="upload-zone relative flex flex-col items-center justify-center w-full h-96 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl cursor-pointer transition-all duration-300 hover:border-indigo-400 dark:hover:border-indigo-500">
                    
                    <input type="file" id="file-upload" class="absolute inset-0 opacity-0 cursor-pointer" wire:model="file" accept=".xlsx,.xls">

                    {{-- Loading State --}}
                    <div wire:loading wire:target="file" class="flex flex-col items-center gap-4 z-10">
                        <div class="relative">
                            <div class="w-16 h-16 border-4 border-indigo-200 dark:border-indigo-900/30 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                            <x-heroicon-o-arrow-up-tray class="absolute inset-0 m-auto w-6 h-6 text-indigo-600" />
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Sedang Memproses...</p>
                            <p class="text-xs text-gray-500 mt-1">Menganalisis struktur file</p>
                        </div>
                    </div>

                    {{-- Default State --}}
                    <div wire:loading.remove wire:target="file" class="flex flex-col items-center text-center px-6 z-10">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-500 group-hover:scale-110 group-hover:border-indigo-200 group-hover:shadow-indigo-100 dark:group-hover:shadow-none"
                             x-bind:class="{ 'scale-110 border-indigo-300 shadow-indigo-200': isDragging }">
                            <x-heroicon-o-document-plus class="w-10 h-10 text-gray-400 dark:text-gray-500 transition-colors duration-500 group-hover:text-indigo-600"
                                                        x-bind:class="{ 'text-indigo-600': isDragging }" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight" 
                            x-text="isDragging ? 'Lepaskan File Disini' : 'Unggah File Excel'"></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-xs leading-relaxed" x-show="!isDragging">
                            Seret dan lepas file Anda di sini, atau klik untuk memilih file dari komputer
                        </p>
                        <p class="text-xs text-gray-400 mt-3 font-medium" x-show="!isDragging">
                            Format: .xlsx, .xls (Maks. 10MB)
                        </p>
                        <div class="flex gap-2 mt-8" x-show="!isDragging">
                            <span class="badge-premium badge-primary px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">.xlsx</span>
                            <span class="badge-premium badge-primary px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">.xls</span>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Guide Card --}}
            <div class="card-premium p-6 bg-gradient-to-br from-indigo-600 to-indigo-700 text-white border-0 shadow-xl">
                <h4 class="text-base font-bold mb-4 flex items-center gap-2">
                    <x-heroicon-s-sparkles class="w-5 h-5 text-indigo-200" />
                    Panduan Cepat
                </h4>
                <ul class="space-y-3">
                    <li class="flex gap-3 text-sm">
                        <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                        <span class="text-indigo-50">Gunakan template resmi MCC</span>
                    </li>
                    <li class="flex gap-3 text-sm">
                        <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold shrink-0">2</span>
                        <span class="text-indigo-50">Sheet per bulan akan terbaca otomatis</span>
                    </li>
                    <li class="flex gap-3 text-sm">
                        <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                        <span class="text-indigo-50">Header kolom di baris pertama</span>
                    </li>
                </ul>
                
                <div class="mt-6 pt-4 border-t border-white/20">
                    <x-filament::button href="#" tag="a" color="white" icon="heroicon-o-arrow-down-tray" class="w-full rounded-xl">
                        <span class="text-indigo-600 font-semibold">Unduh Template</span>
                    </x-filament::button>
                </div>
            </div>

            {{-- Required Columns --}}
            <div class="card-premium p-6">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Kolom Wajib</h4>
                <div class="space-y-2">
                    @foreach(['Nama Organisasi', 'Bidang', 'Jenis Kegiatan', 'Judul Kegiatan', 'Tanggal', 'Lokasi', 'Lantai', 'Jumlah Peserta'] as $col)
                        <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800/50 text-sm">
                            <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                                <x-heroicon-m-check class="w-3 h-3 text-white" />
                            </div>
                            <span class="text-gray-700 dark:text-gray-300">{{ $col }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 2: VALIDATION --}}
    @if($currentStep === 2)
    <div class="space-y-6 animate-fade-in">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="stat-card">
                <div class="icon-wrap bg-indigo-50 dark:bg-indigo-900/20">
                    <x-heroicon-o-table-cells class="w-5 h-5 text-indigo-600" />
                </div>
                <div class="stat-value">{{ number_format($totalRows) }}</div>
                <div class="stat-label">Total Baris</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap bg-emerald-50 dark:bg-emerald-900/20">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600" />
                </div>
                <div class="stat-value">{{ number_format($validRows + $reviewRows) }}</div>
                <div class="stat-label">Siap Import</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap bg-red-50 dark:bg-red-900/20">
                    <x-heroicon-o-x-circle class="w-5 h-5 text-red-600" />
                </div>
                <div class="stat-value">{{ number_format($duplikatRows) }}</div>
                <div class="stat-label">Duplikat</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Column Discovery --}}
            <div class="xl:col-span-2 card-premium overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Struktur Kolom Terdeteksi</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Sistem mendeteksi kolom berikut dari file Anda</p>
                    </div>
                    <span class="badge-premium badge-primary text-xs">{{ count($foundColumns) }} Kolom</span>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-2">
                        @foreach($foundColumns as $col)
                            @php 
                                $isImportant = in_array(strtolower($col), [
                                    'nama organisasi', 'bidang', 'jenis kegiatan', 
                                    'judul kegiatan', 'tanggal', 'jumlah peserta'
                                ]); 
                            @endphp
                            <span class="badge-premium {{ $isImportant ? 'badge-primary' : 'badge-secondary' }} py-1.5 px-3 rounded-lg text-xs">
                                {{ $col }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Mapping Status --}}
                    <div class="mt-6 p-5 rounded-xl bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/30">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-4">Pemetaan Kolom Wajib</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php
                                $required = [
                                    'Organisasi' => ['nama organisasi', 'organisasi'],
                                    'Bidang' => ['bidang'],
                                    'Jenis Kegiatan' => ['jenis kegiatan'],
                                    'Judul Kegiatan' => ['judul kegiatan', 'event'],
                                    'Tanggal' => ['tanggal'],
                                    'Jumlah Peserta' => ['jumlah peserta'],
                                ];
                                $foundLower = array_map('strtolower', $foundColumns);
                            @endphp
                            @foreach($required as $label => $keys)
                                @php $isMatched = !empty(array_intersect($keys, $foundLower)); @endphp
                                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-white/50 dark:bg-gray-800/30">
                                    <span class="text-sm font-medium {{ $isMatched ? 'text-gray-700 dark:text-gray-300' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $label }}
                                    </span>
                                    @if($isMatched)
                                        <x-heroicon-m-check-circle class="w-5 h-5 text-emerald-500" />
                                    @else
                                        <span class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                            <x-heroicon-m-x-circle class="w-4 h-4" /> Tidak ditemukan
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Panel --}}
            <div class="space-y-5">
                <div class="card-premium p-6">
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl mb-6">
                        <h4 class="text-sm font-bold text-amber-800 dark:text-amber-300 flex items-center gap-2 mb-2">
                            <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                            Perlu Review
                        </h4>
                        <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                            Terdapat <strong>{{ number_format($reviewRows) }} data</strong> yang perlu ditinjau. Data akan tetap diimport dengan status review.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 font-medium">Validitas Data</span>
                            <span class="font-bold text-indigo-600">{{ round(($validRows / max(1, $totalRows)) * 100) }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full transition-all" style="width: {{ ($validRows / max(1, $totalRows)) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="mt-8 space-y-3">
                        <x-filament::button wire:click="$set('currentStep', 3)" wire:loading.attr="disabled" class="w-full rounded-xl py-2.5 shadow-md" icon="heroicon-o-arrow-right" icon-position="after">
                            Lanjutkan ke Pratinjau
                        </x-filament::button>
                        <x-filament::button wire:click="resetImport" wire:loading.attr="disabled" color="gray" variant="outline" class="w-full rounded-xl">
                            Unggah Ulang
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 3: PREVIEW TABLE --}}
    @if($currentStep === 3)
    <div class="space-y-6 animate-fade-in">
        <div class="card-premium overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3 bg-gray-50/50 dark:bg-gray-800/30">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pratinjau Data</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Data yang terstandardisasi sebelum disimpan</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right text-sm">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $currentPage }}</span>
                        <span class="text-gray-400"> / {{ ceil($totalRows / $perPage) }}</span>
                    </div>
                    <div class="flex gap-1">
                        <x-filament::button wire:click="prevPage" @disabled($currentPage === 1) wire:loading.attr="disabled" color="gray" size="sm" icon="heroicon-o-chevron-left" class="rounded-lg p-2" />
                        <x-filament::button wire:click="nextPage" @disabled($currentPage * $perPage >= $totalRows) wire:loading.attr="disabled" color="gray" size="sm" icon="heroicon-o-chevron-right" class="rounded-lg p-2" />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="perf-table">
                    <thead>
                        <tr>
                            <th>Organisasi</th>
                            <th>Kegiatan</th>
                            <th>Waktu & Lokasi</th>
                            <th class="text-right">Peserta</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->previewData as $row)
                        <tr class="{{ $row['status_data'] === 'Duplikat' ? 'opacity-60 bg-gray-50/50 dark:bg-gray-900/30' : '' }}">
                            <td class="align-top">
                                <div class="font-semibold text-gray-900 dark:text-white truncate max-w-[200px]">{{ $row['nama_organisasi'] ?? '-' }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $row['bidang'] ?? '-' }}</div>
                            </td>
                            <td class="align-top">
                                <div class="text-gray-700 dark:text-gray-300 line-clamp-1 max-w-[220px]">{{ $row['judul_kegiatan'] ?? '-' }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ $row['jenis_kegiatan'] ?? '-' }}</div>
                            </td>
                            <td class="align-top whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                                    <x-heroicon-m-calendar class="w-3.5 h-3.5 text-indigo-400" />
                                    {{ $row['tanggal'] ?? '-' }}
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1">
                                    <x-heroicon-m-map-pin class="w-3.5 h-3.5" />
                                    {{ $row['lokasi'] ?? '-' }} (Lt.{{ $row['lantai'] ?? '-' }})
                                </div>
                            </td>
                            <td class="text-right align-top">
                                <span class="font-bold text-gray-900 dark:text-white">{{ number_format($row['jumlah_peserta'] ?? 0) }}</span>
                            </td>
                            <td class="text-center align-top">
                                <span class="badge-premium 
                                    {{ $row['status_data'] === 'Valid' ? 'badge-success' : '' }}
                                    {{ $row['status_data'] === 'Review' ? 'badge-warning' : '' }}
                                    {{ $row['status_data'] === 'Duplikat' ? 'badge-danger' : '' }}">
                                    {{ $row['status_data'] ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-500">
                                Tidak ada data untuk ditampilkan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Menampilkan {{ number_format(($currentPage - 1) * $perPage + 1) }} - {{ number_format(min($currentPage * $perPage, $totalRows)) }} 
                    dari {{ number_format($totalRows) }} data
                </p>
                <div class="flex items-center gap-3">
                    <x-filament::button wire:click="resetImport" wire:loading.attr="disabled" color="gray" variant="outline" size="sm">
                        Batalkan
                    </x-filament::button>
                    <x-filament::button wire:click="confirmImport" wire:loading.attr="disabled" size="sm" class="bg-indigo-600 hover:bg-indigo-700">
                        <span wire:loading.remove wire:target="confirmImport">Import {{ number_format($validRows + $reviewRows) }} Data</span>
                        <span wire:loading wire:target="confirmImport">Menyimpan...</span>
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 4: SUCCESS --}}
    @if($currentStep === 4)
    <div class="max-w-xl mx-auto py-8 animate-fade-in">
        <div class="card-premium p-8 text-center">
            {{-- Success Animation --}}
            <div class="mb-8">
                <div class="w-24 h-24 mx-auto bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center mb-4">
                    <x-heroicon-o-check-badge class="w-12 h-12 text-emerald-600" />
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Import Berhasil!</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-8">Data telah diintegrasikan ke dalam database SiREKAP MCC</p>

            {{-- Stats Summary --}}
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                    <div class="text-xl font-bold text-indigo-600">{{ number_format($validRows) }}</div>
                    <div class="text-xs font-medium text-gray-500 mt-1 uppercase tracking-wide">Valid</div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                    <div class="text-xl font-bold text-amber-500">{{ number_format($reviewRows) }}</div>
                    <div class="text-xs font-medium text-gray-500 mt-1 uppercase tracking-wide">Review</div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                    <div class="text-xl font-bold text-gray-400">{{ number_format($duplikatRows) }}</div>
                    <div class="text-xs font-medium text-gray-500 mt-1 uppercase tracking-wide">Skipped</div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <x-filament::button tag="a" href="{{ route('filament.admin.resources.events.index') }}" class="rounded-xl">
                    Lihat Data Event
                </x-filament::button>
                <x-filament::button wire:click="resetImport" color="gray" variant="outline" class="rounded-xl">
                    Import File Lagi
                </x-filament::button>
            </div>
        </div>
    </div>
    @endif

</div>
</x-filament-panels::page>