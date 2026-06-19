@props([
    'id'    => 'modal',
    'title' => '',
    'size'  => 'md',  {{-- sm | md | lg | xl --}}
])

@php
$widths = ['sm' => '400px', 'md' => '560px', 'lg' => '720px', 'xl' => '960px'];
$width  = $widths[$size] ?? $widths['md'];
@endphp

{{-- Overlay --}}
<div id="{{ $id }}"
     style="display: none; position: fixed; inset: 0; z-index: 50; background: rgba(0,0,0,0.7); align-items: center; justify-content: center;"
     onclick="if(event.target===this) document.getElementById('{{ $id }}').style.display='none'">

    {{-- Dialog --}}
    <div style="
        background: var(--color-bg-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        width: {{ $width }};
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    ">
        {{-- Header --}}
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 600; color: var(--color-text-primary);">{{ $title }}</h3>
            <button onclick="document.getElementById('{{ $id }}').style.display='none'"
                    style="background: none; border: none; cursor: pointer; color: var(--color-text-muted); font-size: 1.25rem; line-height: 1; padding: 0.25rem;">
                &times;
            </button>
        </div>

        {{-- Body --}}
        <div style="padding: 1.5rem; overflow-y: auto; color: var(--color-text-secondary);">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @isset($footer)
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>
