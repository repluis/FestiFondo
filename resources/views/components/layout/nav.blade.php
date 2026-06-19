<nav style="display: flex; align-items: center; gap: 0.25rem;">
    @foreach ($links as $link)
        <a href="{{ $link['url'] }}"
           style="
               padding: 0.4rem 0.75rem;
               border-radius: var(--radius-md);
               font-size: 0.875rem;
               font-weight: 500;
               color: {{ request()->is(ltrim($link['url'], '/')) ? 'var(--color-primary-light)' : 'var(--color-text-secondary)' }};
               background: {{ request()->is(ltrim($link['url'], '/')) ? 'var(--color-primary-subtle)' : 'transparent' }};
               text-decoration: none;
               transition: color 0.15s, background 0.15s;
           "
           onmouseover="this.style.color='var(--color-text-primary)'; this.style.background='var(--color-bg-overlay)';"
           onmouseout="this.style.color='{{ request()->is(ltrim($link['url'], '/')) ? 'var(--color-primary-light)' : 'var(--color-text-secondary)' }}'; this.style.background='{{ request()->is(ltrim($link['url'], '/')) ? 'var(--color-primary-subtle)' : 'transparent' }}';">
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
