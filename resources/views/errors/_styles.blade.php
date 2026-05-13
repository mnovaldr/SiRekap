<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        height: 100%;
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        background: #f9fafb;
        color: #111827;
        -webkit-font-smoothing: antialiased;
    }
    .page {
        min-height: 100vh;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 24px; position: relative; overflow: hidden;
    }
    .bg-blob {
        position: absolute; border-radius: 50%;
        filter: blur(80px); opacity: 0.12; pointer-events: none;
    }
    .bg-blob-1 { width: 600px; height: 600px; top: -200px; right: -150px; }
    .bg-blob-2 { width: 400px; height: 400px; bottom: -100px; left: -100px; }
    .bg-grid {
        position: absolute; inset: 0;
        background-image:
            linear-gradient(#e5e7eb 1px, transparent 1px),
            linear-gradient(90deg, #e5e7eb 1px, transparent 1px);
        background-size: 40px 40px; opacity: 0.4; pointer-events: none;
    }
    .card {
        position: relative; z-index: 1;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 24px;
        padding: 48px 40px; max-width: 520px; width: 100%; text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 8px 32px rgba(0,0,0,.07);
        display: flex; flex-direction: column; align-items: center;
        animation: fadeUp .4s ease both;
    }
    .brand {
        display: inline-flex; align-items: center; gap: 10px;
        margin-bottom: 20px; padding: 8px 16px;
        border-radius: 999px;
    }
    .brand-icon {
        width: 28px; height: 28px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .brand-icon svg { width: 16px; height: 16px; color: #fff; }
    .brand-name { font-size: .85rem; font-weight: 700; letter-spacing: .02em; }
    .error-code {
        font-size: 7rem; font-weight: 900; line-height: 1;
        letter-spacing: -.05em; margin-bottom: 20px;
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .icon-wrap {
        width: 72px; height: 72px; border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 24px; flex-shrink: 0;
        animation: float 4s ease-in-out infinite;
        border-width: 2px; border-style: solid;
    }
    .icon-wrap svg { width: 36px; height: 36px; }
    .title {
        font-size: 1.5rem; font-weight: 800; color: #111827;
        margin-bottom: 10px; letter-spacing: -.02em;
    }
    .subtitle {
        font-size: .9rem; color: #6b7280; line-height: 1.6; margin-bottom: 32px;
    }
    .subtitle strong { color: #374151; font-weight: 600; }
    .divider { width: calc(100% + 80px); height: 1px; background: #f3f4f6; margin-bottom: 28px; }
    .btn-group { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; width: 100%; }
    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 10px;
        font-size: .875rem; font-weight: 600; text-decoration: none;
        transition: all .15s ease; cursor: pointer; border: none; font-family: inherit;
    }
    .btn svg { width: 16px; height: 16px; flex-shrink: 0; }
    .btn-primary { color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.15), 0 4px 12px rgba(0,0,0,.1); }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,.2), 0 6px 16px rgba(0,0,0,.12); }
    .btn-secondary {
        background: #fff; color: #374151;
        border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .btn-secondary:hover { background: #f9fafb; border-color: #d1d5db; transform: translateY(-1px); }
    .chips { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 20px; }
    .chip {
        display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px;
        background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 999px;
        font-size: .72rem; font-weight: 600; color: #6b7280;
    }
    .chip-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .footer {
        position: relative; z-index: 1; margin-top: 24px;
        font-size: .75rem; color: #9ca3af; text-align: center;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-8px); }
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 480px) {
        .card { padding: 36px 24px; }
        .divider { width: calc(100% + 48px); }
        .error-code { font-size: 5rem; }
        .title { font-size: 1.25rem; }
    }
</style>
