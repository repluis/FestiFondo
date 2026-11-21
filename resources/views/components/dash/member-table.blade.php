{{--
    Tabla de miembros con barra proporcional.
    mode = paid  → muestra total pagado
    mode = debt  → muestra deuda (cuotas + mora)
--}}
@props([
    'members' => [],
    'mode'    => 'paid',
    'empty'   => 'Sin datos',
])

@php
    $value = fn ($m) => $mode === 'debt' ? $m->totalDue() : $m->totalPaid;
    $max   = max(array_map($value, $members) ?: [0]) ?: 1;
    $color = $mode === 'debt' ? 'var(--color-error)' : 'var(--color-success)';
@endphp

@if (count($members) === 0)
    <div class="dash-empty">{{ $empty }}</div>
@else
    <div class="dash-rows">
        @foreach ($members as $i => $m)
            <div class="dash-row">
                <span class="dash-rank">{{ $i + 1 }}</span>
                <div class="dash-row-main">
                    <div class="dash-row-head">
                        <span class="dash-row-name">{{ $m->name }}</span>
                        <span class="dash-row-value" style="color: {{ $color }};">${{ number_format($value($m), 2) }}</span>
                    </div>
                    <div class="dash-bar"><div style="width: {{ round($value($m) / $max * 100, 1) }}%; background: {{ $color }};"></div></div>
                    <div class="dash-row-sub">
                        @if ($mode === 'debt')
                            {{ $m->monthsDue }} {{ $m->monthsDue === 1 ? 'cuota' : 'cuotas' }} (${{ number_format($m->feesDue, 2) }})
                            · mora ${{ number_format($m->penaltiesDue, 2) }}
                        @else
                            {{ $m->paymentsCount }} {{ $m->paymentsCount === 1 ? 'pago' : 'pagos' }}
                            · último {{ $m->lastPaymentDate ? \Carbon\Carbon::parse($m->lastPaymentDate)->format('d/m/Y') : '—' }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
