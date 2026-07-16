<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Presentasi Kunjungan - SiREKAP MCC</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * { font-family: 'Inter', sans-serif; }

        /* GPU Hardware Acceleration Fixes for Smart TVs */
        .sdg-card-item {
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            -webkit-transform: translateZ(0);
            transform: translateZ(0);
        }

        /* ============================
           LIGHT MODE (DEFAULT)
        ============================ */
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            transition: background-color 0.4s ease, color 0.4s ease;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 4px rgba(15,23,42,0.06), 0 4px 16px rgba(15,23,42,0.04);
            border-radius: 16px;
            transition: background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
        }
        .stat-badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
        }
        .progress-track {
            background: #e2e8f0;
        }
        .section-subtitle { color: #64748b; }
        .section-title { color: #0f172a; }
        .label-text { color: #374151; }
        .value-text { color: #111827; }
        .divider { border-color: #e2e8f0; }

        /* ============================
           DARK MODE
        ============================ */
        .dark body {
            background-color: #0d1117;
            color: #e2e8f0;
        }
        .dark .card {
            background: rgba(22, 27, 38, 0.95);
            border: 1px solid rgba(99, 102, 241, 0.12);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.02), 0 8px 40px rgba(0,0,0,0.4);
        }
        .dark .stat-badge {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: #94a3b8;
        }
        .dark .progress-track {
            background: rgba(255,255,255,0.06);
        }
        .dark .section-subtitle { color: #94a3b8; }
        .dark .section-title { color: #f1f5f9; }
        .dark .label-text { color: #cbd5e1; }
        .dark .value-text { color: #f8fafc; }
        .dark .divider { border-color: rgba(255,255,255,0.07); }

        /* Grid accent lines in dark mode */
        .dark body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                radial-gradient(ellipse at 15% 20%, rgba(99, 102, 241, 0.07) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 55%);
            z-index: 0;
        }

        /* ============================
           SCROLLBAR
        ============================ */
        .scroll::-webkit-scrollbar { width: 4px; }
        .scroll::-webkit-scrollbar-track { background: transparent; }
        .scroll::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 4px; }

        /* ============================
           ANIMATIONS
        ============================ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeInUp 0.5s ease forwards; }

        @keyframes shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #e2e8f0 25%, #f8fafc 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 6px;
        }
        .dark .skeleton {
            background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Progress bar fill animation */
        .bar-fill {
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Theme toggle transition */
        #theme-toggle {
            transition: all 0.25s ease;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col p-4 md:p-5 overflow-y-auto md:overflow-hidden xl:overflow-y-auto relative">

    <!-- ===== HEADER ===== -->
    <header class="card flex flex-wrap items-center justify-between px-4 md:px-6 py-4 mb-5 relative z-10 gap-4">
        <div class="flex items-center gap-4">
            <img id="logo-img" src="/images/logo.png" alt="Logo MCC" class="h-10 object-contain transition-all duration-300">
            <div class="h-9 w-px divider border-l"></div>
            <div>
                <p class="text-[10px] font-semibold tracking-[0.15em] uppercase text-indigo-600 dark:text-indigo-400">SiREKAP MCC</p>
                <h1 class="section-title text-xl font-bold leading-tight">{{ $label }}</h1>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <!-- Clock -->
            <div class="text-right">
                <div id="realtime-clock" class="font-mono font-bold text-base section-title tabular-nums">00:00:00</div>
                <div id="realtime-date" class="text-[10px] section-subtitle font-medium tracking-wide"></div>
            </div>

            <div class="h-8 w-px divider border-l hidden md:block"></div>

            <!-- Theme Toggle -->
            <button id="theme-toggle"
                class="p-2.5 rounded-xl border stat-badge hover:scale-105 active:scale-95 focus:outline-none"
                title="Toggle tema">
                <svg id="icon-moon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg id="icon-sun" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="flex-1 flex flex-col gap-3 relative z-10 min-h-0">
        <!-- TOP ROW WRAPPER -->
        <div class="flex flex-col md:flex-row gap-3 shrink-0 min-h-0 xl:h-[280px]">
            <!-- KOLOM ATAS KIRI: Donut Chart Sub-Sektor Ekraf -->
            <section class="card w-full md:w-2/3 p-4 flex flex-col min-h-0">
            <!-- Section Header -->
            <div class="flex items-start justify-between mb-5">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="h-4 w-1 rounded-full bg-blue-500"></div>
                        <h2 class="section-title text-sm font-bold uppercase tracking-wide">Sub-Sektor Ekraf</h2>
                    </div>
                    <p id="ekraf-subtitle" class="section-subtitle text-xs ml-3">Distribusi ekonomi kreatif aktif di MCC</p>
                </div>
            </div>

            <!-- Wrapper Flex untuk merespons laptop (xl) -->
            <div class="flex-1 flex flex-col justify-center xl:justify-start xl:flex-row min-h-0 relative z-10 xl:items-start xl:pt-2 gap-y-8 xl:gap-y-0">
                <!-- Donut Chart -->
                <div class="w-full xl:w-[45%] h-[200px] md:h-[260px] xl:h-[180px] shrink-0 flex items-center justify-center relative">
                    <canvas id="ekraf-chart"></canvas>
                    <div id="ekraf-center-text" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span id="ekraf-total-count" class="section-title text-2xl font-black">—</span>
                        <span class="section-subtitle text-[10px] font-medium uppercase tracking-widest">Sub-Sektor</span>
                    </div>
                </div>

                <!-- Custom Legend -->
                <div id="ekraf-legend" class="mt-4 xl:mt-0 xl:w-[55%] flex flex-wrap justify-center xl:justify-start content-start gap-x-5 gap-y-2 overflow-y-auto flex-1 min-h-0">
                    <!-- Skeleton loaders -->
                    <div class="skeleton h-4 w-24"></div>
                    <div class="skeleton h-4 w-32"></div>
                    <div class="skeleton h-4 w-20"></div>
                    <div class="skeleton h-4 w-28"></div>
                    <div class="skeleton h-4 w-24"></div>
                </div>
            </div>
            </section>

            <!-- KOLOM ATAS KANAN: Utilitas Ruangan -->
            <section class="card w-full md:w-1/3 p-4 flex flex-col min-h-0 h-[300px] md:h-auto">
            <div class="mb-5">
                <div class="flex items-center gap-2 mb-1">
                    <div class="h-4 w-1 rounded-full bg-violet-500"></div>
                    <h2 class="section-title text-sm font-bold uppercase tracking-wide">Utilitas Ruangan</h2>
                </div>
                <p id="lokasi-subtitle" class="section-subtitle text-xs ml-3">Efektivitas penggunaan ruangan MCC</p>
            </div>

            <div id="lokasi-container" class="flex-1 space-y-4 overflow-y-auto scroll pr-1">
                <!-- Skeletons -->
                <div class="skeleton h-8 w-full rounded-xl"></div>
                <div class="skeleton h-8 w-full rounded-xl"></div>
                <div class="skeleton h-8 w-5/6 rounded-xl"></div>
                <div class="skeleton h-8 w-4/6 rounded-xl"></div>
            </div>
            </section>
        </div>

        <!-- KOLOM BAWAH: Progress Bars SDGs -->
        <section class="card shrink-0 p-4 flex flex-col min-h-0 overflow-hidden">
            <div class="flex items-start justify-between mb-5 shrink-0">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="h-4 w-1 rounded-full bg-emerald-500"></div>
                        <h2 class="section-title text-sm font-bold uppercase tracking-wide">Dampak SDGs</h2>
                    </div>
                    <p id="sdgs-subtitle" class="section-subtitle text-xs ml-3">Kontribusi aktivitas terhadap pencapaian SDGs</p>
                </div>
            </div>

            <!-- SDGs Marquee Wrapper -->
            <div class="h-[280px] xl:h-[190px] overflow-hidden relative w-full sdg-slider-container">
                <div id="sdgs-container" class="flex gap-4 h-full absolute top-0 left-0 items-center transition-transform duration-[30ms] ease-linear" style="transform: translateX(0px);">
                    <!-- Skeletons -->
                    <div class="skeleton h-full w-[250px] shrink-0 rounded-xl"></div>
                    <div class="skeleton h-full w-[250px] shrink-0 rounded-xl"></div>
                    <div class="skeleton h-full w-[250px] shrink-0 rounded-xl"></div>
                    <div class="skeleton h-full w-[250px] shrink-0 rounded-xl"></div>
                </div>
            </div>
        </section>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="card flex flex-col md:flex-row items-center justify-between px-4 md:px-6 py-3 mt-5 relative z-10 gap-2 md:gap-0 text-center md:text-left">
        <div class="flex items-center gap-2 text-xs section-subtitle">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Sistem Terhubung</span>
        </div>
        <div class="text-xs section-subtitle font-medium">
            © 2026 IT Malang Creative Center (MCC)
        </div>
    </footer>

    <!-- ===== SCRIPTS ===== -->
    <script>
    (() => {
        // ==========================================
        // CONFIG
        // ==========================================
        const TOKEN = "{{ $token }}";
        let ekrafChart = null;

        const PALETTE = [
            '#6366f1','#10b981','#f59e0b','#3b82f6','#ec4899',
            '#06b6d4','#84cc16','#f43f5e','#a855f7','#14b8a6','#fb923c'
        ];

        // SDGs color map
        const SDG_COLORS = {
            '1':  '#e5243b', '2':  '#dda63a', '3':  '#4c9f38',
            '4':  '#c5192d', '5':  '#ff3a21', '6':  '#26bde2',
            '7':  '#fcc30b', '8':  '#a21942', '9':  '#fd6925',
            '10': '#dd1367', '11': '#fd9d24', '12': '#bf8b2e',
            '13': '#3f7e44', '14': '#0a97d9', '15': '#56c02b',
            '16': '#00689d', '17': '#19486a',
        };

        // SDGs description map
        const SDG_DESC = {
            '1':  'Tanpa Kemiskinan', '2':  'Tanpa Kelaparan', '3':  'Kehidupan Sehat & Sejahtera',
            '4':  'Pendidikan Berkualitas', '5':  'Kesetaraan Gender', '6':  'Air Bersih & Sanitasi',
            '7':  'Energi Bersih & Terjangkau', '8':  'Pekerjaan Layak & Pertumbuhan Ekonomi', '9':  'Industri, Inovasi & Infrastruktur',
            '10': 'Berkurangnya Kesenjangan', '11': 'Kota & Pemukiman Berkelanjutan', '12': 'Konsumsi & Produksi Bertanggung Jawab',
            '13': 'Penanganan Perubahan Iklim', '14': 'Ekosistem Lautan', '15': 'Ekosistem Daratan',
            '16': 'Perdamaian, Keadilan & Kelembagaan', '17': 'Kemitraan untuk Mencapai Tujuan'
        };

        function getSdgColor(label) {
            const match = label.match(/\d+/);
            return match ? (SDG_COLORS[match[0]] ?? '#6366f1') : '#6366f1';
        }

        // ==========================================
        // THEME MANAGEMENT
        // ==========================================
        const html    = document.documentElement;
        const logoImg = document.getElementById('logo-img');
        const iconMoon= document.getElementById('icon-moon');
        const iconSun = document.getElementById('icon-sun');
        const themeBtn= document.getElementById('theme-toggle');

        function getTheme() {
            return localStorage.getItem('color-theme') ?? 'light';
        }

        function applyTheme(theme) {
            localStorage.setItem('color-theme', theme);
            if (theme === 'dark') {
                html.classList.add('dark');
                logoImg.src = '/images/logo-white.png';
                iconMoon.classList.add('hidden');
                iconSun.classList.remove('hidden');
            } else {
                html.classList.remove('dark');
                logoImg.src = '/images/logo.png';
                iconSun.classList.add('hidden');
                iconMoon.classList.remove('hidden');
            }
        }

        applyTheme(getTheme());

        themeBtn.addEventListener('click', () => {
            const next = getTheme() === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            // Re-render charts so colors update
            if (ekrafChart) {
                const isDark = next === 'dark';
                ekrafChart.options.plugins.tooltip.backgroundColor = isDark ? '#1e293b' : '#ffffff';
                ekrafChart.options.plugins.tooltip.titleColor = isDark ? '#f1f5f9' : '#0f172a';
                ekrafChart.options.plugins.tooltip.bodyColor  = isDark ? '#94a3b8' : '#475569';
                ekrafChart.update();
            }
        });

        // ==========================================
        // REAL-TIME CLOCK
        // ==========================================
        const clockEl= document.getElementById('realtime-clock');
        const dateEl = document.getElementById('realtime-date');

        function tick() {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
            }) + ' WIB';
            dateEl.textContent = now.toLocaleDateString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            });
        }
        tick();
        setInterval(tick, 1000);

        // ==========================================
        // DATA FETCHING
        // ==========================================
        let consecutiveErrors = 0;

        async function fetchStats() {
            try {
                let fetchOptions = {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                };

                let timeoutId;
                if (typeof AbortController !== 'undefined') {
                    const controller = new AbortController();
                    timeoutId = setTimeout(() => controller.abort(), 15000);
                    fetchOptions.signal = controller.signal;
                }

                const res = await fetch(`/api/public/stats?token=${TOKEN}`, fetchOptions);
                if (timeoutId) clearTimeout(timeoutId);

                if (res.status === 403) {
                    // Token expired/invalid — tampilkan pesan, jangan reload
                    showErrorState('⛔ Link presentasi telah kedaluwarsa atau dinonaktifkan.', false);
                    return;
                }

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }

                consecutiveErrors = 0;
                const data = await res.json();
                
                if (data.ekraf_year_text) {
                    document.getElementById('ekraf-subtitle').innerText = `Distribusi ekonomi kreatif aktif di MCC (${data.ekraf_year_text})`;
                }
                if (data.lokasi_year_text) {
                    document.getElementById('lokasi-subtitle').innerText = `Efektivitas penggunaan ruangan MCC (${data.lokasi_year_text})`;
                }
                if (data.sdgs_year_text) {
                    document.getElementById('sdgs-subtitle').innerText = `Kontribusi aktivitas terhadap pencapaian SDGs (${data.sdgs_year_text})`;
                }

                renderAll(data);
            } catch (e) {
                consecutiveErrors++;
                console.warn(`Fetch error #${consecutiveErrors}:`, e.message);

                if (consecutiveErrors >= 5) {
                    // Setelah 5 kegagalan berturut-turut, tampilkan pesan
                    showErrorState('⚠️ Koneksi terputus. Halaman akan mencoba terhubung kembali...');
                }
                // Tidak reload — biarkan setInterval mencoba ulang
            }
        }

        function showErrorState(msg, canRetry = true) {
            const containers = ['sdgs-container', 'lokasi-container', 'ekraf-legend'];
            containers.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = `<div class="section-subtitle text-xs text-center py-6">${msg}</div>`;
            });
        }

        fetchStats();

        // Auto-refresh khusus digisign karena dipasang di TV/Layar tanpa interaksi
        setInterval(fetchStats, 60000);

        // Auto-scroll logic for Digisign (non-touchscreen)
        function autoScroll() {
            const scrollables = document.querySelectorAll('.scroll');
            scrollables.forEach(el => {
                // Only scroll if content is taller than container
                if (el.scrollHeight > el.clientHeight) {
                    // Check if we reached the bottom (add 2px margin of error)
                    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 2) {
                        // Pause at bottom, then reset to top
                        if (!el.dataset.paused) {
                            el.dataset.paused = "true";
                            setTimeout(() => {
                                el.scrollTop = 0;
                                setTimeout(() => { el.dataset.paused = ""; }, 1500); // Pause at top
                            }, 3000);
                        }
                    } else {
                        // Scroll down if not paused
                        if (!el.dataset.paused) {
                            el.scrollTop += 1;
                        }
                    }
                }
            });
        }
          
        // Start auto-scroll after a short delay
        setTimeout(() => {
            setInterval(autoScroll, 50);
        }, 3000);

        // ==========================================
        // RENDER ALL
        // ==========================================
        function renderAll(data) {
            renderEkraf(data.ekraf ?? []);
            renderSdgs(data.sdgs ?? []);
            renderLokasi(data.lokasi ?? []);
        }

        // ------------------------------------------
        // 1. EKRAF DONUT CHART
        // ------------------------------------------
        function renderEkraf(items) {
            const labels  = items.map(i => i.sub_sektor);
            const values  = items.map(i => i.percentage);
            const bgColors= items.map((_, idx) => PALETTE[idx % PALETTE.length]);
            const isDark  = getTheme() === 'dark';

            const ctx = document.getElementById('ekraf-chart').getContext('2d');

            if (ekrafChart) ekrafChart.destroy();

            ekrafChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: bgColors,
                        borderColor: isDark ? '#161b26' : '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 12,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#1e293b' : '#ffffff',
                            titleColor: isDark ? '#f1f5f9' : '#0f172a',
                            bodyColor:  isDark ? '#94a3b8' : '#475569',
                            borderColor: isDark ? 'rgba(99,102,241,0.2)' : '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw}%`
                            }
                        }
                    },
                    animation: { duration: 800, easing: 'easeOutQuart' }
                }
            });

            // Center total count
            document.getElementById('ekraf-total-count').textContent = items.length;

            // Custom Legend
            const legendEl = document.getElementById('ekraf-legend');
            legendEl.innerHTML = '';
            items.forEach((item, idx) => {
                const color = PALETTE[idx % PALETTE.length];
                const el = document.createElement('div');
                el.className = 'flex items-center gap-1.5 animate-in';
                el.style.animationDelay = `${idx * 40}ms`;
                // Display absolute count if available, otherwise display percentage
                const displayValue = item.count ? item.count.toLocaleString() : item.percentage + '%';
                
                el.innerHTML = `
                    <span class="h-3 w-3 rounded-full shrink-0" style="background:${color}"></span>
                    <span class="text-[11px] whitespace-nowrap text-slate-800 font-semibold" style="color: #1e293b;">
                        ${item.sub_sektor} <span class="font-bold opacity-100 tabular-nums text-slate-900">(${displayValue})</span>
                    </span>
                `;
                legendEl.appendChild(el);
            });
        }

        // ------------------------------------------
        // 2. SDGs CAROUSEL
        // ------------------------------------------
        const SDG_ICONS = {
            1: 'fa-solid fa-users',
            2: 'fa-solid fa-bowl-food',
            3: 'fa-solid fa-heart-pulse',
            4: 'fa-solid fa-book-open',
            5: 'fa-solid fa-venus-mars',
            6: 'fa-solid fa-droplet',
            7: 'fa-solid fa-sun',
            8: 'fa-solid fa-arrow-trend-up',
            9: 'fa-solid fa-industry',
            10: 'fa-solid fa-scale-balanced',
            11: 'fa-solid fa-city',
            12: 'fa-solid fa-recycle',
            13: 'fa-solid fa-earth-americas',
            14: 'fa-solid fa-fish',
            15: 'fa-solid fa-tree',
            16: 'fa-solid fa-dove',
            17: 'fa-solid fa-handshake'
        };

        const SDG_COLORS_HEX = {
            1: '#e5243b', 2: '#dda63a', 3: '#4c9f38', 4: '#c5192d', 5: '#ff3a21',
            6: '#26bde2', 7: '#fcc30b', 8: '#a21942', 9: '#fd6925', 10: '#dd1367',
            11: '#fd9d24', 12: '#bf8b2e', 13: '#3f7e44', 14: '#0a97d9', 15: '#56c02b',
            16: '#00689d', 17: '#19486a'
        };
        
        let currentTranslateX = 0;
        let marqueeInterval = null;

        function renderSdgs(items) {
            const el = document.getElementById('sdgs-container');
            if (!items.length) {
                el.innerHTML = `<div class="section-subtitle text-xs text-center py-10 w-full">Belum ada data SDGs</div>`;
                return;
            }
            el.innerHTML = '';
            
            const defaultIcon = "{{ asset('images/icon-1.png') }}";

            // Sort items by SDG number (1 to 17)
            const sortedItems = [...items].sort((a, b) => {
                const numA = parseInt((a.program.match(/\d+/) || [99])[0]);
                const numB = parseInt((b.program.match(/\d+/) || [99])[0]);
                return numA - numB;
            });

            // Generate cards HTML
            let cardsHTML = '';
            sortedItems.forEach((item) => {
                const match = item.program.match(/\d+/);
                const sdgNum = match ? parseInt(match[0]) : 1;
                const bgColor = SDG_COLORS_HEX[sdgNum] || '#000000';
                const title = SDG_DESC[sdgNum] || item.program;
                const pct = Math.min(item.percentage, 100);
                
                // Gunakan file .jpe berbahasa Indonesia dengan format Goal-1.jpe
                const imagePath = `{{ asset('images/SDG-Icons') }}/Goal-${sdgNum}.jpe`;

                cardsHTML += `
                    <div class="sdg-card-item shrink-0 w-[220px] xl:w-[140px] bg-white rounded-xl overflow-hidden relative flex flex-col shadow-lg border border-gray-100">
                        <img src="${imagePath}" class="w-full aspect-square object-cover" alt="SDG ${sdgNum} - ${title}">
                        
                        <!-- Progress Bar Section -->
                        <div class="p-3 xl:p-2 bg-white">
                            <div class="flex justify-between items-end mb-2 xl:mb-1">
                                <span class="text-[9px] xl:text-[7px] font-bold text-gray-400 uppercase tracking-wider">Pencapaian</span>
                                <span class="font-black text-xl xl:text-base leading-none" style="color: ${bgColor};">${pct}%</span>
                            </div>
                            <div class="w-full bg-gray-100 h-2 xl:h-1 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000" style="width: ${pct}%; background-color: ${bgColor};"></div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            // Duplicate items to create seamless loop
            el.innerHTML = cardsHTML + cardsHTML;

            // Start or restart horizontal auto scroll
            setTimeout(() => {
                if (marqueeInterval) clearInterval(marqueeInterval);
                const scrollStep = 1; 
                marqueeInterval = setInterval(() => {
                    const halfWidth = el.scrollWidth / 2;
                    if (Math.abs(currentTranslateX) >= halfWidth) {
                        currentTranslateX = 0; // reset to beginning seamlessly
                    } else {
                        currentTranslateX -= scrollStep;
                    }
                    el.style.transform = `translateX(${currentTranslateX}px)`;
                }, 30);
            }, 100);
        }

        // ------------------------------------------
        // 3. UTILITAS RUANGAN
        // ------------------------------------------
        function renderLokasi(items) {
            const el = document.getElementById('lokasi-container');
            if (!items.length) {
                el.innerHTML = `<div class="section-subtitle text-xs text-center py-10">Belum ada data ruangan</div>`;
                return;
            }
            el.innerHTML = '';
            items.forEach((item, idx) => {
                const pct   = Math.min(item.percentage, 100);
                const color = pct >= 80 ? '#10b981' : pct >= 50 ? '#6366f1' : '#f59e0b';
                const row   = document.createElement('div');
                row.className = 'animate-in';
                row.style.animationDelay = `${idx * 50}ms`;
                row.innerHTML = `
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="label-text text-xs font-medium truncate max-w-[72%]">${item.lokasi}</span>
                        <span class="text-xs font-black tabular-nums" style="color:${color}">${pct}%</span>
                    </div>
                    <div class="progress-track w-full rounded-full h-1.5 overflow-hidden">
                        <div class="bar-fill h-1.5 rounded-full" style="width:0%;background:${color};"
                             data-target="${pct}"></div>
                    </div>
                `;
                el.appendChild(row);
            });
            requestAnimationFrame(() => {
                el.querySelectorAll('[data-target]').forEach(bar => {
                    bar.style.width = bar.dataset.target + '%';
                });
            });
        }

    })();
    </script>
</body>
</html>
