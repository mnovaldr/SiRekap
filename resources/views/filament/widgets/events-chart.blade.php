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
.ech-pill{display:inline-flex;align-items:center;gap:6px;font-size:.72rem;font-weight:600;color:#374151;background:#f9fafb;border:1px solid #e5e7eb;border-radius:999px;padding:4px 10px;white-space:nowrap}
.dark .ech-pill{background:#334155;border-color:#475569;color:#cbd5e1}
.ech-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.ech-dash{width:20px;height:3px;border-radius:2px;flex-shrink:0;background:repeating-linear-gradient(90deg,currentColor 0,currentColor 4px,transparent 4px,transparent 7px)}
.ech-body{padding:24px}
.ech-canvas-wrap{position:relative;height:320px}
.ech-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;height:320px}
</style>

<div class="ech-wrap" wire:key="ech-{{ md5(json_encode($chartPayload)) }}" x-data="{
    payload: @js($chartPayload),
    init() {
        let chartInst = null;
        
        const draw = () => {
            // Jika Chart.js belum tersedia secara global, muat dari CDN
            if (typeof Chart === 'undefined') {
                if (!document.getElementById('__ech_cjs')) {
                    const script = document.createElement('script');
                    script.id = '__ech_cjs';
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
                    script.onload = () => draw();
                    document.head.appendChild(script);
                } else {
                    setTimeout(draw, 100);
                }
                return;
            }
            
            const canvas = this.$refs.canvas;
            if (!canvas) return;
            
            if (chartInst) {
                chartInst.destroy();
            }
            
            const ctx = canvas.getContext('2d');
            const dark = document.documentElement.classList.contains('dark');
            const grid = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
            const tick = dark ? '#6b7280' : '#9ca3af';
            
            const mkGrad = (a, b) => {
                const g = ctx.createLinearGradient(0, 0, 0, 320);
                g.addColorStop(0, a);
                g.addColorStop(1, b);
                return g;
            };

            // Menggunakan Alpine.raw() untuk mencegah Proxy merusak Chart.js (penyebab a.label.call is not a function)
            const data = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.payload) : this.payload;

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

            chartInst = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Event',
                            data: data.events,
                            borderColor: '#4f46e5',
                            backgroundColor: mkGrad('rgba(79,70,229,.18)', 'rgba(79,70,229,.01)'),
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.42,
                            pointBackgroundColor: '#4f46e5',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            yAxisID: 'yL',
                        },
                        {
                            label: 'Peserta',
                            data: data.peserta,
                            borderColor: '#10b981',
                            backgroundColor: mkGrad('rgba(16,185,129,.15)', 'rgba(16,185,129,.01)'),
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.42,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            yAxisID: 'yR',
                        },
                        {
                            label: 'Komersial',
                            data: data.komersil,
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
                            yAxisID: 'yL',
                        },
                        {
                            label: 'Non-Komersial',
                            data: data.nonKom,
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
                            yAxisID: 'yL',
                        }
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
                            ticks: { color: tick, font: { size: 11, weight: '600' } }
                        },
                        yL: {
                            position: 'left',
                            grid: { color: grid, drawBorder: false },
                            border: { display: false, dash: [4, 4] },
                            ticks: {
                                color: tick,
                                font: { size: 11 },
                                maxTicksLimit: 6,
                                callback: function(v) { return Number(v).toLocaleString('id-ID'); }
                            }
                        },
                        yR: {
                            position: 'right',
                            grid: { display: false },
                            border: { display: false },
                            ticks: {
                                color: '#10b981',
                                font: { size: 11 },
                                maxTicksLimit: 6,
                                callback: function(v) { return Number(v).toLocaleString('id-ID'); }
                            }
                        }
                    }
                },
                plugins: [hoverLabelsPlugin]
            });
        };
        
        this.$nextTick(() => {
            draw();
        });
    }
}">
    <div class="ech-head">
        <div>
            <div class="ech-title">{{ $chartData['title'] }}</div>
            <div class="ech-sub">{{ $chartData['subtitle'] }}</div>
        </div>
        <div class="ech-pills">
            <span class="ech-pill"><span class="ech-dot" style="background:#4f46e5"></span>Event</span>
            <span class="ech-pill"><span class="ech-dot" style="background:#10b981"></span>Peserta</span>
            <span class="ech-pill" style="color:#f59e0b;border-color:#fde68a;background:#fffbeb">
                <span class="ech-dash" style="color:#f59e0b"></span>Komersial
            </span>
            <span class="ech-pill" style="color:#0ea5e9;border-color:#bae6fd;background:#f0f9ff">
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
