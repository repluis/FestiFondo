@php
    $campaignName = ucfirst(str_replace('_', ' ', $campaign));
    $max = max(array_column($members, 'total') ?: [0]) ?: 1;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Transparencia — {{ $campaignName }}</title>
    {{-- Vista previa al compartir por WhatsApp/redes --}}
    <meta property="og:title" content="Transparencia financiera — {{ $campaignName }}">
    <meta property="og:description" content="Lo que ha aportado cada integrante de la campaña.">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: var(--color-bg-base); color: var(--color-text-primary); margin: 0; min-height: 100vh; }
        .wrap { max-width: 640px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        .head { text-align: center; margin-bottom: 1.75rem; }
        .head .icon { font-size: 2.5rem; }
        .head h1 { font-size: 1.5rem; font-weight: 700; margin: .5rem 0 .25rem; }
        .head p { color: var(--color-text-muted); font-size: .875rem; margin: 0; }
        .card {
            background: var(--color-bg-surface); border: 1px solid var(--color-border);
            border-radius: var(--radius-lg); overflow: hidden;
        }
        .row { display: flex; align-items: center; gap: .9rem; padding: .85rem 1.1rem; border-bottom: 1px solid var(--color-border); }
        .row:last-child { border-bottom: none; }
        .avatar {
            width: 2.1rem; height: 2.1rem; border-radius: 99px; flex-shrink: 0;
            background: var(--color-primary-subtle); color: var(--color-primary-light);
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem;
        }
        .main { flex: 1; min-width: 0; }
        .line { display: flex; justify-content: space-between; gap: .75rem; font-size: .92rem; }
        .name { font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .amount { font-weight: 700; color: var(--color-success); white-space: nowrap; font-variant-numeric: tabular-nums; }
        .bar { height: 5px; border-radius: 99px; background: var(--color-bg-overlay); margin-top: .4rem; overflow: hidden; }
        .bar div { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--color-primary), var(--color-success)); }
        .total {
            display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.1rem;
            background: var(--color-bg-elevated); border-top: 1px solid var(--color-border-subtle); font-weight: 600;
        }
        .total .amount { font-size: 1.15rem; }
        .note { text-align: center; color: var(--color-text-muted); font-size: .75rem; margin-top: 1.25rem; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="head">
            <div class="icon">🤝</div>
            <h1>Transparencia financiera</h1>
            <p>{{ $campaignName }} · actualizado al {{ now()->format('d/m/Y') }}</p>
        </div>

        <div class="card">
            @forelse ($members as $m)
                <div class="row">
                    <div class="avatar">{{ mb_strtoupper(mb_substr($m['name'], 0, 1)) }}</div>
                    <div class="main">
                        <div class="line">
                            <span class="name">{{ $m['name'] }}</span>
                            <span class="amount">${{ number_format($m['total'], 2) }}</span>
                        </div>
                        <div class="bar"><div style="width: {{ round($m['total'] / $max * 100, 1) }}%;"></div></div>
                    </div>
                </div>
            @empty
                <div class="row" style="justify-content:center; color:var(--color-text-muted);">Aún no hay integrantes.</div>
            @endforelse

            <div class="total">
                <span>Total aportado</span>
                <span class="amount">${{ number_format($total, 2) }}</span>
            </div>
        </div>

        <p class="note">
            Montos aportados por cada integrante, sin incluir pagos de mora.<br>
            FestiFondo
        </p>
    </div>
</body>
</html>
