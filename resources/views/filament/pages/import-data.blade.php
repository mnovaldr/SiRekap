<x-filament-panels::page>
<div class="import-wrap space-y-8 pb-10">

    <style>
    /* Ã¢â€â‚¬Ã¢â€â‚¬ Import Design System Ã¢â€â‚¬Ã¢â€â‚¬ */
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

    /* Ã¢â€â‚¬Ã¢â€â‚¬ Step 2: Stat Cards New Style Ã¢â€â‚¬Ã¢â€â‚¬ */
    .stat-card-v2 {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s ease;
        min-height: 140px;
    }
    .dark .stat-card-v2 { background: #1f2937; border-color: #374151; }
    .stat-card-v2:hover { box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1); }
    .stat-card-v2 .sv2-label {
        font-size: 0.65rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.12em;
        color: #6b7280;
    }
    .stat-card-v2 .sv2-value {
        font-size: 2.5rem; font-weight: 800;
        line-height: 1; letter-spacing: -0.04em;
        color: #111827;
        margin-top: 0.5rem;
    }
    .dark .stat-card-v2 .sv2-value { color: #f9fafb; }
    .stat-card-v2.success { border-color: #a7f3d0; }
    .stat-card-v2.success .sv2-label { color: #059669; }
    .stat-card-v2.success .sv2-value { color: #059669; }
    .stat-card-v2.danger  { border-color: #fecaca; }
    .stat-card-v2.danger  .sv2-label { color: #dc2626; }
    .stat-card-v2.danger  .sv2-value { color: #dc2626; }
    .sv2-mono { font-size: 0.6rem; color: #9ca3af; font-family: monospace; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 0.4rem; }
    .sv2-bar { height: 4px; border-radius: 2px; background: #10b981; margin-top: 0.75rem; width: 75%; }

    /* Alert Card (Perlu Review) */
    .alert-card {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 1.5rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .dark .alert-card { background: rgba(154,52,18,0.15); border-color: rgba(154,52,18,0.4); }
    .alert-card-title { font-size: 0.875rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .dark .alert-card-title { color: #f9fafb; }
    .alert-card-icon { width: 24px; height: 24px; background: #f97316; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.875rem; flex-shrink: 0; }
    .alert-card-body { font-size: 0.75rem; color: #374151; line-height: 1.6; }
    .dark .alert-card-body { color: #d1d5db; }
    .alert-progress-bar { margin-top: 1rem; }
    .alert-progress-label { display: flex; justify-content: space-between; font-size: 0.7rem; font-weight: 700; color: #374151; margin-bottom: 4px; }
    .dark .alert-progress-label { color: #d1d5db; }
    .alert-progress-track { height: 6px; background: #fed7aa; border-radius: 3px; overflow: hidden; }
    .dark .alert-progress-track { background: rgba(154,52,18,0.3); }
    .alert-progress-fill { height: 100%; background: #c2410c; border-radius: 3px; }

    /* Column Mapping List */
    .col-map-list { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.25rem; }
    .col-map-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.875rem 1rem;
        border-radius: 0.875rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        transition: all 0.2s ease;
    }
    .dark .col-map-row { background: #1f2937; border-color: #374151; }
    .col-map-row.missing { border-color: #fecaca; background: #fff5f5; }
    .dark .col-map-row.missing { background: rgba(127,29,29,0.1); border-color: rgba(127,29,29,0.3); }
    .col-map-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #eef2ff; color: #4338ca;
        flex-shrink: 0;
    }
    .col-map-row.missing .col-map-icon { background: #fee2e2; color: #dc2626; }
    .col-map-info { display: flex; flex-direction: column; gap: 2px; margin-left: 12px; flex: 1; }
    .col-map-name { font-size: 0.875rem; font-weight: 700; color: #111827; }
    .dark .col-map-name { color: #f9fafb; }
    .col-map-sub { font-size: 0.7rem; color: #6b7280; }
    .col-map-right { display: flex; align-items: center; gap: 10px; }
    .col-map-mapped { font-size: 0.7rem; font-style: italic; color: #9ca3af; font-family: monospace; }
    .badge-sesuai { background: #d1fae5; color: #065f46; padding: 0.2rem 0.65rem; border-radius: 99px; font-size: 0.65rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .badge-missing { background: #fee2e2; color: #991b1b; padding: 0.2rem 0.65rem; border-radius: 99px; font-size: 0.65rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .btn-manual { background: #fff; border: 1px solid #fca5a5; color: #dc2626; font-size: 0.7rem; font-weight: 600; padding: 0.3rem 0.75rem; border-radius: 0.5rem; cursor: default; }
    .dark .btn-manual { background: #1f2937; border-color: rgba(220,38,38,0.5); }
    
    /* Step 2 action panel */
    .action-panel { display: flex; flex-direction: column; gap: 0.75rem; }
    .btn-primary-xl {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 1rem 1.5rem;
        background: #4338ca; color: #fff; font-weight: 700; font-size: 1rem;
        border-radius: 1rem; border: none; cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-primary-xl:hover { background: #3730a3; }
    .btn-outline-xl {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 1rem 1.5rem;
        background: #fff; color: #374151; font-weight: 700; font-size: 0.9rem;
        border-radius: 1rem; border: 2px solid #e5e7eb; cursor: pointer;
        transition: all 0.2s ease;
    }
    .dark .btn-outline-xl { background: #1f2937; color: #d1d5db; border-color: #374151; }
    .btn-outline-xl:hover { border-color: #6b7280; }
    .hint-box {
        border: 1px dashed #d1d5db; border-radius: 1rem; padding: 1rem;
        display: flex; gap: 10px; font-size: 0.75rem; color: #6b7280;
        background: #f9fafb;
    }
    .dark .hint-box { background: rgba(31,41,55,0.4); border-color: #374151; color: #9ca3af; }

    /* Step 3: Debug Info Bar */
    .debug-bar {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 1.25rem;
        padding: 1rem 1.25rem;
    }
    .dark .debug-bar { background: #1f2937; border-color: #374151; }
    .debug-bar-title { display: flex; align-items: center; gap: 6px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; margin-bottom: 0.875rem; }
    .debug-cells { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; }
    @media (max-width: 640px) { .debug-cells { grid-template-columns: repeat(2, 1fr); } }
    .debug-cell {
        background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.75rem;
        padding: 0.75rem 1rem;
    }
    .dark .debug-cell { background: #111827; border-color: #374151; }
    .debug-cell-label { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; margin-bottom: 0.35rem; }
    .debug-cell-value { font-size: 1rem; font-weight: 700; color: #111827; }
    .dark .debug-cell-value { color: #f9fafb; }
    .debug-cell-value.active { color: #10b981; font-size: 0.8rem; display: flex; align-items: center; gap: 5px; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; }

    /* Step 3: Table preview header */
    .preview-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: space-between;
        background: #fff;
    }
    .dark .preview-header { background: #1f2937; border-color: #374151; }
    .preview-title { font-size: 1.125rem; font-weight: 800; color: #111827; letter-spacing: -0.02em; }
    .dark .preview-title { color: #f9fafb; }
    .preview-sub { font-size: 0.75rem; color: #6b7280; margin-top: 2px; }
    .pager { display: flex; align-items: center; gap: 12px; }
    .pager-label { font-size: 0.875rem; font-weight: 600; color: #374151; }
    .dark .pager-label { color: #d1d5db; }
    .pager-btn {
        width: 34px; height: 34px; border-radius: 8px;
        border: 1px solid #e5e7eb; background: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.15s ease; color: #374151;
    }
    .dark .pager-btn { background: #374151; border-color: #4b5563; color: #d1d5db; }
    .pager-btn:hover { background: #f3f4f6; }
    .dark .pager-btn:hover { background: #4b5563; }
    .pager-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    /* Step 3: Status pill badges */
    .pill-valid   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 3px 12px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
    .pill-review  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 3px 12px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
    .pill-duplikat { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 3px 12px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
    .pill-default  { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; padding: 3px 12px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }

    /* Step 3: Bottom info cards */
    .info-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 1.5rem;
        padding: 1.5rem; display: flex; gap: 1rem; align-items: flex-start;
    }
    .dark .info-card { background: #1f2937; border-color: #374151; }
    .info-card-icon {
        width: 48px; height: 48px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .info-card-title { font-size: 0.875rem; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .dark .info-card-title { color: #f9fafb; }
    .info-card-body { font-size: 0.75rem; color: #6b7280; line-height: 1.6; }
    
    /* Matched/Missing badges in header */
    .badge-matched { background: #eef2ff; color: #4338ca; padding: 3px 10px; border-radius: 99px; font-size: 0.65rem; font-weight: 700; }
    .badge-cols-missing { background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 99px; font-size: 0.65rem; font-weight: 700; }
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
    <div style="display:flex;flex-direction:column;gap:1.5rem;" class="animate-fade-in">

        {{-- Stats Row + Alert --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:1.25rem;align-items:stretch;">

            {{-- Total Baris --}}
            <div class="stat-card-v2">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div class="sv2-label">Total Baris</div>
                    <x-heroicon-o-bars-4 style="width:18px;height:18px;color:#9ca3af;" />
                </div>
                <div>
                    <div class="sv2-value">{{ number_format($totalRows) }}</div>
                    <div class="sv2-mono">Entries Found</div>
                </div>
            </div>

            {{-- Siap Import --}}
            <div class="stat-card-v2 success">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div class="sv2-label">Siap Import</div>
                    <div style="width:22px;height:22px;border-radius:50%;border:2px solid #10b981;display:flex;align-items:center;justify-content:center;">
                        <x-heroicon-s-check style="width:14px;height:14px;color:#10b981;" />
                    </div>
                </div>
                <div>
                    <div class="sv2-value">{{ number_format($validRows + $reviewRows) }}</div>
                    <div class="sv2-bar"></div>
                </div>
            </div>

            {{-- Duplikat --}}
            <div class="stat-card-v2 danger">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div class="sv2-label">Duplikat</div>
                    <x-heroicon-o-exclamation-triangle style="width:18px;height:18px;color:#dc2626;" />
                </div>
                <div>
                    <div class="sv2-value">{{ number_format($duplikatRows) }}</div>
                    <div class="sv2-mono" style="color:#ef4444;">Requires Action</div>
                </div>
            </div>

            {{-- Perlu Review Alert --}}
            <div class="alert-card" style="min-width:240px;">
                <div>
                    <div class="alert-card-title">
                        <div class="alert-card-icon">!</div>
                        Perlu Review
                    </div>
                    <div class="alert-card-body">
                        Ditemukan <strong>{{ number_format($duplikatRows) }}</strong> data duplikat dan beberapa data perlu ditinjau. Pastikan semua kolom wajib terisi sebelum melanjutkan.
                    </div>
                </div>
                <div class="alert-progress-bar">
                    <div class="alert-progress-label">
                        <span>Validitas Data</span>
                        <span>{{ $totalRows > 0 ? round((($validRows + $reviewRows) / $totalRows) * 100) : 0 }}%</span>
                    </div>
                    <div class="alert-progress-track">
                        <div class="alert-progress-fill" style="width:{{ $totalRows > 0 ? (($validRows + $reviewRows) / $totalRows) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Column Mapping + Action --}}
        <div style="display:grid;grid-template-columns:1fr auto;gap:1.25rem;align-items:start;">

            {{-- Column Discovery Panel --}}
            <div class="card-premium" style="padding:1.5rem;">
                @php
                    $required = [
                        'Nama Organisasi'    => ['nama organisasi', 'organisasi', 'komunitas'],
                        'Bidang'             => ['bidang'],
                        'Jenis Kegiatan'     => ['jenis kegiatan', 'kegiatan'],
                        'Judul Kegiatan'     => ['judul kegiatan', 'judul kegiatan/event', 'judul', 'event'],
                        'Tanggal Pelaksanaan'=> ['tanggal', 'tanggal kegiatan', 'tgl'],
                        'Lokasi'             => ['lokasi', 'ruangan'],
                        'Jumlah Peserta'     => ['jumlah peserta', 'peserta'],
                    ];
                    $foundLower   = array_map('strtolower', $foundColumns);
                    $matchedCount = 0;
                    foreach ($required as $keys) {
                        foreach ($keys as $key) {
                            foreach ($foundLower as $fc) {
                                if (str_contains($fc, $key) || str_contains($key, $fc)) { $matchedCount++; break 2; }
                            }
                        }
                    }
                    $missingCount = count($required) - $matchedCount;
                @endphp

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                    <div>
                        <h3 style="font-size:1rem;font-weight:800;color:#111827;margin:0;">Struktur Kolom Terdeteksi</h3>
                        <p style="font-size:0.75rem;color:#6b7280;margin-top:3px;">Sistem mendeteksi mapping kolom berikut dari file Anda</p>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <span class="badge-matched">{{ $matchedCount }} Matched</span>
                        @if($missingCount > 0)
                        <span class="badge-cols-missing">{{ $missingCount }} Missing</span>
                        @endif
                    </div>
                </div>

                <div class="col-map-list">
                    @foreach($required as $label => $keys)
                        @php
                            $isMatched  = false;
                            $matchedCol = '';
                            $icons = [
                                'Nama Organisasi'     => 'heroicon-o-building-office',
                                'Bidang'              => 'heroicon-o-squares-2x2',
                                'Tanggal Pelaksanaan' => 'heroicon-o-calendar-days',
                                'Jumlah Peserta'      => 'heroicon-o-users',
                                'Lokasi'              => 'heroicon-o-map-pin',
                                'Jenis Kegiatan'      => 'heroicon-o-tag',
                                'Judul Kegiatan'      => 'heroicon-o-document-text',
                            ];
                            $iconComp = $icons[$label] ?? 'heroicon-o-document-text';
                            foreach ($keys as $key) {
                                foreach ($foundLower as $idx => $fc) {
                                    if (str_contains($fc, $key) || str_contains($key, $fc)) {
                                        $isMatched = true;
                                        $matchedCol = $foundColumns[$idx];
                                        break 2;
                                    }
                                }
                            }
                            $isOptional = in_array($label, ['Lokasi','Jumlah Peserta']);
                        @endphp
                        <div class="col-map-row {{ !$isMatched ? 'missing' : '' }}">
                            <div style="display:flex;align-items:center;flex:1;">
                                <div class="col-map-icon">
                                    <x-dynamic-component :component="$iconComp" style="width:18px;height:18px;" />
                                </div>
                                <div class="col-map-info">
                                    <div class="col-map-name">{{ $label }}</div>
                                    <div class="col-map-sub">{{ $isOptional ? 'Opsional' : 'Wajib diisi' }}</div>
                                </div>
                            </div>
                            <div class="col-map-right">
                                @if($isMatched)
                                    <span class="col-map-mapped">Mapped to "{{ $matchedCol }}"</span>
                                    <span class="badge-sesuai">
                                        <x-heroicon-s-check-circle style="width:12px;height:12px;" /> Sesuai
                                    </span>
                                @else
                                    <span class="btn-manual">Pilih Kolom Manually</span>
                                    <span class="badge-missing">
                                        <x-heroicon-s-x-circle style="width:12px;height:12px;" /> Missing
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Action Panel --}}
            <div class="action-panel" style="min-width:260px;">
                <button wire:click="$set('currentStep', 3)" wire:loading.attr="disabled" class="btn-primary-xl">
                    Lanjutkan ke Pratinjau
                    <x-heroicon-o-arrow-right style="width:18px;height:18px;" />
                </button>
                <button wire:click="resetImport" wire:loading.attr="disabled" class="btn-outline-xl">
                    <x-heroicon-o-cloud-arrow-up style="width:18px;height:18px;" />
                    Unggah Ulang File
                </button>
                <div class="hint-box">
                    <x-heroicon-o-information-circle style="width:18px;height:18px;flex-shrink:0;" />
                    <span>Format: <strong>.xlsx, .xls, .csv</strong> maks. 10MB.</span>
                </div>
            </div>

        </div>
    </div>
    @endif


    {{-- STEP 3: PREVIEW TABLE --}}
    @if($currentStep === 3)
    <div style="display:flex;flex-direction:column;gap:1.5rem;" class="animate-fade-in">
        {{-- Debug Info Bar --}}
        <div class="debug-bar">
            <div class="debug-bar-title">
                <x-heroicon-o-information-circle style="width:14px;height:14px;" />
                Debug Info
            </div>
            <div class="debug-cells">
                <div class="debug-cell">
                    <div class="debug-cell-label">Total Rows</div>
                    <div class="debug-cell-value">{{ number_format($totalRows) }}</div>
                </div>
                <div class="debug-cell">
                    <div class="debug-cell-label">Session Status</div>
                    <div class="debug-cell-value active">
                        <span class="status-dot"></span> Active
                    </div>
                </div>
                <div class="debug-cell">
                    <div class="debug-cell-label">Preview Count</div>
                    <div class="debug-cell-value">{{ count($this->previewData) }} Items</div>
                </div>
                <div class="debug-cell">
                    <div class="debug-cell-label">Data Integrity</div>
                    <div class="debug-cell-value">Passed</div>
                </div>
            </div>
        </div>

        <div class="card-premium overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Pratinjau Data</h3>
                    <p class="text-sm text-gray-500 mt-1">Data yang terstandardisasi sebelum disimpan</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-300">
                        {{ $currentPage }} <span class="text-gray-400 font-normal">/ {{ ceil($totalRows / $perPage) ?: 1 }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="prevPage" @disabled($currentPage === 1) class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            <x-heroicon-o-chevron-left class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                        </button>
                        <button wire:click="nextPage" @disabled($currentPage * $perPage >= $totalRows) class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="perf-table w-full">
                    <thead>
                        <tr>
                            <th class="py-4">Organisasi</th>
                            <th class="py-4">Kegiatan</th>
                            <th class="py-4">Waktu & Lokasi</th>
                            <th class="py-4 text-center">Peserta</th>
                            <th class="py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->previewData as $row)
                        <tr class="group {{ ($row['status_data'] ?? '') === 'Duplikat' ? 'opacity-70 bg-gray-50/50 dark:bg-gray-900/30' : '' }}">
                            <td class="align-top py-4">
                                <div class="font-bold text-sm text-gray-900 dark:text-white truncate max-w-[220px]" title="{{ $row['nama_organisasi'] ?? '-' }}">
                                    {{ Str::limit($row['nama_organisasi'] ?? '-', 35) }}
                                </div>
                                <div class="text-sm text-gray-500 mt-1">{{ $row['bidang'] ?? '-' }}</div>
                            </td>
                            <td class="align-top py-4">
                                <div class="font-medium text-sm text-gray-800 dark:text-gray-200 line-clamp-2 max-w-[260px]" title="{{ $row['judul_kegiatan'] ?? '-' }}">
                                    {{ Str::limit($row['judul_kegiatan'] ?? '-', 45) }}
                                </div>
                                <div class="text-sm text-indigo-600 dark:text-indigo-400 mt-1">{{ $row['jenis_kegiatan'] ?? '-' }}</div>
                            </td>
                            <td class="align-top py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-calendar class="w-4 h-4" />
                                    {{ isset($row['tanggal']) ? \Carbon\Carbon::parse($row['tanggal'])->format('d M Y') : '-' }}
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-500 mt-1.5">
                                    <x-heroicon-o-map-pin class="w-4 h-4" />
                                    {{ $row['lokasi'] ?? '-' }} (Lt.{{ $row['lantai'] ?? '-' }})
                                </div>
                            </td>
                            <td class="text-center align-middle py-4">
                                <span class="font-bold text-base text-gray-900 dark:text-white">{{ number_format($row['jumlah_peserta'] ?? 0) }}</span>
                            </td>
                            <td class="text-center align-middle py-4">
                                @php
                                    $status = $row['status_data'] ?? 'Unknown';
                                    $badgeStyle = match($status) {
                                        'Valid' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
                                        'Review' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
                                        'Duplikat' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
                                        default => 'bg-gray-100 text-gray-600 border-gray-200'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $badgeStyle }}">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3">
                                    <x-heroicon-o-inbox class="w-12 h-12 text-gray-300" />
                                    <p class="font-medium text-gray-500">Tidak ada data untuk ditampilkan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs font-medium text-gray-500">
                    Menampilkan {{ number_format(($currentPage - 1) * $perPage + 1) }} - {{ number_format(min($currentPage * $perPage, $totalRows)) }} 
                    dari {{ number_format($totalRows) }} data
                </p>
                <div class="flex items-center gap-3">
                    <x-filament::button wire:click="resetImport" wire:loading.attr="disabled" color="gray" variant="outline" class="rounded-xl px-6">
                        Batalkan
                    </x-filament::button>
                    <x-filament::button wire:click="confirmImport" wire:loading.attr="disabled" class="bg-indigo-700 hover:bg-indigo-800 rounded-xl px-8 shadow-md">
                        <span wire:loading.remove wire:target="confirmImport">Import {{ number_format($validRows + $reviewRows) }} Data</span>
                        <span wire:loading wire:target="confirmImport">Memproses...</span>
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Info Cards (Bottom) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="card-premium p-6 flex gap-4 bg-white dark:bg-gray-900">
                <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                    <x-heroicon-o-eye class="w-6 h-6 text-indigo-500" />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Data Verification</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        We have identified {{ number_format($validRows + $reviewRows) }} records ready for import. Please review any flagged "Review" or "Duplikat" entries to ensure data accuracy before final confirmation.
                    </p>
                </div>
            </div>
            <div class="card-premium p-6 flex gap-4 bg-white dark:bg-gray-900">
                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                    <x-heroicon-o-shield-check class="w-6 h-6 text-emerald-500" />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Secure Processing</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Your data is processed through our secure encryption layer. Once confirmed, the information will be synchronized across all MCC ecosystem modules immediately.
                    </p>
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
