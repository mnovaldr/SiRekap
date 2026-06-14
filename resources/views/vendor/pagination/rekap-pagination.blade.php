@php
    // Map pageName -> Livewire per-page property
    $_perPagePropMap = [
        'page'          => 'perPage',
        'eventPage'     => 'eventPagePerPage',
        'communityPage' => 'communityPagePerPage',
    ];
    $_perPageProp = $_perPagePropMap[$paginator->getPageName()] ?? null;
@endphp
@if ($paginator->hasPages() || true) {{-- always show so per-page is accessible --}}
@php
    $pageName = $paginator->getPageName();
    $currentPage = $paginator->currentPage();
    $lastPage    = $paginator->lastPage();
    $perPage     = $paginator->perPage();
    $total       = $paginator->total();
    $from        = $paginator->firstItem() ?? 0;
    $to          = $paginator->lastItem() ?? 0;

    // Build page number windows: first 3, ellipsis, last 2 — with current in range
    $window = [];
    $showDots1 = false;
    $showDots2 = false;

    if ($lastPage <= 7) {
        $window = range(1, $lastPage);
    } else {
        $start = max(2, $currentPage - 1);
        $end   = min($lastPage - 1, $currentPage + 1);

        $window[] = 1;
        if ($start > 2) { $showDots1 = true; }
        foreach (range($start, $end) as $p) { $window[] = $p; }
        if ($end < $lastPage - 1) { $showDots2 = true; }
        $window[] = $lastPage;
    }
@endphp

<div class="rp-wrap" wire:key="pagination-{{ $pageName }}">

    {{-- Left: Showing info --}}
    <div class="rp-info">
        Showing <strong>{{ number_format($from) }}</strong> to <strong>{{ number_format($to) }}</strong>
        of <strong>{{ number_format($total) }}</strong> results
    </div>

    {{-- Middle: Per page --}}
    @if($_perPageProp)
    <div class="rp-perpage">
        <span class="rp-perpage-label">Per page</span>
        <div class="rp-perpage-select-wrap">
            <select
                class="rp-perpage-select"
                wire:model.live="{{ $_perPageProp }}"
            >
                @foreach([10, 12, 15, 25, 50] as $opt)
                    <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                @endforeach
            </select>
            <svg class="rp-chevron" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </div>
    </div>
    @endif

    {{-- Right: Page numbers --}}
    <div class="rp-pages">

        {{-- Prev --}}
        @if($paginator->onFirstPage())
            <button class="rp-btn rp-btn-nav" disabled aria-label="Previous">
                <svg viewBox="0 0 20 20" fill="currentColor" class="rp-nav-icon"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
            </button>
        @else
            <button
                class="rp-btn rp-btn-nav"
                wire:click="previousPage('{{ $pageName }}')"
                wire:loading.attr="disabled"
                aria-label="Previous"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" class="rp-nav-icon"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
            </button>
        @endif

        {{-- Page numbers --}}
        @foreach($window as $i => $page)
            @if($i > 0 && $page !== $window[$i-1] + 1)
                {{-- dots before this page --}}
                <span class="rp-dots">…</span>
            @endif

            @if($page === $currentPage)
                <button class="rp-btn rp-btn-active" aria-current="page">{{ $page }}</button>
            @else
                <button
                    class="rp-btn"
                    wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                    wire:loading.attr="disabled"
                >{{ $page }}</button>
            @endif
        @endforeach

        {{-- Next --}}
        @if($paginator->hasMorePages())
            <button
                class="rp-btn rp-btn-nav"
                wire:click="nextPage('{{ $pageName }}')"
                wire:loading.attr="disabled"
                aria-label="Next"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" class="rp-nav-icon"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
            </button>
        @else
            <button class="rp-btn rp-btn-nav" disabled aria-label="Next">
                <svg viewBox="0 0 20 20" fill="currentColor" class="rp-nav-icon"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
            </button>
        @endif
    </div>
</div>
@endif
