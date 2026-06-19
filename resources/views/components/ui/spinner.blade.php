@props([
    'size'  => '24px',
    'color' => 'var(--color-primary)',
])

<span style="
    display: inline-block;
    width: {{ $size }};
    height: {{ $size }};
    border: 2px solid var(--color-border);
    border-top-color: {{ $color }};
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
" {{ $attributes }}></span>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
