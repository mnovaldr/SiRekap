@php
    use Filament\Support\Enums\MaxWidth;
    $livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after'      => null,
        'heading'    => null,
        'subheading' => null,
    ])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ── Base ── */
        *, *::before, *::after { box-sizing: border-box; }

        /* Halaman login tidak bisa di-scroll */
        html, body {
            overflow: hidden !important;
            height: 100% !important;
        }

        .fi-simple-layout {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
            height: 100vh;
            max-height: 100vh;
            background: #f9fafb !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden !important;
            padding: 24px;
        }

        /* ── Grid lines (sama persis dengan halaman 404) ── */
        .si-bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(#e5e7eb 1px, transparent 1px),
                linear-gradient(90deg, #e5e7eb 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Blob 1 (kanan atas) ── */
        .si-blob-1 {
            position: absolute;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: #6366f1;
            filter: blur(80px);
            opacity: 0.12;
            top: -200px; right: -150px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Blob 2 (kiri bawah) ── */
        .si-blob-2 {
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: #818cf8;
            filter: blur(80px);
            opacity: 0.12;
            bottom: -100px; left: -100px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Main container ── */
        .fi-simple-main-ctn {
            position: relative;
            z-index: 1;
            width: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* ── Card ── */
        .fi-simple-main {
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 24px !important;
            padding: 40px !important;
            max-width: 440px !important;
            width: 100% !important;
            box-shadow:
                0 1px 3px rgba(0,0,0,.06),
                0 8px 32px rgba(79,70,229,.09) !important;
            animation: si-fadeUp .4s ease both;
            --tw-ring-shadow: none !important;
            --tw-shadow: none !important;
            margin-top: auto !important;
            margin-bottom: auto !important;
        }

        /* ── Heading ── */
        .fi-simple-header-heading {
            font-size: 1.5rem !important;
            font-weight: 800 !important;
            color: #111827 !important;
            letter-spacing: -.02em !important;
            line-height: 1.2 !important;
        }
        .fi-simple-header-subheading {
            font-size: .875rem !important;
            color: #6b7280 !important;
            margin-top: 6px !important;
            line-height: 1.5 !important;
        }

        /* ── Divider bawah heading ── */
        .si-header-divider {
            width: calc(100% + 80px);
            margin-left: -40px;
            height: 1px;
            background: #f3f4f6;
            margin-top: 24px;
        }

        /* ── Form labels ── */
        .fi-simple-main .fi-fo-field-wrp-label span,
        .fi-simple-main label {
            font-size: .8rem !important;
            font-weight: 600 !important;
            color: #374151 !important;
            letter-spacing: .01em !important;
        }

        /* ── Inputs ── */
        .fi-simple-main input[type="email"],
        .fi-simple-main input[type="password"],
        .fi-simple-main input[type="text"] {
            border-radius: 10px !important;
            border: 1px solid #e5e7eb !important;
            font-size: .875rem !important;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
            transition: border-color .15s, box-shadow .15s !important;
            background: #fff !important;
        }
        .fi-simple-main input[type="email"]:focus,
        .fi-simple-main input[type="password"]:focus,
        .fi-simple-main input[type="text"]:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,.12) !important;
            outline: none !important;
        }

        /* ── Submit button ── */
        .fi-simple-main .fi-btn {
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: .875rem !important;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
            transition: all .15s ease !important;
            letter-spacing: .01em !important;
        }
        .fi-simple-main .fi-btn-color-primary {
            background: #4f46e5 !important;
            box-shadow: 0 1px 3px rgba(79,70,229,.3), 0 4px 12px rgba(79,70,229,.2) !important;
        }
        .fi-simple-main .fi-btn-color-primary:hover {
            background: #4338ca !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 6px rgba(79,70,229,.4), 0 6px 16px rgba(79,70,229,.25) !important;
        }

        /* ── Footer ── */
        .si-login-footer {
            position: relative;
            z-index: 1;
            margin-top: 20px;
            font-size: .72rem;
            color: #9ca3af;
            text-align: center;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Animations ── */
        @keyframes si-fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .fi-simple-main { padding: 28px 20px !important; border-radius: 20px !important; }
            .si-header-divider { width: calc(100% + 40px); margin-left: -20px; }
        }

        /* ── Dark mode ── */
        .dark .fi-simple-layout { background: #0f172a !important; }
        .dark .si-bg-grid { opacity: 0.08; }
        .dark .si-blob-1, .dark .si-blob-2 { opacity: 0.08; }
        .dark .fi-simple-main {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,.3), 0 8px 32px rgba(0,0,0,.2) !important;
        }
        .dark .fi-simple-header-heading { color: #f1f5f9 !important; }
        .dark .fi-simple-header-subheading { color: #94a3b8 !important; }
        .dark .si-header-divider { background: #334155; }
        .dark .fi-simple-main input[type="email"],
        .dark .fi-simple-main input[type="password"],
        .dark .fi-simple-main input[type="text"] {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        .dark .si-login-footer { color: #475569; }
    </style>

    {{-- Background decorations (sama persis dengan 404) --}}
    <div class="fi-simple-layout w-full">
        <div class="si-bg-grid"></div>
        <div class="si-blob-1"></div>
        <div class="si-blob-2"></div>

        @if (($hasTopbar ?? true) && filament()->auth()->check())
            <div class="absolute end-0 top-0 flex h-16 items-center gap-x-4 pe-4 md:pe-6 lg:pe-8" style="z-index:10;">
                @if (filament()->hasDatabaseNotifications())
                    @livewire(Filament\Livewire\DatabaseNotifications::class, [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications()
                    ])
                @endif
                <x-filament-panels::user-menu />
            </div>
        @endif

        <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
            <main @class([
                'fi-simple-main my-16 w-full',
                match ($maxWidth ??= (filament()->getSimplePageMaxContentWidth() ?? MaxWidth::Large)) {
                    MaxWidth::ExtraSmall, 'xs' => 'max-w-xs',
                    MaxWidth::Small, 'sm'       => 'max-w-sm',
                    MaxWidth::Medium, 'md'      => 'max-w-md',
                    MaxWidth::Large, 'lg'       => 'max-w-lg',
                    MaxWidth::ExtraLarge, 'xl'  => 'max-w-xl',
                    default                     => $maxWidth,
                },
            ])>
                {{ $slot }}
            </main>
        </div>

        <p class="si-login-footer">
            &copy; {{ date('Y') }} SiREKAP MCC &mdash; Sistem Rekapitulasi Ekonomi Kreatif
        </p>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire?->getRenderHookScopes()) }}
</x-filament-panels::layout.base>
