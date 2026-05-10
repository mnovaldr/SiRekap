@php $chart = $this->getChartData(); @endphp

<x-filament-widgets::widget>
<style>
.ech-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    transition: box-shadow .2s;
}
.dark .ech-card {
    background: #1e293b;
    border-color: #334155;
}
.ech-card:hover { box-shadow: 0 4px 24px rgba(0,0,0,.08); }

.ech-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.dark .ech-header { border-bottom-color: #1e293b; }

.ech-title    { font-size: 1rem; font-weight: 700; color: #111827; }
.dark .ech-title { color: #f1f5f9; }
.ech-subtitle { font-size: .8rem; color: #9ca3af; margin-top: 2px; }

.ech-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.ech-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .72rem;
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    padding: 4px 10px;
    white-space: nowrap;
}
.dark .ech-pill { background: #334155; border-color: #475569; color: #cbd5e1; }
.ech-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ech-dot-dashed {
    width: 20px; height: 3px; border-radius: 2px; flex-shrink: 0;
    background: repeating-linear-gradient(90deg, currentColor 0, currentColor 4px, transparent 4px, transparent 7px);
}

.ech-body { padding: 24px; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush

<div 
    class="ech-card"
    wire:key="ech_card_{{ $this->getId() }}_{{ md5(json_encode($chart)) }}"
    x-data="eventsChartComponent"
>



    <div class="ech-header">
        <div>
            <div class="ech-title">{{ $chart['title'] }}</div>
            <div class="ech-subtitle">{{ $chart['subtitle'] }}</div>
        </div>
        <div class="ech-legend">
            <span class="ech-pill"><span class="ech-dot" style="background:#4f46e5"></span>Event</span>
            <span class="ech-pill"><span class="ech-dot" style="background:#10b981"></span>Peserta</span>
            <span class="ech-pill" style="color:#f59e0b;border-color:#fde68a;background:#fffbeb;">
                <span class="ech-dot-dashed" style="color:#f59e0b"></span>Komersial
            </span>
            <span class="ech-pill" style="color:#6366f1;border-color:#c7d2fe;background:#eef2ff;">
                <span class="ech-dot-dashed" style="color:#6366f1"></span>Non-Komersial
            </span>
        </div>
    </div>
    <div class="ech-body">
        <div style="position:relative; height:320px;">
            <canvas x-ref="canvas"></canvas>
            
            {{-- Placeholder jika data kosong --}}
            <div 
                x-show="!hasData" 
                style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.7); z-index:10;"
                class="dark:bg-gray-800/70"
            >
                <div class="text-center">
                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                    <p class="text-gray-400 text-sm font-medium">Data tidak ditemukan pada periode ini</p>
                </div>
            </div>
        </div>
    </div>

    <div x-ref="data" style="display:none;"
         data-labels='@json($chart['labels'])'
         data-events='@json($chart['events'])'
         data-peserta='@json($chart['peserta'])'
         data-komersil='@json($chart['komersil'])'
         data-nonkom='@json($chart['nonKom'])'>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('eventsChartComponent', () => ({
            chart: null,
            hasData: true,
            init() {
                this.$nextTick(() => this.render());
            },
            render() {
                if (typeof Chart === 'undefined') {
                    setTimeout(() => this.render(), 300);
                    return;
                }

                const canvas = this.$refs.canvas;
                const dataEl = this.$refs.data;
                if (!canvas || !dataEl) return;

                const labels   = JSON.parse(dataEl.getAttribute('data-labels') || '[]');
                const events   = JSON.parse(dataEl.getAttribute('data-events') || '[]');
                const peserta  = JSON.parse(dataEl.getAttribute('data-peserta') || '[]');
                const komersil = JSON.parse(dataEl.getAttribute('data-komersil') || '[]');
                const nonKom   = JSON.parse(dataEl.getAttribute('data-nonkom') || '[]');

                // Cek apakah ada data yang bernilai > 0
                this.hasData = events.some(v => v > 0) || peserta.some(v => v > 0);

                if (this.chart) this.chart.destroy();
                if (!this.hasData) return;

                const isDark     = document.documentElement.classList.contains('dark');
                const gridColor  = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
                const tickColor  = isDark ? '#6b7280' : '#9ca3af';
                const tooltipBg  = isDark ? '#1f2937' : '#ffffff';
                const tooltipBdr = isDark ? '#374151' : '#e5e7eb';
                const tooltipTxt = isDark ? '#f9fafb' : '#111827';

                const ctx = canvas.getContext('2d');

                function grad(colorTop, colorBot) {
                    const g = ctx.createLinearGradient(0, 0, 0, 320);
                    g.addColorStop(0, colorTop);
                    g.addColorStop(1, colorBot);
                    return g;
                }

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Event',
                                data: events,
                                borderColor: '#4f46e5',
                                backgroundColor: grad('rgba(79,70,229,.18)', 'rgba(79,70,229,.01)'),
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
                                backgroundColor: grad('rgba(16,185,129,.15)', 'rgba(16,185,129,.01)'),
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
                                borderColor: '#6366f1',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                borderDash: [6, 3],
                                fill: false,
                                tension: 0.42,
                                pointBackgroundColor: '#6366f1',
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
                                backgroundColor: tooltipBg,
                                borderColor: tooltipBdr,
                                borderWidth: 1,
                                titleColor: tooltipTxt,
                                bodyColor: tickColor,
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(ctx) {
                                        return '  ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('id-ID');
                                    }
                                }
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
                    }
                });
            }
        }));
    });
</script>


</x-filament-widgets::widget>
