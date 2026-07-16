<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Kedaluwarsa - SiREKAP MCC</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen text-slate-100 p-6">
    <div class="max-w-md w-full bg-slate-900/60 border border-slate-800 rounded-2xl p-8 text-center backdrop-blur-md shadow-2xl">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-500/10 text-red-500 mb-6">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">Akses Ditolak</h1>
        <p class="text-slate-400 text-sm mb-6">{{ $message ?? 'Link presentasi telah kedaluwarsa atau dinonaktifkan.' }}</p>
        <div class="border-t border-slate-800 pt-6">
            <p class="text-xs text-slate-500">Sistem Informasi Rekapitulasi Event (SiREKAP MCC)</p>
        </div>
    </div>
</body>
</html>
