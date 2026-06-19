@props([
    'name'   => '',
    'src'    => null,
    'size'   => '40px',
])

@php
$initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
@endphp

<div style="
    width: {{ $size }};
    height: {{ $size }};
    border-radius: var(--radius-full);
    background: var(--color-primary-subtle);
    border: 2px solid var(--color-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
" {{ $attributes }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" style="width: 100%; height: 100%; object-fit: cover;">
    @else
        <span style="color: var(--color-primary-light); font-weight: 600; font-size: calc({{ $size }} * 0.4);">
            {{ $initials }}
        </span>
    @endif
</div>
