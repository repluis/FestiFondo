<x-layout.header>

    <x-layout.app-nav />

    <div style="display: flex; align-items: center; gap: 0.5rem;">
        @auth
            <a href="{{ url('/dashboard') }}"
               style="padding: 0.4rem 1rem; border-radius: var(--radius-md); background: var(--color-primary); color: #fff; font-size: 0.875rem; text-decoration: none; font-weight: 500;">
                Dashboard
            </a>
            <form method="POST" action="{{ route('auth.logout') }}" style="margin: 0;">
                @csrf
                <button type="submit"
                        style="padding: 0.4rem 0.9rem; border-radius: var(--radius-md); color: var(--color-text-secondary); font-size: 0.875rem; font-weight: 500; border: 1px solid var(--color-border-subtle); background: transparent; cursor: pointer;">
                    Salir
                </button>
            </form>
        @else
            <a href="{{ route('auth.login') }}"
               style="padding: 0.4rem 0.9rem; border-radius: var(--radius-md); color: var(--color-text-secondary); font-size: 0.875rem; text-decoration: none; font-weight: 500; border: 1px solid var(--color-border-subtle);">
                Iniciar sesión
            </a>
            <a href="{{ route('auth.register') }}"
               style="padding: 0.4rem 1rem; border-radius: var(--radius-md); background: var(--color-primary); color: #fff; font-size: 0.875rem; text-decoration: none; font-weight: 500;">
                Registrarse
            </a>
        @endauth
    </div>

</x-layout.header>
