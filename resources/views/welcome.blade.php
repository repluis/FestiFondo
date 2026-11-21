<!DOCTYPE html>
<html lang="es">
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
        .feature {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
        }
        .feature h3 { font-size: .95rem; font-weight: 600; color: var(--color-text-primary); margin: .75rem 0 .4rem; }
        .feature p  { font-size: .825rem; color: var(--color-text-secondary); margin: 0; line-height: 1.55; }
    </style>
</head>
<body>

    {{-- Portada pública: sin datos. Los datos reales se ven al iniciar sesión (Inicio y Dashboard). --}}
    <x-layout.app-header />

    <div style="display: flex; flex: 1;">
        <x-layout.app-sidebar />

        <x-layout.main>

            <div style="
                background: linear-gradient(135deg, var(--color-primary-subtle) 0%, var(--color-bg-elevated) 100%);
                border: 1px solid var(--color-border-subtle);
                border-radius: var(--radius-xl);
                padding: 3rem 2rem;
                margin-bottom: 2rem;
                text-align: center;
            ">
                <div style="font-size: 3rem; margin-bottom: 0.75rem;">🎉</div>
                <h1 style="font-size: 2rem; font-weight: 700; color: var(--color-text-primary); margin: 0 0 0.75rem;">
                    Bienvenido a <span style="color: var(--color-primary-light);">FestiFondo</span>
                </h1>
                <p style="font-size: 1rem; color: var(--color-text-secondary); max-width: 540px; margin: 0 auto 1.75rem; line-height: 1.65;">
                    Gestión de fondos para campañas y eventos: cuotas mensuales, mora, pagos y reportes en un solo lugar.
                </p>
                <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="{{ route('auth.login') }}"
                       style="padding: 0.65rem 1.5rem; border-radius: var(--radius-md); background: var(--color-primary); color: #fff; font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('games.secret-santa') }}"
                       style="padding: 0.65rem 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--color-border-subtle); color: var(--color-text-secondary); font-weight: 500; text-decoration: none; font-size: 0.9rem;">
                        🎁 Amigo Secreto
                    </a>
                </div>
            </div>

            <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--color-text-primary); margin: 0 0 1rem;">
                ¿Qué incluye?
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                @foreach ([
                    ['icon' => '📣', 'title' => 'Campañas',       'desc' => 'Metas de recaudación con cuota mensual, día de vencimiento y mora diaria.'],
                    ['icon' => '💳', 'title' => 'Pagos',          'desc' => 'Cada pago cubre primero la mora y luego las cuotas más antiguas.'],
                    ['icon' => '📊', 'title' => 'Dashboard',      'desc' => 'Quién pagó más, quién debe más, recaudación por mes y estado de cada miembro.'],
                    ['icon' => '🎁', 'title' => 'Amigo Secreto',  'desc' => 'Sorteo entre los integrantes de una campaña con enlaces privados de un solo uso.'],
                ] as $feat)
                    <div class="feature">
                        <div style="font-size: 1.75rem;">{{ $feat['icon'] }}</div>
                        <h3>{{ $feat['title'] }}</h3>
                        <p>{{ $feat['desc'] }}</p>
                    </div>
                @endforeach
            </div>

        </x-layout.main>
    </div>

    <x-layout.footer />

</body>
</html>
