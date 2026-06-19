<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — FestiFondo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background-color: var(--color-bg-base); color: var(--color-text-primary); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-2xl font-semibold mb-2">Bienvenido, {{ auth()->user()->name }}</h1>
        <p style="color: var(--color-text-muted);">Has iniciado sesión correctamente.</p>
        <form method="POST" action="{{ route('auth.logout') }}" class="mt-6">
            @csrf
            <button type="submit"
                style="background: var(--color-primary); color: #fff; padding: 8px 20px; border-radius: var(--radius-md); font-size: 0.875rem; cursor: pointer;">
                Cerrar sesión
            </button>
        </form>
    </div>
</body>
</html>
