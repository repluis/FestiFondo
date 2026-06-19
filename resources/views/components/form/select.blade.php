@props([
    'label'    => null,
    'name'     => '',
    'options'  => [],   {{-- ['value' => 'Label', ...] --}}
    'selected' => null,
    'error'    => null,
    'required' => false,
])

<div style="display: flex; flex-direction: column; gap: 0.35rem;">

    @if ($label)
        <label for="{{ $name }}" style="font-size: 0.875rem; font-weight: 500; color: var(--color-text-secondary);">
            {{ $label }}
            @if ($required) <span style="color: var(--color-error);">*</span> @endif
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
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
            cursor: pointer;
        "
        {{ $attributes }}
    >
        <option value="" style="background: var(--color-bg-elevated);">-- Select --</option>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}
                    style="background: var(--color-bg-elevated);">
                {{ $label }}
            </option>
        @endforeach
    </select>

    @if ($error)
        <span style="font-size: 0.8rem; color: var(--color-error);">{{ $error }}</span>
    @endif

</div>
