<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 — Layanan Tidak Tersedia | SiREKAP MCC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('errors._styles')
    <style>
        /* Indigo accent */
        .bg-blob-1, .bg-blob-2 { background: #6366f1; }
        .brand { background: #eef2ff; border: 1px solid #c7d2fe; }
        .brand-icon { background: #4f46e5; }
        .brand-name { color: #4338ca; }
        .error-code { background-image: linear-gradient(135deg, #4f46e5, #818cf8); }
        .icon-wrap { background: #eef2ff; border-color: #e0e7ff; }
        .icon-wrap svg { color: #4f46e5; }
        .btn-primary { background: #4f46e5; }
        .btn-primary:hover { background: #4338ca; }
        .chip-code { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
        .chip-code .chip-dot { background: #6366f1; }

        /* Maintenance pulse */
        .pulse-dot {
            display: inline-block;
            width: 8px; height: 8px; border-radius: 50%;
            background: #4f46e5; margin-right: 6px;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .4; transform: scale(.8); }
        }
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

        <div class="error-code">503</div>

        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
            </svg>
        </div>

        <h1 class="title">Sedang Dalam Pemeliharaan</h1>
        <p class="subtitle">
            Sistem SiREKAP MCC sedang dalam pemeliharaan terjadwal.<br>
            Kami akan segera kembali. Terima kasih atas <strong>kesabaran Anda</strong>.
        </p>

        <div class="divider"></div>

        <div class="btn-group">
            <button onclick="location.reload()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Coba Lagi
            </button>
        </div>

        <div class="chips">
            <span class="chip chip-code">
                <span class="chip-dot"></span>Error 503
            </span>
            <span class="chip" style="background:#eef2ff; border-color:#c7d2fe; color:#4338ca;">
                <span class="pulse-dot"></span>Maintenance
            </span>
        </div>
    </div>

    <p class="footer">&copy; {{ date('Y') }} SiREKAP MCC &mdash; Sistem Rekapitulasi Ekonomi Kreatif</p>
</div>
</body>
</html>
