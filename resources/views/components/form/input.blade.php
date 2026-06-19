@props([
    'label'       => null,
    'name'        => '',
    'type'        => 'text',
    'placeholder' => '',
    'error'       => null,
    'required'    => false,
])

<div style="display: flex; flex-direction: column; gap: 0.35rem;">

    @if ($label)
        <label for="{{ $name }}" style="font-size: 0.875rem; font-weight: 500; color: var(--color-text-secondary);">
            {{ $label }}
            @if ($required) <span style="color: var(--color-error);">*</span> @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        style="
            background: var(--color-bg-elevated);
            border: 1px solid {{ $error ? 'var(--color-error)' : 'var(--color-border-subtle)' }};
            border-radius: var(--radius-md);
            padding: 0.55rem 0.875rem;
            color: var(--color-text-primary);
            font-size: 0.875rem;
            outline: none;
            width: 100%;
            box-sizing: border-box;
        "
        onfocus="this.style.borderColor='var(--color-primary)'; this.style.boxShadow='0 0 0 3px var(--color-primary-subtle)';"
        onblur="this.style.borderColor='{{ $error ? 'var(--color-error)' : 'var(--color-border-subtle)' }}'; this.style.boxShadow='none';"
        {{ $attributes }}
    >

    @if ($error)
        <span style="font-size: 0.8rem; color: var(--color-error);">{{ $error }}</span>
    @endif

</div>
