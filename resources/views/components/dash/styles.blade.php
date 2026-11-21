{{-- Estilos compartidos por Inicio y Dashboard --}}
<style>
    body { background: var(--color-bg-base); color: var(--color-text-primary); margin: 0; min-height: 100vh; display: flex; flex-direction: column; }

    .dash-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin: 1.5rem 0; flex-wrap: wrap; }
    .dash-title { font-size: 1.5rem; font-weight: 700; color: var(--color-text-primary); margin: 0; }
    .dash-subtitle { color: var(--color-text-muted); font-size: .875rem; margin-top: .2rem; }
    .dash-link {
        padding: .45rem 1rem; border-radius: var(--radius-md); font-size: .85rem; font-weight: 500;
        text-decoration: none; color: var(--color-text-secondary); border: 1px solid var(--color-border-subtle);
        transition: color .15s, border-color .15s;
    }
    .dash-link:hover { color: var(--color-text-primary); border-color: var(--color-text-secondary); }
    .dash-link.primary { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

    .dash-grid-kpi { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .dash-grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .dash-grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }

    .dash-kpi {
        background: var(--color-bg-surface); border: 1px solid var(--color-border);
        border-radius: var(--radius-lg); padding: 1.1rem 1.25rem; display: flex; flex-direction: column; gap: .35rem;
    }
    .dash-kpi-top { display: flex; justify-content: space-between; align-items: center; }
    .dash-kpi-label { font-size: .72rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }
    .dash-kpi-icon { font-size: 1.2rem; }
    .dash-kpi-value { font-size: 1.65rem; font-weight: 700; line-height: 1.2; }
    .dash-kpi-hint { font-size: .78rem; color: var(--color-text-muted); }

    .dash-rows { display: flex; flex-direction: column; gap: .9rem; }
    .dash-row { display: flex; gap: .75rem; align-items: flex-start; }
    .dash-rank {
        flex-shrink: 0; width: 1.6rem; height: 1.6rem; border-radius: 99px; background: var(--color-bg-overlay);
        color: var(--color-text-secondary); font-size: .75rem; font-weight: 700; display: flex; align-items: center; justify-content: center;
    }
    .dash-row-main { flex: 1; min-width: 0; }
    .dash-row-head { display: flex; justify-content: space-between; gap: .5rem; font-size: .875rem; }
    .dash-row-name { color: var(--color-text-primary); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dash-row-value { font-weight: 700; white-space: nowrap; }
    .dash-row-sub { font-size: .75rem; color: var(--color-text-muted); margin-top: .3rem; }
    .dash-bar { height: 6px; border-radius: 99px; background: var(--color-bg-overlay); margin-top: .35rem; overflow: hidden; }
    .dash-bar > div { height: 100%; border-radius: 99px; }

    .dash-empty { padding: 1.5rem; text-align: center; color: var(--color-text-muted); font-size: .875rem; }

    .dash-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .dash-table th {
        text-align: left; padding: .65rem .9rem; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em;
        color: var(--color-text-muted); border-bottom: 1px solid var(--color-border); font-weight: 600; white-space: nowrap;
    }
    .dash-table td { padding: .7rem .9rem; border-bottom: 1px solid var(--color-border); color: var(--color-text-secondary); white-space: nowrap; }
    .dash-table tr:last-child td { border-bottom: none; }
    .dash-table .num { text-align: right; font-variant-numeric: tabular-nums; }
    .dash-table .strong { color: var(--color-text-primary); font-weight: 500; }

    .dash-badge { display: inline-block; padding: .15rem .55rem; border-radius: 99px; font-size: .72rem; font-weight: 600; }
    .dash-badge.ok   { background: var(--color-success-bg); color: var(--color-success); }
    .dash-badge.warn { background: var(--color-warning-bg); color: var(--color-warning); }
    .dash-badge.bad  { background: var(--color-error-bg);   color: var(--color-error); }
    .dash-badge.info { background: var(--color-primary-subtle); color: var(--color-primary-light); }

    .dash-progress { height: 10px; border-radius: 99px; background: var(--color-bg-overlay); overflow: hidden; }
    .dash-progress > div { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--color-primary), var(--color-accent)); }

    .dash-legend { display: flex; flex-wrap: wrap; gap: .9rem; font-size: .78rem; color: var(--color-text-secondary); }
    .dash-legend span { display: inline-flex; align-items: center; gap: .35rem; }
    .dash-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }

    .dash-scroll { overflow-x: auto; }

    @media (max-width: 640px) {
        .dash-grid-2, .dash-grid-3 { grid-template-columns: 1fr; }
        .dash-kpi-value { font-size: 1.35rem; }
    }
</style>
