@props([
    'label'   => '',
    'name'    => '',
    'checked' => false,
    'error'   => null,
])

<div style="display: flex; flex-direction: column; gap: 0.35rem;">

    <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-size: 0.875rem; color: var(--color-text-secondary);">
        <input
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $checked ? 'checked' : '' }}
            style="
                width: 16px;
                height: 16px;
                accent-color: var(--color-primary);
                cursor: pointer;
            "
            {{ $attributes }}
        >
        {{ $label }}
    </label>

    @if ($error)
        <span style="font-size: 0.8rem; color: var(--color-error);">{{ $error }}</span>
    @endif

</div>
