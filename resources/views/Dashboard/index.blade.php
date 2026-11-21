<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — FestiFondo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-dash.styles />
    <style>
        /* Barras mensuales apiladas */
        .chart-bars { display: flex; align-items: flex-end; gap: .5rem; height: 220px; padding-top: 1.5rem; }
        .chart-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; min-width: 0; }
        .chart-stack { flex: 1; width: 100%; max-width: 44px; display: flex; flex-direction: column-reverse; position: relative; }
        .chart-stack > div:not(.chart-val) { width: 100%; }
        .chart-stack > div:first-child { border-radius: 0 0 4px 4px; }
        .chart-top { border-radius: 4px 4px 0 0; }
        .chart-val { position: absolute; left: 50%; transform: translateX(-50%); font-size: .68rem; color: var(--color-text-secondary); white-space: nowrap; font-weight: 600; }
        .chart-lbl { font-size: .68rem; color: var(--color-text-muted); margin-top: .45rem; white-space: nowrap; }
        .chart-col:hover .chart-stack { filter: brightness(1.2); }

        /* Donas */
        .donut-wrap { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; justify-content: center; }
        .donut { width: 150px; height: 150px; border-radius: 50%; position: relative; flex-shrink: 0; }
        .donut::after { content: ''; position: absolute; inset: 22px; border-radius: 50%; background: var(--color-bg-surface); }
        .donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 1; }
        .donut-center strong { font-size: 1.25rem; color: var(--color-text-primary); }
        .donut-center span { font-size: .7rem; color: var(--color-text-muted); }
        .donut-list { display: flex; flex-direction: column; gap: .6rem; font-size: .82rem; min-width: 160px; }
        .donut-list div { display: flex; justify-content: space-between; gap: 1rem; color: var(--color-text-secondary); }
        .donut-list i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; margin-right: .4rem; }
        .donut-list b { color: var(--color-text-primary); font-weight: 600; }
    </style>
</head>
<body>

<x-layout.app-header />

<div style="display: flex; flex: 1;">
    <x-layout.app-sidebar />

    <x-layout.main>

        <x-ui.breadcrumb :items="[['label' => 'Inicio', 'url' => '/'], ['label' => 'Dashboard']]" />

        <div class="dash-head">
            <div>
                <h1 class="dash-title">📊 Dashboard</h1>
                <p class="dash-subtitle">Estado de pagos, deudas y recaudación al {{ now()->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('reports.transactions') }}" class="dash-link">📋 Reporte de transacciones</a>
        </div>

        @php
            $money = fn ($v) => '$'.number_format($v, 2);
            $c     = $collection;
        @endphp

        {{-- ── Indicadores ─────────────────────────────────────────────── --}}
        <div class="dash-grid-kpi">
            <x-dash.kpi icon="💰" label="Total recaudado" :value="$money($kpis['collected'])" :hint="$kpis['payments_count'].' pagos'" color="var(--color-success)" />
            <x-dash.kpi icon="🧮" label="Pago promedio" :value="$money($kpis['average_payment'])" :hint="$c['best_month'] ? 'Mejor mes: '.$c['best_month']['label'].' ('.$money($c['best_month']['total']).')' : null" />
            <x-dash.kpi icon="✅" label="Al día" :value="$kpis['up_to_date'].' / '.$kpis['members']" :hint="round($kpis['members'] ? $kpis['up_to_date'] / $kpis['members'] * 100 : 0).'% de los miembros'" color="var(--color-primary-light)" />
            <x-dash.kpi icon="📅" label="Cuotas pendientes" :value="$money($kpis['fees_due'])" :hint="$kpis['months_due'].' cuotas sin pagar'" color="var(--color-warning)" />
            <x-dash.kpi icon="⏰" label="Mora pendiente" :value="$money($kpis['penalties_due'])" color="var(--color-error)" />
            <x-dash.kpi icon="📉" label="Deuda total" :value="$money($kpis['total_due'])" :hint="$kpis['with_debt'].' miembros con deuda'" color="var(--color-error)" />
        </div>

        {{-- ── Recaudación por mes ─────────────────────────────────────── --}}
        @php
            $maxMonth = max(array_column($c['monthly'], 'total') ?: [0]) ?: 1;
        @endphp
        <x-ui.card title="📈 Recaudación por mes (últimos 12 meses)">
            <div class="dash-legend" style="margin-bottom:.5rem;">
                <span><i style="background:var(--color-primary-light)"></i> Aplicado a cuotas</span>
                <span><i style="background:var(--color-warning)"></i> Aplicado a mora</span>
                <span><i style="background:var(--color-accent)"></i> Pagos históricos (sin desglose)</span>
            </div>
            <div class="dash-scroll">
                <div class="chart-bars" style="min-width: 560px;">
                    @foreach ($c['monthly'] as $m)
                        @php
                            $other = max(0, $m['total'] - $m['to_fees'] - $m['to_penalties']);
                            $h     = fn ($v) => round($v / $maxMonth * 100, 2);
                            $segments = array_values(array_filter([
                                ['v' => $m['to_fees'],      'c' => 'var(--color-primary-light)'],
                                ['v' => $m['to_penalties'], 'c' => 'var(--color-warning)'],
                                ['v' => $other,             'c' => 'var(--color-accent)'],
                            ], fn ($s) => $s['v'] > 0));
                        @endphp
                        <div class="chart-col" title="{{ $m['label'] }}: {{ $money($m['total']) }} en {{ $m['count'] }} pagos">
                            <div class="chart-stack">
                                @foreach ($segments as $s)
                                    <div class="{{ $loop->last ? 'chart-top' : '' }}" style="height: {{ $h($s['v']) }}%; background: {{ $s['c'] }};"></div>
                                @endforeach
                                @if ($m['total'] > 0)
                                    <div class="chart-val" style="bottom: calc({{ $h($m['total']) }}% + 4px);">${{ rtrim(rtrim(number_format($m['total'], 2), '0'), '.') }}</div>
                                @endif
                            </div>
                            <div class="chart-lbl">{{ $m['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-ui.card>

        <div style="height:1.5rem;"></div>

        {{-- ── Donas ──────────────────────────────────────────────────── --}}
        @php
            $pct = fn ($part, $total) => $total > 0 ? round($part / $total * 100, 2) : 0;

            // 1) ¿A qué se fue lo recaudado?
            $p1 = $pct($c['to_fees'], $c['total']);
            $p2 = $p1 + $pct($c['to_penalties'], $c['total']);

            // 2) Estado de los miembros
            $s1 = $pct($kpis['up_to_date'], $kpis['members']);

            // 3) Composición de la deuda
            $d1 = $pct($kpis['fees_due'], $kpis['total_due']);
        @endphp
        <div class="dash-grid-3">
            <x-ui.card title="🥧 Destino de lo recaudado">
                <div class="donut-wrap">
                    <div class="donut" style="background: conic-gradient(var(--color-primary-light) 0 {{ $p1 }}%, var(--color-warning) {{ $p1 }}% {{ $p2 }}%, var(--color-accent) {{ $p2 }}% 100%);">
                        <div class="donut-center"><strong>{{ $money($c['total']) }}</strong><span>recaudado</span></div>
                    </div>
                    <div class="donut-list">
                        <div><span><i style="background:var(--color-primary-light)"></i>Cuotas</span><b>{{ $money($c['to_fees']) }}</b></div>
                        <div><span><i style="background:var(--color-warning)"></i>Mora</span><b>{{ $money($c['to_penalties']) }}</b></div>
                        <div><span><i style="background:var(--color-accent)"></i>Históricos</span><b>{{ $money($c['unallocated']) }}</b></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="👥 Estado de los miembros">
                <div class="donut-wrap">
                    <div class="donut" style="background: conic-gradient(var(--color-success) 0 {{ $s1 }}%, var(--color-error) {{ $s1 }}% 100%);">
                        <div class="donut-center"><strong>{{ round($s1) }}%</strong><span>al día</span></div>
                    </div>
                    <div class="donut-list">
                        <div><span><i style="background:var(--color-success)"></i>Al día</span><b>{{ $kpis['up_to_date'] }}</b></div>
                        <div><span><i style="background:var(--color-error)"></i>Con deuda</span><b>{{ $kpis['with_debt'] }}</b></div>
                        <div><span><i style="background:var(--color-bg-overlay)"></i>Total</span><b>{{ $kpis['members'] }}</b></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="📉 Composición de la deuda">
                <div class="donut-wrap">
                    <div class="donut" style="background: {{ $kpis['total_due'] > 0 ? 'conic-gradient(var(--color-warning) 0 '.$d1.'%, var(--color-error) '.$d1.'% 100%)' : 'var(--color-success)' }};">
                        <div class="donut-center"><strong>{{ $money($kpis['total_due']) }}</strong><span>adeudado</span></div>
                    </div>
                    <div class="donut-list">
                        <div><span><i style="background:var(--color-warning)"></i>Cuotas</span><b>{{ $money($kpis['fees_due']) }}</b></div>
                        <div><span><i style="background:var(--color-error)"></i>Mora</span><b>{{ $money($kpis['penalties_due']) }}</b></div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- ── Rankings ───────────────────────────────────────────────── --}}
        <div class="dash-grid-2">
            <x-ui.card title="🏆 Quienes más han pagado">
                <x-dash.member-table :members="array_slice($standings['top_payers'], 0, 5)" mode="paid" />
            </x-ui.card>
            <x-ui.card title="🐢 Quienes menos han pagado">
                <x-dash.member-table :members="array_slice($standings['bottom_payers'], 0, 5)" mode="paid" />
            </x-ui.card>
            <x-ui.card title="⚠️ Quienes más deben">
                <x-dash.member-table :members="array_slice($standings['top_debtors'], 0, 5)" mode="debt" empty="🎉 Todos están al día" />
            </x-ui.card>
            <x-ui.card title="🟡 Quienes menos deben">
                <x-dash.member-table :members="array_slice($standings['smallest_debtors'], 0, 5)" mode="debt" empty="🎉 Todos están al día" />
            </x-ui.card>
        </div>

        {{-- ── Tabla completa ─────────────────────────────────────────── --}}
        @php
            $rows = $standings['all'];
            usort($rows, fn ($a, $b) => [$b->totalDue(), $a->name] <=> [$a->totalDue(), $b->name]);
        @endphp
        <x-ui.card title="📋 Estado de cuenta de todos los miembros" padding="0">
            <div class="dash-scroll">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Miembro</th>
                            <th>Estado</th>
                            <th class="num">Total pagado</th>
                            <th class="num">Pagos</th>
                            <th>Último pago</th>
                            <th class="num">Cuotas pend.</th>
                            <th class="num">Mora</th>
                            <th class="num">Deuda total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $m)
                            @php
                                $state = $m->isUpToDate() ? ['ok', 'Al día'] : ($m->monthsDue >= 3 ? ['bad', 'Atrasado'] : ['warn', 'Pendiente']);
                            @endphp
                            <tr>
                                <td class="strong">{{ $m->name }}</td>
                                <td><span class="dash-badge {{ $state[0] }}">{{ $state[1] }}</span></td>
                                <td class="num" style="color:var(--color-success); font-weight:600;">{{ $money($m->totalPaid) }}</td>
                                <td class="num">{{ $m->paymentsCount }}</td>
                                <td>{{ $m->lastPaymentDate ? \Carbon\Carbon::parse($m->lastPaymentDate)->format('d/m/Y') : '—' }}</td>
                                <td class="num">{{ $m->monthsDue }} <span style="color:var(--color-text-muted);">({{ $money($m->feesDue) }})</span></td>
                                <td class="num">{{ $money($m->penaltiesDue) }}</td>
                                <td class="num" style="font-weight:700; color: {{ $m->isUpToDate() ? 'var(--color-success)' : 'var(--color-error)' }};">{{ $money($m->totalDue()) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="strong" style="border-top:1px solid var(--color-border-subtle);">Totales</td>
                            <td style="border-top:1px solid var(--color-border-subtle);"></td>
                            <td class="num strong" style="border-top:1px solid var(--color-border-subtle);">{{ $money($kpis['collected']) }}</td>
                            <td class="num strong" style="border-top:1px solid var(--color-border-subtle);">{{ $kpis['payments_count'] }}</td>
                            <td style="border-top:1px solid var(--color-border-subtle);"></td>
                            <td class="num strong" style="border-top:1px solid var(--color-border-subtle);">{{ $kpis['months_due'] }} ({{ $money($kpis['fees_due']) }})</td>
                            <td class="num strong" style="border-top:1px solid var(--color-border-subtle);">{{ $money($kpis['penalties_due']) }}</td>
                            <td class="num strong" style="border-top:1px solid var(--color-border-subtle); color:var(--color-error);">{{ $money($kpis['total_due']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="padding:.75rem 1rem; border-top:1px solid var(--color-border); font-size:.75rem; color:var(--color-text-muted);">
                <strong>Al día</strong>: no debe nada · <strong>Pendiente</strong>: debe 1 o 2 cuotas · <strong>Atrasado</strong>: debe 3 cuotas o más.
                Los pagos registrados antes de que el sistema repartiera cada pago entre cuotas y mora aparecen como "históricos".
            </div>
        </x-ui.card>

    </x-layout.main>
</div>

<x-layout.footer />

</body>
</html>
