<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Kesalahan Server | SiREKAP MCC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('errors._styles')
    <style>
        /* Red accent */
        .bg-blob-1, .bg-blob-2 { background: #ef4444; }
        .brand { background: #fef2f2; border: 1px solid #fecaca; }
        .brand-icon { background: #dc2626; }
        .brand-name { color: #b91c1c; }
        .error-code { background-image: linear-gradient(135deg, #dc2626, #f87171); }
        .icon-wrap { background: #fef2f2; border-color: #fee2e2; }
        .icon-wrap svg { color: #dc2626; }
        .btn-primary { background: #dc2626; }
        .btn-primary:hover { background: #b91c1c; }
        .chip-code { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .chip-code .chip-dot { background: #ef4444; }
    </style>
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

        <div class="error-code">500</div>

        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>

        <h1 class="title">Kesalahan Server</h1>
        <p class="subtitle">
            Terjadi kesalahan pada server kami. Tim teknis sedang menangani masalah ini.<br>
            Silakan coba lagi dalam beberapa saat atau <strong>hubungi administrator</strong>.
        </p>

        <div class="divider"></div>

        <div class="btn-group">
            <a href="/" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                Kembali ke Dashboard
            </a>
            <button onclick="location.reload()" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Coba Lagi
            </button>
        </div>

        <div class="chips">
            <span class="chip chip-code">
                <span class="chip-dot"></span>Error 500
            </span>
            <span class="chip">
                <span class="chip-dot" style="background:#9ca3af"></span>
                Internal Server Error
            </span>
        </div>
    </div>

    <p class="footer">&copy; {{ date('Y') }} SiREKAP MCC &mdash; Sistem Rekapitulasi Ekonomi Kreatif</p>
</div>
</body>
</html>
