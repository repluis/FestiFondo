<header style="background: var(--color-bg-surface); border-bottom: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; display: flex; align-items: center; justify-content: space-between; height: 64px;">

        {{-- Logo --}}
        <a href="/" style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
            <span style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary-light); letter-spacing: -0.02em;">
                {{ config('app.name', 'FestiFondo') }}
            </span>
        </a>

        {{-- Slot for extra content (nav, actions, etc.) --}}
        {{ $slot }}

    </div>
</header>
