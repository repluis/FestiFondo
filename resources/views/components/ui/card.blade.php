@props([
    'title'   => null,
    'padding' => '1.5rem',
])

<div style="
    background: var(--color-bg-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
" {{ $attributes }}>

    @if ($title)
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-border);">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 600; color: var(--color-text-primary);">
                {{ $title }}
            </h3>
        </div>
    @endif

    <div style="padding: {{ $padding }};">
        {{ $slot }}
    </div>

</div>
