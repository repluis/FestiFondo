<x-layout.sidebar>

@php
    $sections = [
        'financiero' => [
            'label' => 'Financiero',
            'links' => [
                ['url' => '/v1/financial/fund-raising',   'label' => 'Recaudación',    'icon' => '🎯'],
                ['url' => '/v1/financial/campaigns',      'label' => 'Campañas',       'icon' => '📣'],
                ['url' => '/v1/financial/fund-transfers', 'label' => 'Transferencias', 'icon' => '💸'],
            ],
        ],
        'administrativo' => [
            'label' => 'Administrativo',
            'links' => [
                ['url' => '/v1/financial/members', 'label' => 'Miembros', 'icon' => '👥'],
            ],
        ],
    ];

    // Detectar la sección activa según la URL actual
    $activeSection = null;
    foreach ($sections as $key => $section) {
        foreach ($section['links'] as $link) {
            if (request()->is(ltrim($link['url'], '/') . '*')) {
                $activeSection = $key;
                break 2;
            }
        }
    }

    // Si hay sección activa, mostrar solo esa; si no (home, etc.) mostrar todas
    $visibleSections = $activeSection
        ? [$activeSection => $sections[$activeSection]]
        : $sections;
@endphp

@foreach ($visibleSections as $key => $section)

    <div style="padding: 0 0.75rem; margin-bottom: 0.75rem; {{ !$loop->first ? 'margin-top: 1.25rem;' : '' }}">
        <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-muted); font-weight: 600;">
            {{ $section['label'] }}
        </span>
    </div>

    @foreach ($section['links'] as $link)
        @php $active = request()->is(ltrim($link['url'], '/') . '*'); @endphp
        <a href="{{ $link['url'] }}" style="
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.75rem;
            margin: 0 0.5rem 0.15rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: {{ $active ? '600' : '400' }};
            color: {{ $active ? 'var(--color-primary-light)' : 'var(--color-text-secondary)' }};
            background: {{ $active ? 'var(--color-primary-subtle)' : 'transparent' }};
            text-decoration: none;
            transition: color 0.15s, background 0.15s;
        "
        @if(!$active)
            onmouseover="this.style.background='var(--color-bg-overlay)'; this.style.color='var(--color-text-primary)';"
            onmouseout="this.style.background='transparent'; this.style.color='var(--color-text-secondary)';"
        @endif>
            <span>{{ $link['icon'] }}</span>
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach

@endforeach

</x-layout.sidebar>
