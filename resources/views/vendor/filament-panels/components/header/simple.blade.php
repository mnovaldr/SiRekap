@props([
    'heading'    => null,
    'logo'       => true,
    'subheading' => null,
])

{{-- Logo brand (light & dark) --}}
@if ($logo)
    <div style="display:flex; justify-content:center; margin-bottom:20px;">
        <img src="/images/logo.png"
             alt="{{ filament()->getBrandName() }}"
             class="dark:hidden"
             style="height:3rem; width:auto; object-fit:contain;" />
        <img src="/images/logo-white.png"
             alt="{{ filament()->getBrandName() }}"
             class="hidden dark:block"
             style="height:3rem; width:auto; object-fit:contain;" />
    </div>
@endif

@if (filled($heading))
    <header class="fi-simple-header" style="text-align:center; margin-bottom:4px;">
        <h1 class="fi-simple-header-heading"
            style="font-size:1.5rem; font-weight:800; color:#111827; letter-spacing:-.02em; line-height:1.2;">
            {{ $heading }}
        </h1>

        @if (filled($subheading))
            <p class="fi-simple-header-subheading"
               style="margin-top:6px; font-size:.875rem; color:#6b7280; line-height:1.5;">
                {{ $subheading }}
            </p>
        @endif

        {{-- Divider pemisah heading & form --}}
        <div class="si-header-divider"></div>
    </header>
@endif
