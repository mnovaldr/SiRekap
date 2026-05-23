<x-filament-panels::page>
<div class="space-y-6 pb-8" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">

    <style>
        /* ===== PREMIUM OVERRIDES FOR EXPORT PAGE ===== */
        .export-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .dark .export-card {
            background: #111827;
            border-color: #1f2937;
        }
        
        .export-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .export-header {
            border-color: #1f2937;
        }
        
        .export-picker-card {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #f9fafb;
        }
        .export-picker-card.active {
            border-color: #4f46e5;
            background: #eef2ff;
        }
        .dark .export-picker-card {
            border-color: #1f2937;
            background: #1f2937;
        }
        .dark .export-picker-card.active {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }

        .export-select {
            width: 100%;
            padding: 12px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #111827;
            transition: all 0.2s ease;
            outline: none;
        }
        .export-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .dark .export-select {
            background: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }
        .dark .export-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .export-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }
        .export-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #5c54f2, #4f46e5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }
        .export-btn:active:not(:disabled) {
            transform: translateY(0);
        }
        .export-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .export-table {
            width: 100%;
            border-collapse: collapse;
        }
        .export-table th {
            padding: 12px 16px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #9ca3af;
            background: #f9fafb;
            border-bottom: 1px solid #f3f4f6;
            text-align: left;
        }
        .dark .export-table th {
            background: #1f2937;
            border-color: #374151;
            color: #9ca3af;
        }
        .export-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f9fafb;
        }
        .dark .export-table td {
            border-color: #1f2937;
        }
        .export-table tr:hover td {
            background: rgba(79, 70, 229, 0.03);
        }
        .dark .export-table tr:hover td {
            background: rgba(99, 102, 241, 0.03);
        }

        @keyframes zoomIn {
            from { transform: scale(0.96); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <div class="export-card">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl">
                    <x-heroicon-o-arrow-down-tray class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        Export Data Engine
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Ekstraksi data terstruktur untuk laporan dan integrasi sistem
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         MAIN ENGINE LAYOUT WITH OVERLAY
    ══════════════════════════════════════════ --}}
    <div class="relative">
        <!-- Premium Glassmorphism Loading Overlay -->
        <div wire:loading wire:target="export" class="absolute inset-0 bg-white/70 dark:bg-slate-950/70 backdrop-blur-md z-50 flex flex-col items-center justify-center rounded-2xl transition-all duration-300" style="display: none;">
            <div class="flex flex-col items-center gap-5 text-center max-w-sm p-8 bg-white dark:bg-slate-900 shadow-2xl rounded-3xl border border-gray-100 dark:border-slate-800" style="transform: scale(0.96); animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-indigo-100 dark:border-indigo-950 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-t-indigo-600 dark:border-t-indigo-400 rounded-full animate-spin"></div>
                    <span class="text-2xl z-10">📥</span>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white tracking-tight">Menyiapkan Berkas Ekspor</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Sedang mengompilasi data ke dalam format <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold rounded text-[10px]">{{ strtoupper($selectedFormat) }}</span>. Silakan tunggu beberapa saat...
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- ══════════════════════════════════════════
                 CONFIGURATION PANEL (4/12)
            ══════════════════════════════════════════ --}}
            <div class="lg:col-span-4">
                <div class="export-card" style="position:sticky; top:24px;">
                    <div class="export-header">
                        <div style="font-size:1rem; font-weight:700; color:#111827;" class="dark:text-white">Export Configuration</div>
                        <div style="font-size:.8rem; color:#9ca3af; margin-top:2px;">Pilih parameter export</div>
                    </div>
                    
                    <div style="padding:24px; display:flex; flex-direction:column; gap:20px;">
                        {{-- Export Type Picker --}}
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <label style="font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">Tipe Export</label>
                            @foreach($exportTypes as $type)
                                <label class="export-picker-card {{ $exportType === $type['id'] ? 'active' : '' }} hover:border-indigo-300 dark:hover:border-indigo-500">
                                    <input type="radio" wire:model.live="exportType" value="{{ $type['id'] }}" style="display:none;">
                                    <span style="font-size:1.5rem; margin-right:12px;">{{ $type['icon'] }}</span>
                                    <div style="flex:1;">
                                        <div style="font-size:.85rem; font-weight:700; color:#111827;" class="dark:text-white">{{ $type['name'] }}</div>
                                        <div style="font-size:.75rem; color:#9ca3af; margin-top:2px;">{{ $type['desc'] }}</div>
                                    </div>
                                    @if($exportType === $type['id'])
                                        <div style="width:20px; height:20px; background:#4f46e5; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                            <x-heroicon-o-check style="width:12px; height:12px; color:#fff; stroke-width:3;" />
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        {{-- Year Range Picker --}}
                        <div style="display:flex; flex-direction:column; gap:6px;">
                            <label style="font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">Rentang Periode</label>
                            <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:8px; align-items:center;">
                                <select wire:model.live="selectedYearStart" class="export-select">
                                    @foreach($this->getAvailableYears() as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                                <span style="font-size:.9rem; font-weight:700; color:#9ca3af;">—</span>
                                <select wire:model.live="selectedYearEnd" class="export-select">
                                    @foreach($this->getAvailableYears() as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="font-size:.7rem; color:#9ca3af; margin-top:2px;">
                                @if($selectedYearStart === $selectedYearEnd)
                                    📅 Tahun {{ $selectedYearEnd }}
                                @else
                                    📅 Periode {{ $selectedYearStart }} - {{ $selectedYearEnd }}
                                @endif
                            </div>
                        </div>

                        {{-- Month Picker (only for detail export) --}}
                        @if($exportType === 'detail')
                        <div style="display:flex; flex-direction:column; gap:6px;">
                            <label style="font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">Filter Bulan (Opsional)</label>
                            <select wire:model.live="selectedMonth" class="export-select">
                                @foreach($months as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Format Picker --}}
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <label style="font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">Format Output</label>
                            @foreach($formats as $format)
                                <label class="export-picker-card {{ $selectedFormat === $format['id'] ? 'active' : '' }} hover:border-indigo-300 dark:hover:border-indigo-500">
                                    <input type="radio" wire:model.live="selectedFormat" value="{{ $format['id'] }}" style="display:none;">
                                    <span style="font-size:1.5rem; margin-right:12px;">{{ $format['icon'] }}</span>
                                    <div style="flex:1;">
                                        <div style="font-size:.85rem; font-weight:700; color:#111827;" class="dark:text-white">{{ $format['name'] }}</div>
                                        <div style="font-size:.75rem; color:#9ca3af; margin-top:2px;">{{ $format['desc'] }}</div>
                                    </div>
                                    @if($selectedFormat === $format['id'])
                                        <div style="width:20px; height:20px; background:#4f46e5; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                            <x-heroicon-o-check style="width:12px; height:12px; color:#fff; stroke-width:3;" />
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        {{-- Export Button --}}
                        <button wire:click="export" wire:loading.attr="disabled" class="export-btn">
                            <span wire:loading.remove>Generate Download</span>
                            <span wire:loading style="display:flex; align-items:center; gap:6px;">
                                <svg class="animate-spin" style="width:16px; height:16px; color:#fff;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle style="opacity:.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path style="opacity:.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
                <div class="export-card" style="display:flex; flex-direction:column; min-height:600px;">
                    <div class="export-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:1rem; font-weight:700; color:#111827;" class="dark:text-white">
                                {{ $exportType === 'summary' ? 'Laporan Analitik' : 'Data Pipeline Preview' }}
                            </div>
                            <div style="font-size:.8rem; color:#9ca3af; margin-top:2px;">
                                @if($exportType === 'summary')
                                    Ringkasan analitik seperti halaman Analitik Ekraf
                                @else
                                    Pratinjau hasil export • 
                                    <span style="font-weight:600; color:#4f46e5;">{{ number_format($totalRows) }} records</span> tersedia
                                @endif
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ ($exportType === 'summary' || $totalRows > 0) ? '#10b981' : '#ef4444' }};display:inline-block;animation:pulse 2s infinite;"></span>
                            <span style="font-size:.72rem;font-weight:600;color:#9ca3af;">{{ ($exportType === 'summary' || $totalRows > 0) ? 'Data Ready' : 'No Data' }}</span>
                        </div>
                    </div>

                    <div style="flex:1; overflow-x:auto;">
                        @if($exportType === 'summary')
                            {{-- Summary Preview --}}
                            <div style="padding:40px 24px; text-align:center;">
                                <div style="width:80px; height:80px; background:#eef2ff; border-radius:24px; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;" class="dark:bg-indigo-900/20">
                                    <x-heroicon-o-chart-bar style="width:40px; height:40px; color:#4f46e5;" class="dark:text-indigo-400" />
                                </div>
                                <h4 style="font-size:1.2rem; font-weight:700; color:#111827; margin-bottom:8px;" class="dark:text-white">Laporan Analitik Siap Diekspor</h4>
                                <p style="font-size:.9rem; color:#6b7280; max-width:500px; margin:0 auto 20px;">
                                    Export akan menghasilkan laporan analitik lengkap dengan:
                                </p>
                                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; max-width:500px; margin:0 auto; text-align:left;">
                                    <div style="background:#f9fafb; padding:12px; border-radius:8px; font-size:.85rem;" class="dark:bg-slate-800/50">
                                        ✅ Ringkasan statistik
                                    </div>
                                    <div style="background:#f9fafb; padding:12px; border-radius:8px; font-size:.85rem;" class="dark:bg-slate-800/50">
                                        ✅ Distribusi sub sektor
                                    </div>
                                    <div style="background:#f9fafb; padding:12px; border-radius:8px; font-size:.85rem;" class="dark:bg-slate-800/50">
                                        ✅ Jenis kegiatan
                                    </div>
                                    <div style="background:#f9fafb; padding:12px; border-radius:8px; font-size:.85rem;" class="dark:bg-slate-800/50">
                                        ✅ Bidang pelaku
                                    </div>
                                    <div style="background:#f9fafb; padding:12px; border-radius:8px; font-size:.85rem; grid-column:span 2;" class="dark:bg-slate-800/50">
                                        ✅ Rekapitulasi bulanan per tahun
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Detail Preview --}}
                            @php $previewData = $this->getPreviewData(); @endphp
                            @if($previewData->count() > 0)
                                <table class="export-table">
                                    <thead>
                                        <tr>
                                            <th>Event Identity</th>
                                            <th>Spatial Data</th>
                                            <th>Temporal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($previewData as $row)
                                            <tr>
                                                <td>
                                                    <div style="font-size:.85rem; font-weight:700; color:#111827; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" class="dark:text-white">{{ $row->judul_kegiatan }}</div>
                                                    <div style="font-size:.75rem; color:#9ca3af; margin-top:2px;">{{ $row->nama_organisasi }}</div>
                                                </td>
                                                <td>
                                                    <span style="display:inline-flex; align-items:center; padding:2px 9px; border-radius:999px; font-size:.72rem; font-weight:700; background:#eef2ff; color:#4338ca;" class="dark:bg-indigo-900/30 dark:text-indigo-400">
                                                        {{ $row->lokasi }}
                                                    </span>
                                                </td>
                                                <td style="font-size:.85rem; font-weight:700; color:#111827;" class="dark:text-white">
                                                    {{ date('d M Y', strtotime($row->tanggal)) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; padding:60px 20px; text-align:center;">
                                    <div style="width:80px; height:80px; background:#fef3c7; border-radius:24px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;" class="dark:bg-yellow-900/20">
                                        <x-heroicon-o-exclamation-triangle style="width:40px; height:40px; color:#f59e0b;" class="dark:text-yellow-600" />
                                    </div>
                                    <h4 style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:8px;" class="dark:text-white">Tidak Ada Data</h4>
                                    <p style="font-size:.85rem; color:#9ca3af; max-width:400px; margin-bottom:16px;">
                                        Tidak ada event dengan status <strong>Valid</strong> untuk periode 
                                        <strong>{{ $selectedMonth === 'Semua' ? 'Tahun' : $months[$selectedMonth] }} {{ $selectedYearEnd }}</strong>
                                    </p>
                                    <div style="background:#eef2ff; border:1px solid #c7d2fe; border-radius:8px; padding:12px 16px; font-size:.8rem; color:#4338ca; max-width:400px;" class="dark:bg-indigo-900/20 dark:border-indigo-800">
                                        💡 <strong>Tips:</strong> Coba pilih tahun atau bulan lain, atau pastikan data sudah diimport dengan status Valid.
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Pagination --}}
                    @if($exportType === 'detail' && $previewData->count() > 0)
                        <div style="padding:16px 24px; border-top:1px solid #f3f4f6; background:#f9fafb;" class="dark:border-gray-700 dark:bg-gray-800">
                            {{ $previewData->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
</x-filament-panels::page>
