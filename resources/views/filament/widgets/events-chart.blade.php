@php
    $chartData = $this->getChartData();
    $hasData   = collect($chartData['events'])->some(fn($v) => $v > 0)
              || collect($chartData['peserta'])->some(fn($v) => $v > 0);

    $chartPayload = [
        'labels'   => $chartData['labels'],
        'events'   => $chartData['events'],
        'peserta'  => $chartData['peserta'],
        'komersil' => $chartData['komersil'],
        'nonKom'   => $chartData['nonKom'],
    ];
    $uid = 'ech_' . md5(json_encode($chartPayload));
@endphp

<x-filament-widgets::widget>
<style>
.ech-wrap{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;transition:box-shadow .2s}
.dark .ech-wrap{background:#1e293b;border-color:#334155}
.ech-wrap:hover{box-shadow:0 4px 24px rgba(0,0,0,.08)}
.ech-head{padding:20px 24px 16px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px}
.dark .ech-head{border-bottom-color:#334155}
.ech-title{font-size:1rem;font-weight:700;color:#111827}
.dark .ech-title{color:#f1f5f9}
.ech-sub{font-size:.8rem;color:#9ca3af;margin-top:2px}
.ech-pills{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.ech-pill{display:inline-flex;align-items:center;gap:6px;font-size:.72rem;font-weight:600;color:#374151;background:#f9fafb;border:1px solid #e5e7eb;border-radius:999px;padding:4px 10px;white-space:nowrap;cursor:pointer;user-select:none;transition:opacity .15s,filter .15s}
.dark .ech-pill{background:#334155;border-color:#475569;color:#cbd5e1}
.ech-pill.inactive{opacity:0.35;text-decoration:line-through;filter:grayscale(1)}
.ech-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.ech-dash{width:20px;height:3px;border-radius:2px;flex-shrink:0;background:repeating-linear-gradient(90deg,currentColor 0,currentColor 4px,transparent 4px,transparent 7px)}
.ech-body{padding:24px}
.ech-canvas-wrap{position:relative;height:320px}
.ech-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;height:320px}
</style>

{{-- Wrapper: wire:ignore.self so Livewire never morphs children, preserving Alpine + canvas --}}
<div class="ech-wrap" wire:key="ech-{{ md5(json_encode($chartPayload)) }}" x-data="{
    payload: @js($chartPayload),
    active: { 0: true, 1: true, 2: true, 3: true },
    toggle(index) {
        const canvas = this.$refs.canvas;
        if (!canvas || !canvas._chart) return;
        const chart = canvas._chart;
        const vis = chart.isDatasetVisible(index);
        chart.setDatasetVisibility(index, !vis);
        chart.update('none');
        this.active[index] = !vis;
    },
    init() {
        const draw = () => {
            if (typeof Chart === 'undefined') {
                if (!document.getElementById('__cjs_lib')) {
                    const s = document.createElement('script');
                    s.id = '__cjs_lib';
                    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
                    s.onload = () => draw();
                    document.head.appendChild(s);
                } else {
                    setTimeout(draw, 100);
                }
                return;
            }

            const canvas = this.$refs.canvas;
            if (!canvas) return;

            if (canvas._chart) {
                canvas._chart.destroy();
            }

            const ctx = canvas.getContext('2d');
            const dark = document.documentElement.classList.contains('dark');
            const gc = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
            const tc = dark ? '#6b7280' : '#9ca3af';

            const mkGrad = (a, b) => {
                const g = ctx.createLinearGradient(0, 0, 0, 320);
                g.addColorStop(0, a); g.addColorStop(1, b);
                return g;
            };

            const data = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.payload) : this.payload;

            const hoverPlugin = {
                id: 'hoverLines',
                afterDatasetsDraw(chart) {
                    const { ctx, chartArea: { top, bottom, left, right } } = chart;
                    const els = chart.getActiveElements();
                    if (!els || els.length === 0) return;

                    const idx = els[0].index;
                    const x = els[0].element.x;

                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(x, top); ctx.lineTo(x, bottom);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,.15)' : 'rgba(0,0,0,.1)';
                    ctx.stroke(); ctx.restore();

                    const items = [];
                    chart.data.datasets.forEach((ds, di) => {
                        const meta = chart.getDatasetMeta(di);
                        if (!chart.isDatasetVisible(di) || meta.hidden) return;
                        const el = meta.data[idx];
                        if (!el) return;
                        items.push({
                            y: el.y, oy: el.y,
                            text: ds.label + ': ' + Number(ds.data[idx]).toLocaleString('id-ID'),
                            color: ds.borderColor
                        });
                    });
                    if (items.length === 0) return;

                    items.sort((a, b) => a.oy - b.oy);
                    const bh = 20, ms = 24;
                    for (let i = 1; i < items.length; i++) {
                        if (items[i].y - items[i-1].y < ms) items[i].y = items[i-1].y + ms;
                    }
                    const maxB = bottom - bh/2 - 4;
                    if (items[items.length-1].y > maxB) {
                        items[items.length-1].y = maxB;
                        for (let i = items.length-2; i >= 0; i--) {
                            if (items[i+1].y - items[i].y < ms) items[i].y = items[i+1].y - ms;
                        }
                    }
                    const minB = top + bh/2 + 4;
                    if (items[0].y < minB) {
                        items[0].y = minB;
                        for (let i = 1; i < items.length; i++) {
                            if (items[i].y - items[i-1].y < ms) items[i].y = items[i-1].y + ms;
                        }
                    }

                    items.forEach(t => {
                        ctx.save();
                        ctx.font = 'bold 10px Inter, sans-serif';
                        const tw = ctx.measureText(t.text).width;
                        const px = 8, iw = 3, ig = 5, bw = tw + px*2 + iw + ig;
                        let bx = x + 10;
                        if (bx + bw > right) bx = x - bw - 10;
                        const by = t.y - bh/2;

                        ctx.beginPath();
                        if (typeof ctx.roundRect === 'function') ctx.roundRect(bx, by, bw, bh, 5);
                        else ctx.rect(bx, by, bw, bh);
                        ctx.fillStyle = document.documentElement.classList.contains('dark') ? 'rgba(15,23,42,.95)' : 'rgba(30,41,59,.9)';
                        ctx.fill();

                        ctx.beginPath();
                        if (typeof ctx.roundRect === 'function') ctx.roundRect(bx+px, by+(bh-10)/2, iw, 10, 1);
                        else ctx.rect(bx+px, by+(bh-10)/2, iw, 10);
                        ctx.fillStyle = t.color; ctx.fill();

                        ctx.fillStyle = '#fff'; ctx.textBaseline = 'middle';
                        ctx.fillText(t.text, bx+px+iw+ig, by+bh/2+.5);
                        ctx.restore();
                    });
                }
            };

            canvas._chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Event', data: data.events, borderColor: '#4f46e5', backgroundColor: mkGrad('rgba(79,70,229,.18)','rgba(79,70,229,.01)'), borderWidth: 2.5, fill: true, tension: 0.42, pointBackgroundColor: '#4f46e5', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 7, yAxisID: 'yL' },
                        { label: 'Peserta', data: data.peserta, borderColor: '#10b981', backgroundColor: mkGrad('rgba(16,185,129,.15)','rgba(16,185,129,.01)'), borderWidth: 2.5, fill: true, tension: 0.42, pointBackgroundColor: '#10b981', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 7, yAxisID: 'yR' },
                        { label: 'Komersial', data: data.komersil, borderColor: '#f59e0b', backgroundColor: 'transparent', borderWidth: 2, borderDash: [6,3], fill: false, tension: 0.42, pointBackgroundColor: '#f59e0b', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, yAxisID: 'yL' },
                        { label: 'Non-Komersial', data: data.nonKom, borderColor: '#0ea5e9', backgroundColor: 'transparent', borderWidth: 2, borderDash: [6,3], fill: false, tension: 0.42, pointBackgroundColor: '#0ea5e9', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, yAxisID: 'yL' }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, ticks: { color: tc, font: { size: 11, weight: '600' } } },
                        yL: { position: 'left', grid: { color: gc, drawBorder: false }, border: { display: false, dash: [4,4] }, ticks: { color: tc, font: { size: 11 }, maxTicksLimit: 6, callback: v => Number(v).toLocaleString('id-ID') } },
                        yR: { position: 'right', grid: { display: false }, border: { display: false }, ticks: { color: '#10b981', font: { size: 11 }, maxTicksLimit: 6, callback: v => Number(v).toLocaleString('id-ID') } }
                    }
                },
                plugins: [hoverPlugin]
            });
        };

        this.$nextTick(() => draw());
    }
}">
    <div class="ech-head">
        <div>
            <div class="ech-title">{{ $chartData['title'] }}</div>
            <div class="ech-sub">{{ $chartData['subtitle'] }}</div>
        </div>
        <div class="ech-pills">
            <span class="ech-pill cursor-pointer select-none" :class="!active[0] && 'inactive'" @click="toggle(0)"><span class="ech-dot" style="background:#4f46e5"></span>Event</span>
            <span class="ech-pill cursor-pointer select-none" :class="!active[1] && 'inactive'" @click="toggle(1)"><span class="ech-dot" style="background:#10b981"></span>Peserta</span>
            <span class="ech-pill cursor-pointer select-none" :class="!active[2] && 'inactive'" @click="toggle(2)" style="color:#f59e0b;border-color:#fde68a;background:#fffbeb">
                <span class="ech-dash" style="color:#f59e0b"></span>Komersial
            </span>
            <span class="ech-pill cursor-pointer select-none" :class="!active[3] && 'inactive'" @click="toggle(3)" style="color:#0ea5e9;border-color:#bae6fd;background:#f0f9ff">
                <span class="ech-dash" style="color:#0ea5e9"></span>Non-Komersial
            </span>
        </div>
    </div>
    <div class="ech-body">
        @if($hasData)
            <div class="ech-canvas-wrap" wire:ignore>
                <canvas x-ref="canvas"></canvas>
            </div>
        @else
            <div class="ech-empty">
                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-gray-300"/>
                <p class="text-gray-400 text-sm font-medium">Data tidak ditemukan pada periode ini</p>
            </div>
        @endif
    </div>
</div>
</x-filament-widgets::widget>
