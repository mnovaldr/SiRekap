<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 — Sesi Berakhir | SiREKAP MCC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('errors._styles')
    <style>
        /* Amber accent */
        .bg-blob-1, .bg-blob-2 { background: #f59e0b; }
        .brand { background: #fffbeb; border: 1px solid #fde68a; }
        .brand-icon { background: #d97706; }
        .brand-name { color: #b45309; }
        .error-code { background-image: linear-gradient(135deg, #d97706, #fbbf24); }
        .icon-wrap { background: #fffbeb; border-color: #fef3c7; }
        .icon-wrap svg { color: #d97706; }
        .btn-primary { background: #d97706; }
        .btn-primary:hover { background: #b45309; }
        .chip-code { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .chip-code .chip-dot { background: #f59e0b; }
    </style>
    <script>
        // Auto redirect ke login setelah 3 detik
        setTimeout(function () {
            window.location.replace('/admin/login?logged_out=1');
        }, 3000);
    </script>
</head>
<body>
<div class="page">
    <div class="bg-grid"></div>
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>

    <div class="card">
        <div class="brand">
            <div class="brand-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </div>
            <span class="brand-name">SiREKAP MCC</span>
        </div>

        <div class="error-code">419</div>

        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="title">Sesi Telah Berakhir</h1>
        <p class="subtitle">
            Token keamanan Anda sudah tidak valid atau sesi telah habis.<br>
            Anda akan diarahkan ke halaman login dalam <strong id="countdown">3</strong> detik.
        </p>

        <div class="divider"></div>

        <div class="btn-group">
            <a href="/admin/login?logged_out=1" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                Login Sekarang
            </a>
            <button onclick="history.back()" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                </svg>
                Halaman Sebelumnya
            </button>
        </div>

        <div class="chips">
            <span class="chip chip-code">
                <span class="chip-dot"></span>Error 419
            </span>
            <span class="chip">
                <span class="chip-dot" style="background:#9ca3af"></span>
                Page Expired
            </span>
        </div>
    </div>

    <p class="footer">&copy; {{ date('Y') }} SiREKAP MCC &mdash; Sistem Rekapitulasi Ekonomi Kreatif</p>
</div>
<script>
    // Countdown timer
    let seconds = 3;
    const el = document.getElementById('countdown');
    const timer = setInterval(function () {
        seconds--;
        if (el) el.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(timer);
            window.location.replace('/admin/login?logged_out=1');
        }
    }, 1000);
</script>
</body>
</html>
