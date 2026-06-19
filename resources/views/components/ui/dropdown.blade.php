@props([
    'trigger' => 'Options',
    'align'   => 'left',  {{-- left | right --}}
])

<div style="position: relative; display: inline-block;">

    {{-- Trigger --}}
    <button onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'"
            style="
                background: var(--color-bg-elevated);
                border: 1px solid var(--color-border);
                border-radius: var(--radius-md);
                padding: 0.5rem 1rem;
                color: var(--color-text-primary);
                font-size: 0.875rem;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            ">
        {{ $trigger }}
        <span style="font-size: 0.65rem;">&#9660;</span>
    </button>

    {{-- Menu --}}
    <div style="
        display: none;
        position: absolute;
        {{ $align === 'right' ? 'right: 0;' : 'left: 0;' }}
        top: calc(100% + 4px);
        min-width: 180px;
        background: var(--color-bg-elevated);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md);
        z-index: 20;
        padding: 0.25rem 0;
    ">
        {{ $slot }}
    </div>

</div>

{{--
    Usage:
    <x-ui.dropdown trigger="Actions">
        <a href="#" style="display: block; padding: 0.5rem 1rem; color: var(--color-text-secondary); text-decoration: none; font-size: 0.875rem;">Edit</a>
        <a href="#" style="display: block; padding: 0.5rem 1rem; color: var(--color-error); text-decoration: none; font-size: 0.875rem;">Delete</a>
    </x-ui.dropdown>
--}}
