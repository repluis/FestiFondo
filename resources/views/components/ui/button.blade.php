@props([
    'variant' => 'primary',  {{-- primary | secondary | accent | ghost | danger --}}
    'size'    => 'md',       {{-- sm | md | lg --}}
    'type'    => 'button',
])

@php
$colors = [
    'primary'   => 'background: var(--color-primary); color: #fff;',
    'secondary' => 'background: var(--color-secondary); color: #fff;',
    'accent'    => 'background: var(--color-accent); color: #0A0F1E;',
    'ghost'     => 'background: transparent; color: var(--color-text-secondary); border: 1px solid var(--color-border);',
    'danger'    => 'background: var(--color-error); color: #fff;',
];

$sizes = [
    'sm' => 'padding: 0.3rem 0.75rem; font-size: 0.8rem;',
    'md' => 'padding: 0.5rem 1.25rem; font-size: 0.875rem;',
    'lg' => 'padding: 0.75rem 1.75rem; font-size: 1rem;',
];
@endphp

<button
    type="{{ $type }}"
    style="
        {{ $colors[$variant] ?? $colors['primary'] }}
        {{ $sizes[$size] ?? $sizes['md'] }}
        border-radius: var(--radius-md);
        font-weight: 500;
        cursor: pointer;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: opacity 0.15s;
    "
    onmouseover="this.style.opacity='0.85'"
    onmouseout="this.style.opacity='1'"
    {{ $attributes }}
>
    {{ $slot }}
</button>
