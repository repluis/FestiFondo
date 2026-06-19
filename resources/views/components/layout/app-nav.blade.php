<style>
    .ff-nav { display: flex; align-items: center; gap: 0.25rem; }

    .ff-dropdown { position: relative; }

    .ff-dropdown-btn {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--color-text-secondary);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: color 0.15s, background 0.15s;
    }
    .ff-dropdown-btn:hover,
    .ff-dropdown-btn.active {
        color: var(--color-text-primary);
        background: var(--color-bg-overlay);
    }
    .ff-dropdown-btn.active {
        color: var(--color-primary-light);
        background: var(--color-primary-subtle);
    }

    .ff-dropdown-btn svg {
        transition: transform 0.2s;
        flex-shrink: 0;
    }
    .ff-dropdown.open .ff-dropdown-btn svg {
        transform: rotate(180deg);
    }

    .ff-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        min-width: 180px;
        background: var(--color-bg-elevated);
        border: 1px solid var(--color-border-subtle);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        padding: 0.35rem;
        z-index: 100;
    }
    .ff-dropdown.open .ff-dropdown-menu { display: block; }

    .ff-dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        color: var(--color-text-secondary);
        text-decoration: none;
        transition: color 0.15s, background 0.15s;
    }
    .ff-dropdown-item:hover {
        color: var(--color-text-primary);
        background: var(--color-bg-overlay);
    }
    .ff-dropdown-item.active {
        color: var(--color-primary-light);
        background: var(--color-primary-subtle);
        font-weight: 600;
    }

    .ff-nav-home {
        display: flex;
        align-items: center;
        padding: 0.4rem 0.75rem;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--color-text-secondary);
        text-decoration: none;
        transition: color 0.15s, background 0.15s;
    }
    .ff-nav-home:hover { color: var(--color-text-primary); background: var(--color-bg-overlay); }
    .ff-nav-home.active { color: var(--color-primary-light); background: var(--color-primary-subtle); }
</style>

@php
    $menus = [
        [
            'label' => 'Financiero',
            'items' => [
                ['url' => '/v1/financial/fund-raising',   'label' => 'Recaudación', 'icon' => '🎯'],
                ['url' => '/v1/financial/campaigns',      'label' => 'Campañas',    'icon' => '📣'],
                ['url' => '/v1/financial/fund-transfers', 'label' => 'Transferencias', 'icon' => '💸'],
            ],
        ],
        [
            'label' => 'Administrativo',
            'items' => [
                ['url' => '/v1/financial/members', 'label' => 'Miembros', 'icon' => '👥'],
            ],
        ],
        [
            'label' => 'Reportes',
            'items' => [
                ['url' => '/v1/reports/transactions', 'label' => 'Transacciones', 'icon' => '📋'],
            ],
        ],
    ];
@endphp

<nav class="ff-nav">

    {{-- Home --}}
    <a href="/" class="ff-nav-home {{ request()->is('/') ? 'active' : '' }}">
        Inicio
    </a>

    {{-- Dropdowns --}}
    @foreach ($menus as $i => $menu)
        @php
            $isActive = collect($menu['items'])->contains(fn($item) => request()->is(ltrim($item['url'], '/') . '*'));
        @endphp
        <div class="ff-dropdown {{ $isActive ? 'active' : '' }}" id="ff-dd-{{ $i }}">
            <button class="ff-dropdown-btn {{ $isActive ? 'active' : '' }}"
                    onclick="ffToggle('ff-dd-{{ $i }}')">
                {{ $menu['label'] }}
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="ff-dropdown-menu">
                @foreach ($menu['items'] as $item)
                    @php $itemActive = request()->is(ltrim($item['url'], '/') . '*'); @endphp
                    <a href="{{ $item['url'] }}" class="ff-dropdown-item {{ $itemActive ? 'active' : '' }}">
                        <span>{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach

</nav>

<script>
    function ffToggle(id) {
        const el = document.getElementById(id);
        const isOpen = el.classList.contains('open');
        document.querySelectorAll('.ff-dropdown.open').forEach(d => d.classList.remove('open'));
        if (!isOpen) el.classList.add('open');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.ff-dropdown')) {
            document.querySelectorAll('.ff-dropdown.open').forEach(d => d.classList.remove('open'));
        }
    });
</script>
