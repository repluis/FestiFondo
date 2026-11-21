<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio — FestiFondo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-dash.styles />
</head>
<body>

<x-layout.app-header />

<div style="display: flex; flex: 1;">
    <x-layout.app-sidebar />

    <x-layout.main>

        <div class="dash-head">
            <div>
                <h1 class="dash-title">Hola, {{ auth()->user()->name }} 👋</h1>
                <p class="dash-subtitle">Resumen de las campañas y pagos al {{ now()->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="dash-link primary">📊 Ver dashboard completo</a>
        </div>

        {{-- ── Indicadores ─────────────────────────────────────────────── --}}
        <div class="dash-grid-kpi">
            <x-dash.kpi icon="💰" label="Total recaudado" :value="'$'.number_format($kpis['collected'], 2)"
                        :hint="$kpis['payments_count'].' pagos registrados'" color="var(--color-success)" />
            <x-dash.kpi icon="✅" label="Miembros al día" :value="$kpis['up_to_date'].' / '.$kpis['members']"
                        :hint="$kpis['with_debt'].' con deuda pendiente'" color="var(--color-primary-light)" />
            <x-dash.kpi icon="📅" label="Cuotas pendientes" :value="'$'.number_format($kpis['fees_due'], 2)"
                        :hint="$kpis['months_due'].' cuotas sin pagar'" color="var(--color-warning)" />
            <x-dash.kpi icon="⏰" label="Mora pendiente" :value="'$'.number_format($kpis['penalties_due'], 2)"
                        :hint="'Deuda total: $'.number_format($kpis['total_due'], 2)" color="var(--color-error)" />
        </div>

        {{-- ── Campañas ────────────────────────────────────────────────── --}}
        <x-ui.card title="📣 Campañas">
            @forelse ($campaigns as $c)
                @php
                    $badge = ['active' => ['ok', 'Activa'], 'draft' => ['info', 'Borrador'], 'completed' => ['info', 'Completada'], 'cancelled' => ['bad', 'Cancelada']][$c->statusLabel] ?? ['info', ucfirst($c->statusLabel)];
                @endphp
                <div style="{{ !$loop->last ? 'padding-bottom:1.25rem; margin-bottom:1.25rem; border-bottom:1px solid var(--color-border);' : '' }}">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:.75rem; flex-wrap:wrap; margin-bottom:.6rem;">
                        <div style="display:flex; align-items:center; gap:.6rem;">
                            <a href="{{ url('/v1/financial/campaigns/'.$c->uuid) }}" style="color:var(--color-text-primary); font-weight:600; text-decoration:none;">
                                {{ ucfirst(str_replace('_', ' ', $c->name)) }}
                            </a>
                            <span class="dash-badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                        </div>
                        <span style="font-size:.85rem; color:var(--color-text-secondary);">
                            <strong style="color:var(--color-success);">${{ number_format($c->collectedAmount, 2) }}</strong>
                            de ${{ number_format($c->targetAmount, 2) }} · {{ $c->percent() }}%
                        </span>
                    </div>
                    <div class="dash-progress"><div style="width: {{ $c->percent() }}%;"></div></div>
                    <div style="display:flex; justify-content:space-between; gap:.5rem; flex-wrap:wrap; margin-top:.5rem; font-size:.78rem; color:var(--color-text-muted);">
                        <span>👥 {{ $c->membersCount }} miembros · cuota ${{ number_format($c->monthlyFee, 2) }}/mes</span>
                        <span>
                            {{ $c->startDate ? \Carbon\Carbon::parse($c->startDate)->format('d/m/Y') : '—' }}
                            → {{ $c->endDate ? \Carbon\Carbon::parse($c->endDate)->format('d/m/Y') : '—' }}
                            · faltan ${{ number_format($c->remaining(), 2) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="dash-empty">No hay campañas registradas.</div>
            @endforelse
        </x-ui.card>

        <div style="height:1.5rem;"></div>

        {{-- ── Rankings resumidos ─────────────────────────────────────── --}}
        <div class="dash-grid-2">
            <x-ui.card title="🏆 Quienes más han pagado">
                <x-dash.member-table :members="$topPayers" mode="paid" />
            </x-ui.card>
            <x-ui.card title="⚠️ Quienes más deben">
                <x-dash.member-table :members="$topDebtors" mode="debt" empty="🎉 Todos están al día" />
            </x-ui.card>
        </div>

        {{-- ── Últimos pagos ──────────────────────────────────────────── --}}
        <x-ui.card title="🧾 Últimos pagos" padding="0">
            @if (count($recentPayments))
                <div class="dash-scroll">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Miembro</th>
                                <th class="num">Monto</th>
                                <th class="num">A cuotas</th>
                                <th class="num">A mora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPayments as $p)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($p['date'])->format('d/m/Y') }}</td>
                                    <td class="strong">{{ $p['member'] }}</td>
                                    <td class="num" style="color:var(--color-success); font-weight:600;">${{ number_format($p['amount'], 2) }}</td>
                                    <td class="num">${{ number_format($p['to_fees'], 2) }}</td>
                                    <td class="num">${{ number_format($p['to_penalties'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:.75rem 1rem; border-top:1px solid var(--color-border); text-align:right;">
                    <a href="{{ route('reports.transactions') }}" class="dash-link" style="font-size:.8rem;">Ver todas las transacciones →</a>
                </div>
            @else
                <div class="dash-empty">Aún no hay pagos registrados.</div>
            @endif
        </x-ui.card>

    </x-layout.main>
</div>

<x-layout.footer />

</body>
</html>
