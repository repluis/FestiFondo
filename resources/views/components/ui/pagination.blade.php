@props([
    'paginator',
])

@if ($paginator->hasPages())
<nav style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; font-size: 0.875rem;">

    <span style="color: var(--color-text-muted);">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </span>

    <div style="display: flex; gap: 0.25rem;">

        @if ($paginator->onFirstPage())
            <span style="padding: 0.4rem 0.75rem; color: var(--color-text-disabled); border-radius: var(--radius-sm);">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               style="padding: 0.4rem 0.75rem; color: var(--color-text-secondary); border-radius: var(--radius-sm); text-decoration: none; background: var(--color-bg-elevated);">
                &lsaquo;
            </a>
        @endif

        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span style="padding: 0.4rem 0.75rem; background: var(--color-primary); color: #fff; border-radius: var(--radius-sm); font-weight: 600;">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $url }}"
                   style="padding: 0.4rem 0.75rem; color: var(--color-text-secondary); border-radius: var(--radius-sm); text-decoration: none; background: var(--color-bg-elevated);">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               style="padding: 0.4rem 0.75rem; color: var(--color-text-secondary); border-radius: var(--radius-sm); text-decoration: none; background: var(--color-bg-elevated);">
                &rsaquo;
            </a>
        @else
            <span style="padding: 0.4rem 0.75rem; color: var(--color-text-disabled); border-radius: var(--radius-sm);">&rsaquo;</span>
        @endif

    </div>

</nav>
@endif
