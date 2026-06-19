<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — FestiFondo</title>
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

        .checkbox-custom {
            accent-color: var(--color-primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .divider { border-color: var(--color-border-subtle); }

        .brand-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--color-primary);
            display: inline-block;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        {{-- Logo / marca --}}
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
                Bienvenido de vuelta
            </h1>
            <p class="mb-6" style="color: var(--color-text-muted); font-size: 0.875rem;">
                Ingresa tus credenciales para continuar
            </p>

            {{-- Alerta error general --}}
            @if ($errors->any() && !$errors->has('email'))
                <div class="mb-4 px-4 py-3 rounded-lg text-sm"
                     style="background: var(--color-error-bg); color: var(--color-error); border: 1px solid var(--color-error);">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.login.store') }}" novalidate>
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block mb-1">Correo electrónico</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        autofocus
                        placeholder="correo@ejemplo.com"
                        class="input-field w-full px-4 py-2.5 text-sm {{ $errors->has('email') ? 'error' : '' }}"
                    >
                    @error('email')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="mb-5">
                    <label for="password" class="block mb-1">Contraseña</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="input-field w-full px-4 py-2.5 text-sm {{ $errors->has('password') ? 'error' : '' }}"
                    >
                    @error('password')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recuérdame --}}
                <div class="flex items-center gap-2 mb-6">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="checkbox-custom"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <label for="remember" style="font-weight: 400; cursor: pointer;">
                        Mantener sesión iniciada
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary w-full py-2.5 text-sm">
                    Iniciar sesión
                </button>
            </form>

            {{-- Link a registro --}}
            <div class="mt-5 text-center">
                <span style="color: var(--color-text-muted); font-size: 0.875rem;">¿No tienes cuenta?</span>
                <a href="{{ route('auth.register') }}" class="link ml-1">Crear cuenta</a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center mt-6 text-xs" style="color: var(--color-text-disabled);">
            &copy; {{ date('Y') }} FestiFondo. Todos los derechos reservados.
        </p>
    </div>

</body>
</html>
