<x-filament-panels::page>
@php $data = $this->getAnalyticsData(); @endphp

<style data-navigate-once="true">
/* ── Base ── */
.dashboard-wrap { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

/* ── Custom Scrollbar ── */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
.dark ::-webkit-scrollbar-thumb {
    background: #374151;
}
.dark ::-webkit-scrollbar-thumb:hover {
    background: #4b5563;
}

/* ── Summary Cards ── */
.stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px;
    transition: box-shadow .2s;
}
.stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
.stat-card .icon-wrap {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
}
.stat-card .stat-value {
    font-size: 2rem; font-weight: 800;
    line-height: 1.1; letter-spacing: -.03em;
    color: #111827;
}
.stat-card .stat-label { font-size: .75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
.stat-card .stat-sub   { font-size: .8rem; color: #9ca3af; margin-top: 2px; }

/* ── Chart Cards ── */
.chart-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
}
.chart-card-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f3f4f6;
    display: flex; align-items: flex-start; justify-content: space-between;
}
.chart-card-title   { font-size: 1rem; font-weight: 700; color: #111827; }
.chart-card-subtitle{ font-size: .8rem; color: #9ca3af; margin-top: 2px; }
.chart-card-body    { padding: 24px; }

/* ── Legend pill ── */
.legend-pill {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .75rem; font-weight: 600; color: #374151;
    background: #f9fafb; border: 1px solid #e5e7eb;
    border-radius: 999px; padding: 4px 10px;
}
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* ── Horizontal bar rows ── */
.hbar-row { display: flex; align-items: center; gap: 10px; padding: 5px 0; }
.hbar-label {
    width: 160px; min-width: 160px;
    font-size: .78rem; font-weight: 500; color: #374151;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.hbar-track {
    flex: 1; height: 22px;
    background: #f3f4f6; border-radius: 6px; overflow: hidden;
    position: relative;
}
.hbar-fill {
    height: 100%; border-radius: 6px;
    display: flex; align-items: center; justify-content: flex-end;
    padding-right: 8px;
    transition: width 1s cubic-bezier(.4,0,.2,1);
    min-width: 36px;
}
.hbar-fill span { font-size: .7rem; font-weight: 700; color: #fff; white-space: nowrap; }
.hbar-rank {
    width: 22px; min-width: 22px; height: 22px;
    border-radius: 6px; background: #f3f4f6;
    font-size: .68rem; font-weight: 700; color: #9ca3af;
    display: flex; align-items: center; justify-content: center;
}

/* ── Donut ── */
.donut-wrap { position: relative; display: inline-flex; }
.donut-center {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
}
.donut-center .dc-val  { font-size: 1.6rem; font-weight: 800; color: #111827; line-height: 1; }
.donut-center .dc-lbl  { font-size: .7rem; font-weight: 600; color: #9ca3af; margin-top: 3px; }

/* ── Table ── */
.perf-table { width: 100%; border-collapse: collapse; }
.perf-table th {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #9ca3af;
    padding: 10px 16px; text-align: left;
    background: #f9fafb; border-bottom: 1px solid #f3f4f6;
}
.perf-table td { padding: 11px 16px; border-bottom: 1px solid #f9fafb; font-size: .85rem; color: #374151; }
.perf-table tbody tr:nth-child(even) td { background-color: #f8fafc; }
.perf-table tr:last-child td { border-bottom: none; }
.perf-table tr:hover td { background: #fafafa; }
.perf-table .td-num { font-weight: 700; color: #111827; text-align: right; }
.perf-table tfoot td { font-weight: 800; color: #111827; background: #f9fafb; border-top: 2px solid #e5e7eb; }

/* ── Badge ── */
.badge { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 999px; font-size: .72rem; font-weight: 700; }
.badge-indigo { background: #eef2ff; color: #4338ca; }
.badge-emerald{ background: #d1fae5; color: #065f46; }
.badge-blue   { background: #dbeafe; color: #1e40af; }
.badge-purple { background: #ede9fe; color: #5b21b6; }

/* ── Dark mode overrides ── */
.dark .stat-card,
.dark .chart-card { background: #1f2937; border-color: #374151; }
.dark .stat-card .stat-value,
.dark .chart-card-title { color: #f9fafb; }
.dark .chart-card-header { border-color: #374151; }
.dark .hbar-track { background: #374151; }
.dark .hbar-rank  { background: #374151; color: #6b7280; }
.dark .hbar-label { color: #d1d5db; }
.dark .legend-pill{ background: #374151; border-color: #4b5563; color: #d1d5db; }
.dark .perf-table th { background: #374151; color: #9ca3af; border-color: #4b5563; }
.dark .perf-table td { color: #d1d5db; border-color: #374151; }
.dark .perf-table tbody tr:nth-child(even) td { background-color: #1e293b; }
.dark .perf-table tr:hover td { background: #374151; }
.dark .perf-table tfoot td { background: #374151; border-color: #4b5563; color: #f9fafb; }
.dark .donut-center .dc-val { color: #f9fafb; }
.dark .badge-indigo { background: #312e81; color: #a5b4fc; }
.dark .badge-emerald{ background: #064e3b; color: #6ee7b7; }
.dark .badge-blue   { background: #1e3a5f; color: #93c5fd; }
.dark .badge-purple { background: #3b0764; color: #c4b5fd; }

/* ── Table Styling (from Rekap Bulanan) ── */
.premium-table-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
.dark .premium-table-card { background: #111827; border-color: #1f2937; }
.p-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.p-table th { padding: 16px 24px; background: #f9fafb; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #f3f4f6; }
.dark .p-table th { background: #1f2937; color: #9ca3af; border-color: #374151; }
.p-table td { padding: 20px 24px; font-size: 0.875rem; color: #374151; border-bottom: 1px solid #f3f4f6; transition: background 0.2s; }
.dark .p-table td { color: #d1d5db; border-color: #1f2937; }
.p-table tr:hover td { background: #f9fafb; }
.dark .p-table tr:hover td { background: #1f2937/40; }
.p-badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.01em; }
.pb-indigo { background: #eef2ff; color: #4338ca; }
.pb-emerald { background: #ecfdf5; color: #059669; }
.pb-amber { background: #fffbeb; color: #d97706; }
.dark .pb-indigo { background: #312e81; color: #a5b4fc; }
.p-progress-track { width: 100px; height: 6px; background: #f3f4f6; border-radius: 999px; overflow: hidden; }
.dark .p-progress-track { background: #374151; }
.p-progress-fill { height: 100%; border-radius: 999px; transition: width 1s ease-out; }
</style>

@php
    /* ── Pre-compute all values once ── */
    $totalEvents      = $data['bulanan']->sum('total_event');
    $totalPeserta     = $data['bulanan']->sum('total_pengunjung');
    $totalKomersil    = $data['bulanan']->sum('total_komersil');
    $totalNonKomersil = $data['bulanan']->sum('total_non_komersil');
    $avgPeserta       = $totalEvents > 0 ? round($totalPeserta / $totalEvents) : 0;

    $komPct    = ($totalKomersil + $totalNonKomersil) > 0
                    ? round($totalKomersil    / ($totalKomersil + $totalNonKomersil) * 100, 1) : 0;
    $nonKomPct = ($totalKomersil + $totalNonKomersil) > 0
                    ? round($totalNonKomersil / ($totalKomersil + $totalNonKomersil) * 100, 1) : 0;

    /* Donut circumference r=54 → 2πr ≈ 339.3 */
    $circ        = 339.3;
    $komDash     = round($komPct    / 100 * $circ, 2);
    $nonKomDash  = round($nonKomPct / 100 * $circ, 2);
    $komOffset   = 0;
    $nonKomOffset= -$komDash;

    /* Sub-sektor sorted, all items */
    $subSektorSorted = $data['kategoriEkraf']->sortByDesc('total');
    $maxSubSektor    = $subSektorSorted->max('total') ?: 1;

    /* Indigo palette steps for bar gradient */
    $barColors = [
        '#4f46e5','#6366f1','#818cf8','#a5b4fc',
        '#c7d2fe','#4338ca','#3730a3','#312e81',
        '#4f46e5','#6366f1','#818cf8','#a5b4fc',
        '#c7d2fe','#4338ca','#3730a3',
    ];
@endphp

<div class="dashboard-wrap space-y-6 pb-8">

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <div class="chart-card">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl">
                    <x-heroicon-o-chart-bar class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        Analitik Ekonomi Kreatif
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Laporan performa ekosistem MCC &mdash; Tahun <strong class="text-indigo-600">{{ $data['year'] }}</strong>
                    </p>
                </div>
            </div>
            <div class="max-w-xs">
                {{ $this->filtersForm }}
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SUMMARY CARDS
    ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Card 1: Pelaku --}}
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="icon-wrap" style="background:#eef2ff">
                    <svg class="w-5 h-5" style="color:#4f46e5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 10-8 0"/>
                    </svg>
                </div>
                <span class="badge badge-indigo">{{ $data['year'] }}</span>
            </div>
            <div class="stat-label">Total Pelaku Ekraf</div>
            <div class="stat-value">{{ number_format($data['totalPelakuYear']) }}</div>
            <div class="stat-sub">Organisasi unik tercatat</div>
        </div>

        {{-- Card 2: Events --}}
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="icon-wrap" style="background:#d1fae5">
                    <svg class="w-5 h-5" style="color:#059669" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="badge badge-emerald">{{ $data['year'] }}</span>
            </div>
            <div class="stat-label">Total Event</div>
            <div class="stat-value">{{ number_format($totalEvents) }}</div>
            <div class="stat-sub">Kegiatan terverifikasi</div>
        </div>

        {{-- Card 3: Peserta --}}
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="icon-wrap" style="background:#dbeafe">
                    <svg class="w-5 h-5" style="color:#2563eb" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m0-4a4 4 0 118 0 4 4 0 01-8 0z"/>
                    </svg>
                </div>
                <span class="badge badge-blue">Peserta</span>
            </div>
            <div class="stat-label">Total Peserta</div>
            <div class="stat-value">{{ number_format($totalPeserta) }}</div>
            <div class="stat-sub">Rata-rata {{ $avgPeserta }} / event</div>
        </div>

        {{-- Card 4: Komersial --}}
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="icon-wrap" style="background:#ede9fe">
                    <svg class="w-5 h-5" style="color:#7c3aed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="badge badge-purple">{{ $komPct }}%</span>
            </div>
            <div class="stat-label">Komersial</div>
            <div class="stat-value">{{ number_format($totalKomersil) }}</div>
            <div class="stat-sub">{{ number_format($totalNonKomersil) }} Non-Komersial</div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         TREND ACTIVITY — Area Chart (Chart.js)
    ══════════════════════════════════════════ --}}
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <div class="chart-card-title">Trend Aktivitas Bulanan</div>
                <div class="chart-card-subtitle">Event &amp; Peserta per bulan — {{ $data['year'] }}</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="legend-pill"><span class="legend-dot" style="background:#4f46e5"></span>Event</span>
                <span class="legend-pill"><span class="legend-dot" style="background:#10b981"></span>Peserta</span>
                <span class="legend-pill" style="color:#f59e0b;border-color:#fde68a;background:#fffbeb">
                    <span style="width:20px;height:3px;border-radius:2px;flex-shrink:0;background:repeating-linear-gradient(90deg,currentColor 0,currentColor 4px,transparent 4px,transparent 7px);color:#f59e0b"></span>Komersial
                </span>
                <span class="legend-pill" style="color:#0ea5e9;border-color:#bae6fd;background:#f0f9ff">
                    <span style="width:20px;height:3px;border-radius:2px;flex-shrink:0;background:repeating-linear-gradient(90deg,currentColor 0,currentColor 4px,transparent 4px,transparent 7px);color:#0ea5e9"></span>Non-Komersial
                </span>
            </div>
        </div>
        <div class="chart-card-body">
            <div style="position:relative; height:320px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         BOTTOM ROW: Bar Chart + Donut
    ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {{-- Sub Sektor Horizontal Bar — takes 3/5 --}}
        <div class="chart-card xl:col-span-3">
            <div class="chart-card-header">
                <div>
                    <div class="chart-card-title">Distribusi Sub Sektor Ekraf</div>
                    <div class="chart-card-subtitle">Jumlah event per sub sektor — diurutkan terbesar</div>
                </div>
                <span class="legend-pill" style="background:#eef2ff; border-color:#c7d2fe; color:#4338ca;">
                    {{ $subSektorSorted->count() }} sub sektor
                </span>
            </div>
            <div class="chart-card-body" style="max-height:520px; overflow-y:auto;">
                <div style="display:flex; flex-direction:column; gap:6px;">
                    @foreach($subSektorSorted as $i => $item)
                    @php
                        $pct   = round(($item->total / $maxSubSektor) * 100);
                        $color = $barColors[$i % count($barColors)];
                        /* lighten for very small bars */
                        $opacity = max(0.55, $pct / 100);
                    @endphp
                    <div class="hbar-row">
                        <div class="hbar-rank">{{ $i + 1 }}</div>
                        <div class="hbar-label" title="{{ $item->label ?: 'Lainnya' }}">
                            {{ $item->label ?: 'Lainnya' }}
                        </div>
                        <div class="hbar-track">
                            <div class="hbar-fill" style="width:{{ max($pct, 8) }}%; background:{{ $color }};">
                                <span>{{ number_format($item->total) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Donut + breakdown — takes 2/5 --}}
        <div class="chart-card xl:col-span-2 flex flex-col">
            <div class="chart-card-header">
                <div>
                    <div class="chart-card-title">Komersial vs Non-Komersial</div>
                    <div class="chart-card-subtitle">Proporsi jenis kegiatan</div>
                </div>
            </div>
            <div class="chart-card-body flex-1 flex flex-col items-center justify-center gap-6">

                {{-- SVG Donut --}}
                <div class="donut-wrap">
                    <svg width="180" height="180" viewBox="0 0 180 180" style="transform:rotate(-90deg)">
                        {{-- track --}}
                        <circle cx="90" cy="90" r="54" fill="none" stroke="#f3f4f6" stroke-width="20"/>
                        {{-- komersial (emerald) --}}
                        <circle cx="90" cy="90" r="54" fill="none"
                                stroke="#10b981" stroke-width="20"
                                stroke-dasharray="{{ $komDash }} {{ $circ }}"
                                stroke-dashoffset="{{ $komOffset }}"
                                stroke-linecap="round"/>
                        {{-- non-komersial (indigo) --}}
                        <circle cx="90" cy="90" r="54" fill="none"
                                stroke="#6366f1" stroke-width="20"
                                stroke-dasharray="{{ $nonKomDash }} {{ $circ }}"
                                stroke-dashoffset="{{ $nonKomOffset }}"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="donut-center">
                        <div class="dc-val">{{ number_format($totalKomersil + $totalNonKomersil) }}</div>
                        <div class="dc-lbl">Total Event</div>
                    </div>
                </div>

                {{-- Legend rows --}}
                <div style="width:100%; display:flex; flex-direction:column; gap:12px;">
                    {{-- Komersial --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#f0fdf4; border-radius:12px; border:1px solid #bbf7d0;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:12px;height:12px;border-radius:50%;background:#10b981;flex-shrink:0;"></div>
                            <div>
                                <div style="font-size:.8rem;font-weight:700;color:#065f46;">Komersial</div>
                                <div style="font-size:.7rem;color:#6ee7b7;">{{ $komPct }}% dari total</div>
                            </div>
                        </div>
                        <div style="font-size:1.25rem;font-weight:800;color:#065f46;">{{ number_format($totalKomersil) }}</div>
                    </div>
                    {{-- Non-Komersial --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#eef2ff; border-radius:12px; border:1px solid #c7d2fe;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:12px;height:12px;border-radius:50%;background:#6366f1;flex-shrink:0;"></div>
                            <div>
                                <div style="font-size:.8rem;font-weight:700;color:#3730a3;">Non-Komersial</div>
                                <div style="font-size:.7rem;color:#a5b4fc;">{{ $nonKomPct }}% dari total</div>
                            </div>
                        </div>
                        <div style="font-size:1.25rem;font-weight:800;color:#3730a3;">{{ number_format($totalNonKomersil) }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SDGs DISTRIBUTION GRID
    ══════════════════════════════════════════ --}}
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <div class="chart-card-title">Capaian Kategori Program SDGs</div>
                <div class="chart-card-subtitle">Jumlah event yang berkontribusi pada masing-masing program SDGs &mdash; Tahun {{ $data['year'] }}</div>
            </div>
            <span class="legend-pill" style="background:#eef2ff; border-color:#c7d2fe; color:#4338ca;">
                17 Tujuan Pembangunan Berkelanjutan
            </span>
        </div>
        <div class="chart-card-body">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-6 gap-4">
                @php
                    $sdgMeta = [
                        1 => ['title' => 'Tanpa Kemiskinan', 'color' => '#E5243B', 'text' => 'text-white'],
                        2 => ['title' => 'Tanpa Kelaparan', 'color' => '#DDA63A', 'text' => 'text-white'],
                        3 => ['title' => 'Kehidupan Sehat & Sejahtera', 'color' => '#4C9F38', 'text' => 'text-white'],
                        4 => ['title' => 'Pendidikan Berkualitas', 'color' => '#C5192D', 'text' => 'text-white'],
                        5 => ['title' => 'Kesetaraan Gender', 'color' => '#FF3A21', 'text' => 'text-white'],
                        6 => ['title' => 'Air Bersih & Sanitasi Layak', 'color' => '#26BDE2', 'text' => 'text-white'],
                        7 => ['title' => 'Energi Bersih & Terjangkau', 'color' => '#FCC30B', 'text' => 'text-gray-900'],
                        8 => ['title' => 'Pekerjaan Layak & Pertumbuhan Ekonomi', 'color' => '#A21942', 'text' => 'text-white'],
                        9 => ['title' => 'Industri, Inovasi & Infrastruktur', 'color' => '#FD6925', 'text' => 'text-white'],
                        10 => ['title' => 'Berkurangnya Kesenjangan', 'color' => '#DD1367', 'text' => 'text-white'],
                        11 => ['title' => 'Kota & Pemukiman Berkelanjutan', 'color' => '#FD9D24', 'text' => 'text-white'],
                        12 => ['title' => 'Konsumsi & Produksi Bertanggung Jawab', 'color' => '#C9992D', 'text' => 'text-white'],
                        13 => ['title' => 'Penanganan Perubahan Iklim', 'color' => '#3F7E44', 'text' => 'text-white'],
                        14 => ['title' => 'Ekosistem Lautan', 'color' => '#0A97D9', 'text' => 'text-white'],
                        15 => ['title' => 'Ekosistem Daratan', 'color' => '#56C02B', 'text' => 'text-white'],
                        16 => ['title' => 'Perdamaian, Keadilan & Institusi Tangguh', 'color' => '#00689D', 'text' => 'text-white'],
                        17 => ['title' => 'Kemitraan untuk Mencapai Tujuan', 'color' => '#19486A', 'text' => 'text-white'],
                    ];
                @endphp
                @foreach($sdgMeta as $num => $meta)
                    @php
                        $count = $data['sdgsCounts']["SDGS $num"] ?? 0;
                    @endphp
                    <div class="relative flex flex-col justify-between p-4 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-md border border-gray-100 dark:border-gray-800" style="background-color: {{ $meta['color'] }}12;">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg font-black text-sm" style="background-color: {{ $meta['color'] }}; color: {{ str_contains($meta['text'], 'gray') ? '#1f2937' : '#ffffff' }}">
                                {{ $num }}
                            </div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                                {{ number_format($count) }}
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-[0.68rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Goal {{ $num }}</div>
                            <div class="text-xs font-semibold text-gray-800 dark:text-slate-200 mt-0.5 line-clamp-2 leading-snug" title="{{ $meta['title'] }}">
                                {{ $meta['title'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         MONTHLY PERFORMANCE TABLE
    ══════════════════════════════════════════ --}}
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <div class="chart-card-title">Rekapitulasi Bulanan {{ $data['year'] }}</div>
                <div class="chart-card-subtitle">Rincian lengkap per bulan</div>
            </div>
            <div class="flex items-center gap-2">
                <span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;animation:pulse 2s infinite;"></span>
                <span style="font-size:.72rem;font-weight:600;color:#9ca3af;">Live Data</span>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th style="text-align:right;">Event</th>
                        <th style="text-align:right;">Peserta</th>
                        <th style="text-align:right;">Komersial</th>
                        <th style="text-align:right;">Non-Komersial</th>
                        <th style="text-align:right;">Pelaku Unik</th>
                        <th style="text-align:right;">Avg Peserta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['bulanan'] as $row)
                    @php
                        $rowAvg = $row->total_event > 0 ? round($row->total_pengunjung / $row->total_event) : 0;
                        $rowKomPct = ($row->total_komersil + $row->total_non_komersil) > 0
                            ? round($row->total_komersil / ($row->total_komersil + $row->total_non_komersil) * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <span style="font-weight:700;color:#111827;" class="dark:text-white">{{ $row->bulan }}</span>
                        </td>
                        <td class="td-num">
                            <span class="badge badge-indigo">{{ number_format($row->total_event) }}</span>
                        </td>
                        <td class="td-num">{{ number_format($row->total_pengunjung) }}</td>
                        <td class="td-num">
                            <span class="badge badge-emerald">{{ number_format($row->total_komersil) }}</span>
                        </td>
                        <td class="td-num">
                            <span class="badge badge-purple">{{ number_format($row->total_non_komersil) }}</span>
                        </td>
                        <td class="td-num">{{ number_format($row->total_pelaku) }}</td>
                        <td class="td-num" style="color:#6b7280;">{{ $rowAvg }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="font-weight:800;">Total</td>
                        <td class="td-num">{{ number_format($totalEvents) }}</td>
                        <td class="td-num">{{ number_format($totalPeserta) }}</td>
                        <td class="td-num">{{ number_format($totalKomersil) }}</td>
                        <td class="td-num">{{ number_format($totalNonKomersil) }}</td>
                        <td class="td-num">{{ number_format($data['totalPelakuYear']) }}</td>
                        <td class="td-num">{{ $avgPeserta }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         TABLE: BREAKDOWN BULANAN (Merged from RekapBulanan)
    ══════════════════════════════════════════ --}}
    <div class="premium-table-card mt-6">
        <div class="border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900" style="padding: 1.5rem 2rem;">
            <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Breakdown Performa Bulanan</h3>
                <p class="text-xs text-gray-500 font-medium mt-0.5 uppercase tracking-wide">Data teragregasi per bulan di tahun {{ $data['year'] }}</p>
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-emerald-100 dark:border-emerald-800" style="padding: 0.375rem 0.75rem; background-color: #ecfdf5;">
                <div class="w-2 h-2 rounded-full animate-pulse" style="background-color: #10b981;"></div>
                <span class="text-[10px] font-black uppercase" style="color: #059669;">Valid Data Only</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="p-table">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Bulan</th>
                        <th class="text-center whitespace-nowrap">Event</th>
                        <th class="text-center whitespace-nowrap">Pengunjung</th>
                        <th class="text-center whitespace-nowrap">Pelaku Unik</th>
                        <th class="text-right whitespace-nowrap">Rasio Non-Kom</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['bulanan'] as $index => $row)
                        @php
                            $pct = $row->total_event > 0 ? round($row->total_non_komersil / $row->total_event * 100) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center border border-gray-100 dark:border-gray-700">
                                        <span class="text-[10px] font-black text-gray-400">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">{{ $row->bulan }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="p-badge pb-indigo">{{ number_format($row->total_event) }}</span>
                            </td>
                            <td class="text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums">{{ number_format($row->total_pengunjung) }}</span>
                                    <div class="w-8 h-1 bg-emerald-500/20 rounded-full mt-1.5 overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="text-sm font-bold text-gray-600 dark:text-gray-400 tabular-nums">{{ number_format($row->total_pelaku) }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-4">
                                    <div class="p-progress-track hidden sm:block w-24">
                                        <div class="p-progress-fill" style="width: {{ $pct }}%; background-color: #6366f1;"></div>
                                    </div>
                                    <span class="text-sm font-black tabular-nums text-right" style="min-width: 3.5rem; color: #4f46e5;">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <td class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Total Tahun Ini</td>
                        <td class="text-center">
                            <span class="p-badge pb-indigo" style="font-size: 0.875rem">{{ number_format($totalEvents) }}</span>
                        </td>
                        <td class="text-center font-black text-gray-900 dark:text-white tabular-nums text-base">
                            {{ number_format($totalPeserta) }}
                        </td>
                        <td class="text-center font-bold text-gray-600 dark:text-gray-400 tabular-nums">
                            {{ number_format($data['totalPelakuYear']) }}
                        </td>
                        <td class="text-right">
                            <span class="text-base font-black tabular-nums" style="display:inline-block; min-width: 3.5rem; color: #4f46e5;">{{ $nonKomPct }}%</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>{{-- end .dashboard-wrap --}}

{{-- ══════════════════════════════════════════
     CHART.JS — Trend Area Chart
══════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    /* ── Data from PHP ── */
    const labels   = @json($data['bulanan']->pluck('bulan'));
    const events   = @json($data['bulanan']->pluck('total_event')->map(fn($v) => (int)$v));
    const peserta  = @json($data['bulanan']->pluck('total_pengunjung')->map(fn($v) => (int)$v));
    const komersil = @json($data['bulanan']->pluck('total_komersil')->map(fn($v) => (int)$v));
    const nonKom   = @json($data['bulanan']->pluck('total_non_komersil')->map(fn($v) => (int)$v));

    console.log('Chart Data:', { labels, events, peserta, komersil, nonKom });

    /* ── Detect dark mode ── */
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor  = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
    const tickColor  = isDark ? '#6b7280' : '#9ca3af';
    const tooltipBg  = isDark ? '#1f2937' : '#fff';
    const tooltipBdr = isDark ? '#374151' : '#e5e7eb';
    const tooltipTxt = isDark ? '#f9fafb' : '#111827';

    /* ── Gradient helper ── */
    function makeGradient(ctx, colorTop, colorBot) {
        const g = ctx.createLinearGradient(0, 0, 0, 320);
        g.addColorStop(0, colorTop);
        g.addColorStop(1, colorBot);
        return g;
    }

    function initChart() {
        const canvas = document.getElementById('trendChart');
        if (!canvas) {
            console.error('Canvas #trendChart not found');
            return;
        }

        // Destroy existing chart if any
        if (window.analitikChartInstance) {
            window.analitikChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');

        const hoverLabelsPlugin = {
            id: 'hoverLabels',
            afterDatasetsDraw(chart) {
                const { ctx, chartArea: { top, bottom, left, right } } = chart;
                const activeElements = chart.getActiveElements();
                
                if (!activeElements || activeElements.length === 0) return;
                
                const index = activeElements[0].index;
                const x = activeElements[0].element.x;
                
                // 1. Draw vertical reference line
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x, top);
                ctx.lineTo(x, bottom);
                ctx.lineWidth = 1;
                ctx.strokeStyle = document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.1)';
                ctx.stroke();
                ctx.restore();
                
                // 2. Collect visible dataset points
                const tooltips = [];
                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    const meta = chart.getDatasetMeta(datasetIndex);
                    if (meta.hidden || !chart.isDatasetVisible(datasetIndex)) return;
                    
                    const element = meta.data[index];
                    if (!element) return;
                    
                    const y = element.y;
                    const value = dataset.data[index];
                    const labelText = dataset.label + ': ' + Number(value).toLocaleString('id-ID');
                    
                    tooltips.push({
                        y: y,
                        origY: y,
                        labelText: labelText,
                        borderColor: dataset.borderColor
                    });
                });
                
                if (tooltips.length === 0) return;
                
                // Sort by Y position (ascending: top to bottom)
                tooltips.sort((a, b) => a.origY - b.origY);
                
                // Solve overlaps by pushing overlapping boxes downwards
                const boxHeight = 20;
                const spacing = 4;
                const minSpacing = boxHeight + spacing;
                
                for (let i = 1; i < tooltips.length; i++) {
                    if (tooltips[i].y - tooltips[i-1].y < minSpacing) {
                        tooltips[i].y = tooltips[i-1].y + minSpacing;
                    }
                }
                
                // Push upwards if the bottom-most box goes out of bounds
                const maxBoundary = bottom - (boxHeight / 2) - 4;
                if (tooltips[tooltips.length - 1].y > maxBoundary) {
                    tooltips[tooltips.length - 1].y = maxBoundary;
                    for (let i = tooltips.length - 2; i >= 0; i--) {
                        if (tooltips[i+1].y - tooltips[i].y < minSpacing) {
                            tooltips[i].y = tooltips[i+1].y - minSpacing;
                        }
                    }
                }

                // Push downwards if the top-most box goes out of bounds
                const minBoundary = top + (boxHeight / 2) + 4;
                if (tooltips[0].y < minBoundary) {
                    tooltips[0].y = minBoundary;
                    for (let i = 1; i < tooltips.length; i++) {
                        if (tooltips[i].y - tooltips[i-1].y < minSpacing) {
                            tooltips[i].y = tooltips[i-1].y + minSpacing;
                        }
                    }
                }
                
                // Draw each resolved tooltip box
                tooltips.forEach((t) => {
                    ctx.save();
                    ctx.font = 'bold 10px Inter, sans-serif';
                    const textWidth = ctx.measureText(t.labelText).width;
                    
                    const paddingX = 8;
                    const indicatorWidth = 3;
                    const indicatorGap = 5;
                    
                    const boxWidth = textWidth + (paddingX * 2) + indicatorWidth + indicatorGap;
                    
                    const gap = 10;
                    let boxX = x + gap;
                    if (boxX + boxWidth > right) {
                        boxX = x - boxWidth - gap;
                    }
                    const boxY = t.y - (boxHeight / 2);
                    
                    // Draw background box
                    ctx.beginPath();
                    const radius = 5;
                    if (typeof ctx.roundRect === 'function') {
                        ctx.roundRect(boxX, boxY, boxWidth, boxHeight, radius);
                    } else {
                        ctx.rect(boxX, boxY, boxWidth, boxHeight);
                    }
                    ctx.fillStyle = document.documentElement.classList.contains('dark') ? 'rgba(15, 23, 42, 0.95)' : 'rgba(30, 41, 59, 0.9)';
                    ctx.fill();
                    
                    // Draw color indicator bar
                    ctx.beginPath();
                    if (typeof ctx.roundRect === 'function') {
                        ctx.roundRect(boxX + paddingX, boxY + (boxHeight - 10) / 2, indicatorWidth, 10, 1);
                    } else {
                        ctx.rect(boxX + paddingX, boxY + (boxHeight - 10) / 2, indicatorWidth, 10);
                    }
                    ctx.fillStyle = t.borderColor;
                    ctx.fill();
                    
                    // Draw text
                    ctx.fillStyle = '#ffffff';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(t.labelText, boxX + paddingX + indicatorWidth + indicatorGap, boxY + (boxHeight / 2) + 0.5);
                    ctx.restore();
                });
            }
        };

        window.analitikChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Event',
                        data: events,
                        borderColor: '#4f46e5',
                        backgroundColor: makeGradient(ctx, 'rgba(79,70,229,.18)', 'rgba(79,70,229,.01)'),
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.42,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        yAxisID: 'yLeft',
                    },
                    {
                        label: 'Peserta',
                        data: peserta,
                        borderColor: '#10b981',
                        backgroundColor: makeGradient(ctx, 'rgba(16,185,129,.15)', 'rgba(16,185,129,.01)'),
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.42,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        yAxisID: 'yRight',
                    },
                    {
                        label: 'Komersial',
                        data: komersil,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [6, 3],
                        fill: false,
                        tension: 0.42,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'yLeft',
                    },
                    {
                        label: 'Non-Komersial',
                        data: nonKom,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [6, 3],
                        fill: false,
                        tension: 0.42,
                        pointBackgroundColor: '#0ea5e9',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'yLeft',
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: tickColor, font: { size: 11, weight: '600' } }
                    },
                    yLeft: {
                        position: 'left',
                        grid: { color: gridColor, drawBorder: false },
                        border: { display: false, dash: [4, 4] },
                        ticks: {
                            color: tickColor,
                            font: { size: 11 },
                            maxTicksLimit: 6,
                            callback: v => v.toLocaleString('id-ID')
                        }
                    },
                    yRight: {
                        position: 'right',
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#10b981',
                            font: { size: 11 },
                            maxTicksLimit: 6,
                            callback: v => v.toLocaleString('id-ID')
                        }
                    }
                }
            },
            plugins: [hoverLabelsPlugin]
        });

        console.log('Chart initialized successfully');
    }

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        initChart();
    }

    // Re-initialize on Livewire updates
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                setTimeout(initChart, 100);
            });
        });
    }
})();
</script>

</x-filament-panels::page>
