<footer style="
    background: var(--color-bg-surface);
    border-top: 1px solid var(--color-border);
    color: var(--color-text-muted);
    font-size: 0.875rem;
">
    <div style="max-width: 1280px; margin: 0 auto; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <span>&copy; {{ date('Y') }} {{ config('app.name', 'FestiFondo') }}. All rights reserved.</span>

        {{ $slot ?? '' }}
    </div>
</footer>
