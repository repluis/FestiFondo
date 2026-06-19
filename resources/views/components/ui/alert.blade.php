@props([
    'type'    => 'info',   {{-- info | success | warning | error --}}
    'title'   => null,
    'dismiss' => false,
])

@php
$styles = [
    'info'    => ['bg' => 'var(--color-info-bg)',    'border' => 'var(--color-info)',    'text' => 'var(--color-info)'],
    'success' => ['bg' => 'var(--color-success-bg)', 'border' => 'var(--color-success)', 'text' => 'var(--color-success)'],
    'warning' => ['bg' => 'var(--color-warning-bg)', 'border' => 'var(--color-warning)', 'text' => 'var(--color-warning)'],
    'error'   => ['bg' => 'var(--color-error-bg)',   'border' => 'var(--color-error)',   'text' => 'var(--color-error)'],
];
$s = $styles[$type] ?? $styles['info'];
@endphp

<div style="
    background: {{ $s['bg'] }};
    border: 1px solid {{ $s['border'] }};
    border-radius: var(--radius-md);
    padding: 0.875rem 1rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
" {{ $attributes }}>

    <div style="flex: 1;">
        @if ($title)
            <p style="margin: 0 0 0.25rem; font-weight: 600; color: {{ $s['text'] }}; font-size: 0.875rem;">
                {{ $title }}
            </p>
        @endif
        <div style="color: var(--color-text-secondary); font-size: 0.875rem;">
            {{ $slot }}
        </div>
    </div>

</div>
