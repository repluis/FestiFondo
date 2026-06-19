<!DOCTYPE html>
<html lang="es" style="background:var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferencia de Fondos — FestiFondo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: var(--color-bg-base); color: var(--color-text-primary); margin: 0; min-height: 100vh; display: flex; flex-direction: column; }
    </style>
</head>
<body>

<x-layout.app-header />

<div style="display:flex;flex:1;">
    <x-layout.app-sidebar />
    <x-layout.main>

        <div style="
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            gap: 1rem;
            text-align: center;
        ">
            <div style="font-size: 3rem;">💸</div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--color-text-primary); margin: 0;">
                Transferencia de Fondos
            </h1>
            <p style="font-size: 0.95rem; color: var(--color-text-muted); margin: 0;">
                Módulo en construcción.
            </p>
        </div>

    </x-layout.main>
</div>

<x-layout.footer />

</body>
</html>
