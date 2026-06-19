<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'FestiFondo') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: var(--color-bg-base);
            color: var(--color-text-primary);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>

    {{-- ─── Header ─── --}}
    <x-layout.app-header />

    {{-- ─── Body: sidebar + main ─── --}}
    <div style="display: flex; flex: 1;">

        {{-- Sidebar --}}
        <x-layout.app-sidebar />

        {{-- Main content --}}
        <x-layout.main>

            {{-- Hero --}}
            <div style="
                background: linear-gradient(135deg, var(--color-primary-subtle) 0%, var(--color-bg-elevated) 100%);
                border: 1px solid var(--color-border-subtle);
                border-radius: var(--radius-xl);
                padding: 3rem 2.5rem;
                margin-bottom: 2rem;
                text-align: center;
            ">
                <div style="font-size: 3rem; margin-bottom: 0.75rem;">🎉</div>
                <h1 style="font-size: 2rem; font-weight: 700; color: var(--color-text-primary); margin: 0 0 0.75rem;">
                    Bienvenido a <span style="color: var(--color-primary-light);">FestiFondo</span>
                </h1>
                <p style="font-size: 1rem; color: var(--color-text-secondary); max-width: 540px; margin: 0 auto 1.75rem; line-height: 1.65;">
                    La plataforma de gestión de fondos para festivales y eventos. Organiza campañas, gestiona miembros y controla cada transferencia desde un solo lugar.
                </p>
                <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           style="padding: 0.65rem 1.5rem; border-radius: var(--radius-md); background: var(--color-primary); color: #fff; font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                            Ir al Dashboard
                        </a>
                    @else
                        <a href="{{ route('auth.register') }}"
                           style="padding: 0.65rem 1.5rem; border-radius: var(--radius-md); background: var(--color-primary); color: #fff; font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                            Comenzar gratis
                        </a>
                        <a href="{{ route('auth.login') }}"
                           style="padding: 0.65rem 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--color-border-subtle); color: var(--color-text-secondary); font-weight: 500; text-decoration: none; font-size: 0.9rem;">
                            Iniciar sesión
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Stats --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                @foreach ([
                    ['icon' => '💰', 'label' => 'Total recaudado',  'value' => '$0',  'color' => 'var(--color-success)'],
                    ['icon' => '📣', 'label' => 'Campañas activas', 'value' => '0',   'color' => 'var(--color-primary-light)'],
                    ['icon' => '👥', 'label' => 'Miembros',         'value' => '0',   'color' => 'var(--color-secondary)'],
                    ['icon' => '💸', 'label' => 'Transferencias',   'value' => '0',   'color' => 'var(--color-accent)'],
                ] as $stat)
                    <div style="
                        background: var(--color-bg-surface);
                        border: 1px solid var(--color-border);
                        border-radius: var(--radius-lg);
                        padding: 1.25rem 1.5rem;
                        display: flex;
                        flex-direction: column;
                        gap: 0.4rem;
                    ">
                        <span style="font-size: 1.5rem;">{{ $stat['icon'] }}</span>
                        <span style="font-size: 1.5rem; font-weight: 700; color: {{ $stat['color'] }};">{{ $stat['value'] }}</span>
                        <span style="font-size: 0.8rem; color: var(--color-text-muted);">{{ $stat['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Feature cards --}}
            <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--color-text-primary); margin: 0 0 1rem;">
                ¿Qué puedes hacer?
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                @foreach ([
                    ['icon' => '🎯', 'title' => 'Recaudación',     'desc' => 'Crea y gestiona campañas de financiamiento para tus eventos.',           'url' => '/v1/financial/fund-raising'],
                    ['icon' => '💸', 'title' => 'Transferencias',  'desc' => 'Registra y sigue el flujo de fondos entre cuentas y participantes.',      'url' => '/v1/financial/fund-transfers'],
                    ['icon' => '📣', 'title' => 'Campañas',        'desc' => 'Organiza campañas específicas vinculadas a tus festivales o eventos.',    'url' => '/v1/financial/campaigns'],
                    ['icon' => '👥', 'title' => 'Miembros',        'desc' => 'Administra los miembros de tu organización y sus roles.',                 'url' => '/v1/financial/members'],
                ] as $feat)
                    <a href="{{ $feat['url'] }}" style="
                        display: block;
                        background: var(--color-bg-surface);
                        border: 1px solid var(--color-border);
                        border-radius: var(--radius-lg);
                        padding: 1.5rem;
                        text-decoration: none;
                        transition: border-color 0.15s, background 0.15s;
                    "
                    onmouseover="this.style.borderColor='var(--color-border-subtle)'; this.style.background='var(--color-bg-elevated)';"
                    onmouseout="this.style.borderColor='var(--color-border)'; this.style.background='var(--color-bg-surface)';">
                        <div style="font-size: 1.75rem; margin-bottom: 0.75rem;">{{ $feat['icon'] }}</div>
                        <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--color-text-primary); margin: 0 0 0.4rem;">{{ $feat['title'] }}</h3>
                        <p style="font-size: 0.825rem; color: var(--color-text-secondary); margin: 0; line-height: 1.55;">{{ $feat['desc'] }}</p>
                    </a>
                @endforeach
            </div>

        </x-layout.main>
    </div>

    {{-- ─── Footer ─── --}}
    <x-layout.footer>
        <div style="display: flex; gap: 1.25rem;">
            <a href="https://laravel.com/docs" target="_blank"
               style="color: var(--color-text-muted); font-size: 0.8rem; text-decoration: none;">
                Laravel v{{ app()->version() }}
            </a>
            <a href="https://github.com/laravel/framework/blob/13.x/CHANGELOG.md" target="_blank"
               style="color: var(--color-text-muted); font-size: 0.8rem; text-decoration: none;">
                Changelog
            </a>
        </div>
    </x-layout.footer>

</body>
</html>
