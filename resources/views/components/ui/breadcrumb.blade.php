@props([
    'items' => [],  {{-- [['label' => 'Home', 'url' => '/'], ['label' => 'Current']] --}}
])

<nav aria-label="breadcrumb" style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem;">
    @foreach ($items as $index => $item)

        @if (!$loop->last)
            <a href="{{ $item['url'] ?? '#' }}"
               style="color: var(--color-text-secondary); text-decoration: none;"
               onmouseover="this.style.color='var(--color-text-primary)'"
               onmouseout="this.style.color='var(--color-text-secondary)'">
                {{ $item['label'] }}
            </a>
            <span style="color: var(--color-text-disabled);">/</span>
        @else
            <span style="color: var(--color-text-primary); font-weight: 500;">{{ $item['label'] }}</span>
        @endif

    @endforeach
</nav>
