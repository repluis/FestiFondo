@props([
    'icon'  => '',
    'label' => '',
    'value' => '',
    'hint'  => null,
    'color' => 'var(--color-text-primary)',
])

<div class="dash-kpi">
    <div class="dash-kpi-top">
        <span class="dash-kpi-label">{{ $label }}</span>
        <span class="dash-kpi-icon">{{ $icon }}</span>
    </div>
    <div class="dash-kpi-value" style="color: {{ $color }};">{{ $value }}</div>
    @if ($hint)
        <div class="dash-kpi-hint">{{ $hint }}</div>
    @endif
</div>
