<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — FestiFondo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background-color: var(--color-bg-base); color: var(--color-text-primary); }

        .card {
            background-color: var(--color-bg-surface);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
        }

        .input-field {
            background-color: var(--color-bg-elevated);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-md);
            color: var(--color-text-primary);
            transition: border-color 0.2s;
        }
        .input-field::placeholder { color: var(--color-text-muted); }
        .input-field:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        .input-field.error { border-color: var(--color-error); }

        .btn-primary {
            background-color: var(--color-primary);
            color: #fff;
            border-radius: var(--radius-md);
            font-weight: 600;
            transition: background-color 0.2s, transform 0.1s;
        }
        .btn-primary:hover { background-color: var(--color-primary-hover); }
        .btn-primary:active { transform: scale(0.98); }

        .error-msg { color: var(--color-error); font-size: 0.8rem; margin-top: 4px; }

        label { color: var(--color-text-secondary); font-size: 0.875rem; font-weight: 500; }

        .link { color: var(--color-primary-light); text-decoration: none; font-size: 0.875rem; }
        .link:hover { text-decoration: underline; }

        .brand-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--color-primary); display: inline-block; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="brand-dot"></span>
                <span style="color: var(--color-primary); font-size: 1.5rem; font-weight: 700; letter-spacing: -0.5px;">
                    FestiFondo
                </span>
            </div>
            <p style="color: var(--color-text-muted); font-size: 0.875rem;">
                Gestión de fondos para eventos
            </p>
        </div>

        {{-- Card --}}
        <div class="card p-8">

            <h1 class="text-xl font-semibold mb-1" style="color: var(--color-text-primary);">
                Crear cuenta
            </h1>
            <p class="mb-6" style="color: var(--color-text-muted); font-size: 0.875rem;">
                Completa los datos para registrarte
            </p>

            <form method="POST" action="{{ route('auth.register.store') }}" novalidate>
                @csrf

                {{-- Nombre --}}
                <div class="mb-4">
                    <label for="name" class="block mb-1">Nombre completo</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        autofocus
                        placeholder="Juan Pérez"
                        class="input-field w-full px-4 py-2.5 text-sm {{ $errors->has('name') ? 'error' : '' }}"
                    >
                    @error('name')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Username --}}
                <div class="mb-4">
                    <label for="username" class="block mb-1">Nombre de usuario</label>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        placeholder="juanperez"
                        class="input-field w-full px-4 py-2.5 text-sm {{ $errors->has('username') ? 'error' : '' }}"
                    >
                    @error('username')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block mb-1">Correo electrónico</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="correo@ejemplo.com"
                        class="input-field w-full px-4 py-2.5 text-sm {{ $errors->has('email') ? 'error' : '' }}"
                    >
                    @error('email')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="mb-4">
                    <label for="password" class="block mb-1">Contraseña</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Mínimo 8 caracteres"
                        class="input-field w-full px-4 py-2.5 text-sm {{ $errors->has('password') ? 'error' : '' }}"
                    >
                    @error('password')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmar contraseña --}}
                <div class="mb-6">
                    <label for="password_confirmation" class="block mb-1">Confirmar contraseña</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Repite la contraseña"
                        class="input-field w-full px-4 py-2.5 text-sm {{ $errors->has('password_confirmation') ? 'error' : '' }}"
                    >
                    @error('password_confirmation')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary w-full py-2.5 text-sm">
                    Crear cuenta
                </button>
            </form>

            {{-- Link a login --}}
            <div class="mt-5 text-center">
                <span style="color: var(--color-text-muted); font-size: 0.875rem;">¿Ya tienes cuenta?</span>
                <a href="{{ route('auth.login') }}" class="link ml-1">Iniciar sesión</a>
            </div>
        </div>

        <p class="text-center mt-6 text-xs" style="color: var(--color-text-disabled);">
            &copy; {{ date('Y') }} FestiFondo. Todos los derechos reservados.
        </p>
    </div>

</body>
</html>
