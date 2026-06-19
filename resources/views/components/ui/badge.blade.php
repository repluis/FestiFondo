@props([
    'variant' => 'primary', {{-- primary | success | warning | error | info | muted --}}
])

@php
$styles = [
    'primary' => 'background: var(--color-primary-subtle); color: var(--color-primary-light);',
    'success' => 'background: var(--color-success-bg); color: var(--color-success);',
    'warning' => 'background: var(--color-warning-bg); color: var(--color-warning);',
    'error'   => 'background: var(--color-error-bg); color: var(--color-error);',
    'info'    => 'background: var(--color-info-bg); color: var(--color-info);',
    'muted'   => 'background: var(--color-bg-elevated); color: var(--color-text-muted);',
];
@endphp

<span style="
    {{ $styles[$variant] ?? $styles['primary'] }}
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
    white-space: nowrap;
" {{ $attributes }}>
    {{ $slot }}
</span>
