@php
    use App\Models\Event;

    // Periode data yang tersedia
    $yearRange = Event::selectRaw('
        MIN(EXTRACT(YEAR FROM tanggal))::integer as min_year,
        MAX(EXTRACT(YEAR FROM tanggal))::integer as max_year,
        COUNT(DISTINCT EXTRACT(YEAR FROM tanggal)::integer) as total_years
    ')->whereNotNull('tanggal')->first();

    $minYear = $yearRange?->min_year ?? date('Y');
    $maxYear = $yearRange?->max_year ?? date('Y');
    $totalYears = $yearRange?->total_years ?? 1;

    // Total data ringkas
    $totalEvents = Event::valid()->primary()->count();
@endphp

<style>
    /* ── Dashboard Header ── */
    .dash-header-wrap {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .dark .dash-header-wrap {
        background: #1e293b;
        border-color: #334155;
    }
    .dash-header-left { display: flex; align-items: center; gap: 14px; }
    .dash-header-icon {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(79,70,229,0.3);
    }
    .dash-header-title {
        font-size: 1.25rem; font-weight: 800;
        color: #111827; letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .dark .dash-header-title { color: #f1f5f9; }
    .dash-header-sub {
        font-size: .8rem; color: #9ca3af; margin-top: 2px;
    }
    .dash-header-right {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .dash-stat-pill {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 14px;
        border-radius: 10px;
        border: 1px solid;
        font-size: .78rem; font-weight: 700;
        white-space: nowrap;
    }
    .dash-stat-pill-indigo {
        background: #eef2ff; border-color: #c7d2fe; color: #3730a3;
    }
    .dark .dash-stat-pill-indigo {
        background: rgba(79,70,229,.15); border-color: rgba(79,70,229,.3); color: #a5b4fc;
    }
    .dash-stat-pill-emerald {
        background: #d1fae5; border-color: #6ee7b7; color: #065f46;
    }
    .dark .dash-stat-pill-emerald {
        background: rgba(16,185,129,.15); border-color: rgba(16,185,129,.3); color: #6ee7b7;
    }
    .dash-stat-pill-amber {
        background: #fffbeb; border-color: #fde68a; color: #92400e;
    }
    .dark .dash-stat-pill-amber {
        background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.3); color: #fcd34d;
    }

    /* Dot live indicator */
    .live-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #10b981;
        animation: live-pulse 2s infinite;
        flex-shrink: 0;
    }
    @keyframes live-pulse {
        0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16,185,129,.6); }
        50% { opacity: 0.7; box-shadow: 0 0 0 4px rgba(16,185,129,.0); }
    }

    /* Premium Dashboard Widget Styles */
    .fi-wi-stats-overview-stat {
        border-radius: 1.5rem !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }
    .fi-wi-chart {
        border-radius: 2rem !important;
        overflow: hidden !important;
    }
    .fi-wi-chart .fi-section {
        border-radius: 2rem !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }

    /* Scrollbar consistency */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .dark ::-webkit-scrollbar-thumb { background: #374151; }
</style>

<div class="dash-header-wrap">
    <div class="dash-header-left">
        <div class="dash-header-icon">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <div class="dash-header-title">Dashboard SiREKAP MCC</div>
            <div class="dash-header-sub">Rekap data kegiatan Malang Creative Center</div>
        </div>
    </div>

    <div class="dash-header-right">
        {{-- Periode data tersedia --}}
        <span class="dash-stat-pill dash-stat-pill-indigo">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Periode: {{ $minYear }}{{ $minYear !== $maxYear ? ' – ' . $maxYear : '' }}
        </span>

        {{-- Total tahun data --}}
        @if($totalYears > 1)
        <span class="dash-stat-pill dash-stat-pill-amber">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $totalYears }} Tahun Data
        </span>
        @endif

        {{-- Total event live --}}
        <span class="dash-stat-pill dash-stat-pill-emerald">
            <span class="live-dot"></span>
            {{ number_format($totalEvents) }} Event Valid
        </span>
    </div>
</div>
