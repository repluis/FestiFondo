<!DOCTYPE html>
<html lang="es" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Transacciones — FestiFondo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }

        :root {
            --color-bg-base:        #0A0F1E;
            --color-bg-surface:     #0F172A;
            --color-bg-elevated:    #1E293B;
            --color-bg-overlay:     #263347;
            --color-primary:        #2563EB;
            --color-primary-hover:  #1D4ED8;
            --color-primary-light:  #3B82F6;
            --color-primary-subtle: #1E3A5F;
            --color-secondary:      #0EA5E9;
            --color-accent:         #06B6D4;
            --color-text-primary:   #F8FAFC;
            --color-text-secondary: #94A3B8;
            --color-text-muted:     #64748B;
            --color-text-disabled:  #475569;
            --color-border:         #1E293B;
            --color-border-subtle:  #334155;
            --color-success:    #10B981;
            --color-success-bg: #064E3B;
            --color-warning:    #F59E0B;
            --color-warning-bg: #451A03;
            --color-error:      #EF4444;
            --color-error-bg:   #450A0A;
            --color-info:       #3B82F6;
            --color-info-bg:    #1E3A5F;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.4);
            --shadow-md: 0 4px 12px rgba(0,0,0,.5);
            --shadow-lg: 0 8px 24px rgba(0,0,0,.6);
            --radius-sm: 4px; --radius-md: 8px; --radius-lg: 12px;
            --radius-xl: 16px; --radius-full: 9999px;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: flex-end;
            padding: 1rem 1.25rem;
            background: var(--color-bg-elevated);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .filter-group label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .filter-select, .filter-input {
            padding: 0.45rem 0.75rem;
            background: var(--color-bg-overlay);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-md);
            color: var(--color-text-primary);
            font-size: 0.875rem;
            min-width: 160px;
        }
        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: var(--color-primary-light);
        }
        .filter-input[type="date"] { min-width: 140px; color-scheme: dark; }

        .btn-filter {
            padding: 0.45rem 1.1rem;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-filter:hover { background: var(--color-primary-hover); }

        .btn-clear {
            padding: 0.45rem 0.9rem;
            background: transparent;
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: color 0.15s, border-color 0.15s;
        }
        .btn-clear:hover { color: var(--color-text-primary); border-color: var(--color-text-secondary); }

        .amount-income  { color: var(--color-success); font-weight: 600; }
        .amount-expense { color: var(--color-error);   font-weight: 600; }
    </style>
</head>
<body style="background: var(--color-bg-base); color: var(--color-text-primary); min-height: 100vh; display: flex; flex-direction: column;">

<x-layout.app-header />

<div style="display: flex; flex: 1;">

    <x-layout.app-sidebar />

    <x-layout.main>

        <x-ui.breadcrumb :items="[
            ['label' => 'Home',     'url' => '/'],
            ['label' => 'Reportes', 'url' => route('reports.index')],
            ['label' => 'Transacciones'],
        ]" />

        <div style="display:flex; align-items:center; justify-content:space-between; margin: 1.5rem 0;">
            <div>
                <h1 style="font-size:1.5rem; font-weight:700; color:var(--color-text-primary);">Reporte de Transacciones</h1>
                <p style="color:var(--color-text-muted); font-size:.875rem; margin-top:.2rem;">
                    Historial completo de transacciones con filtros por miembro y campaña
                </p>
            </div>
        </div>

        @isset($loadError)
            <x-ui.alert type="error" title="Error al cargar datos">{{ $loadError }}</x-ui.alert>
        @endisset

        {{-- ── Stats ──────────────────────────────────────────────────────────── --}}
        @php
            $totalIncome  = array_sum(array_map(fn($t) => $t->type === 'income'  ? $t->amount : 0, $transactions ?? []));
            $totalExpense = array_sum(array_map(fn($t) => $t->type === 'expense' ? $t->amount : 0, $transactions ?? []));
            $netBalance   = $totalIncome - $totalExpense;
            $totalCount   = count($transactions ?? []);
        @endphp
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
            <x-ui.card>
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Total registros</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;">{{ $totalCount }}</p>
            </x-ui.card>
            <x-ui.card>
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Total ingresos</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;color:var(--color-success);">
                    ${{ number_format($totalIncome, 2) }}
                </p>
            </x-ui.card>
            <x-ui.card>
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Total egresos</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;color:var(--color-error);">
                    ${{ number_format($totalExpense, 2) }}
                </p>
            </x-ui.card>
            <x-ui.card>
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Balance neto</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;
                    color:{{ $netBalance >= 0 ? 'var(--color-success)' : 'var(--color-error)' }};">
                    ${{ number_format($netBalance, 2) }}
                </p>
            </x-ui.card>
        </div>

        {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('reports.transactions') }}" id="filter-form">
            <div class="filter-bar">

                <div class="filter-group">
                    <label for="member_oid">Miembro</label>
                    <select name="member_oid" id="member_oid" class="filter-select">
                        <option value="">Todos los miembros</option>
                        @foreach($members ?? [] as $member)
                            <option value="{{ $member['oid'] }}"
                                {{ (isset($filters['member_oid']) && (int)$filters['member_oid'] === $member['oid']) ? 'selected' : '' }}>
                                {{ $member['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="campaign_oid">Campaña</label>
                    <select name="campaign_oid" id="campaign_oid" class="filter-select">
                        <option value="">Todas las campañas</option>
                        @foreach($campaigns ?? [] as $campaign)
                            <option value="{{ $campaign['oid'] }}"
                                {{ (isset($filters['campaign_oid']) && (int)$filters['campaign_oid'] === $campaign['oid']) ? 'selected' : '' }}>
                                {{ $campaign['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="type">Tipo</label>
                    <select name="type" id="type" class="filter-select" style="min-width:130px;">
                        <option value="">Todos</option>
                        <option value="income"  {{ ($filters['type'] ?? '') === 'income'  ? 'selected' : '' }}>Ingreso</option>
                        <option value="expense" {{ ($filters['type'] ?? '') === 'expense' ? 'selected' : '' }}>Egreso</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date_from">Desde</label>
                    <input type="date" name="date_from" id="date_from" class="filter-input"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>

                <div class="filter-group">
                    <label for="date_to">Hasta</label>
                    <input type="date" name="date_to" id="date_to" class="filter-input"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>

                <div style="display:flex; gap:.5rem; align-items:flex-end;">
                    <button type="submit" class="btn-filter">Filtrar</button>
                    <a href="{{ route('reports.transactions') }}" class="btn-clear">Limpiar</a>
                </div>

            </div>
        </form>

        {{-- ── Table ───────────────────────────────────────────────────────────── --}}
        <x-ui.card title="Transacciones">
            <div style="overflow-x:auto;">
            <x-ui.table :headers="['Fecha', 'Tipo', 'Miembro', 'Campaña', 'Monto', 'A mora', 'A cuotas', 'Mora ant.', 'Mora nueva', 'Cuotas ant.', 'Cuotas nueva', 'Descripción', 'Estado']">
                @forelse($transactions ?? [] as $tx)
                <tr style="border-bottom:1px solid var(--color-border); transition:background .1s;"
                    onmouseover="this.style.background='var(--color-bg-overlay)'"
                    onmouseout="this.style.background='transparent'">

                    <td style="padding:.875rem 1rem; color:var(--color-text-muted); font-size:.85rem; white-space:nowrap;">
                        {{ $tx->transactionDate }}
                    </td>

                    <td style="padding:.875rem 1rem;">
                        @if($tx->type === 'income')
                            <x-ui.badge variant="success">Ingreso</x-ui.badge>
                        @else
                            <x-ui.badge variant="error">Egreso</x-ui.badge>
                        @endif
                    </td>

                    <td style="padding:.875rem 1rem; color:var(--color-text-secondary); white-space:nowrap;">
                        {{ $tx->memberName ?? '—' }}
                    </td>

                    <td style="padding:.875rem 1rem; color:var(--color-text-secondary); white-space:nowrap;">
                        {{ $tx->campaignName ?? '—' }}
                    </td>

                    <td style="padding:.875rem 1rem; white-space:nowrap;">
                        <span class="{{ $tx->type === 'income' ? 'amount-income' : 'amount-expense' }}">
                            ${{ number_format($tx->amount, 2) }}
                        </span>
                    </td>

                    {{-- ── Audit snapshot ────────────────────────────────── --}}
                    <td style="padding:.875rem 1rem; text-align:right; white-space:nowrap;
                        color:{{ $tx->appliedToPenalties > 0 ? 'var(--color-warning)' : 'var(--color-text-disabled)' }}; font-size:.85rem;">
                        {{ $tx->appliedToPenalties !== null ? '$'.number_format($tx->appliedToPenalties, 2) : '—' }}
                    </td>

                    <td style="padding:.875rem 1rem; text-align:right; white-space:nowrap;
                        color:{{ $tx->appliedToFees > 0 ? 'var(--color-info)' : 'var(--color-text-disabled)' }}; font-size:.85rem;">
                        {{ $tx->appliedToFees !== null ? '$'.number_format($tx->appliedToFees, 2) : '—' }}
                    </td>

                    <td style="padding:.875rem 1rem; text-align:right; white-space:nowrap; color:var(--color-text-muted); font-size:.85rem;">
                        {{ $tx->previousPenaltiesBalance !== null ? '$'.number_format($tx->previousPenaltiesBalance, 2) : '—' }}
                    </td>

                    <td style="padding:.875rem 1rem; text-align:right; white-space:nowrap; color:var(--color-text-muted); font-size:.85rem;">
                        {{ $tx->newPenaltiesBalance !== null ? '$'.number_format($tx->newPenaltiesBalance, 2) : '—' }}
                    </td>

                    <td style="padding:.875rem 1rem; text-align:right; white-space:nowrap; color:var(--color-text-muted); font-size:.85rem;">
                        {{ $tx->previousFeesBalance !== null ? '$'.number_format($tx->previousFeesBalance, 2) : '—' }}
                    </td>

                    <td style="padding:.875rem 1rem; text-align:right; white-space:nowrap; color:var(--color-text-muted); font-size:.85rem;">
                        {{ $tx->newFeesBalance !== null ? '$'.number_format($tx->newFeesBalance, 2) : '—' }}
                    </td>
                    {{-- ── /Audit snapshot ───────────────────────────────── --}}

                    <td style="padding:.875rem 1rem; color:var(--color-text-primary); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                        title="{{ $tx->description }}">
                        {{ $tx->description }}
                    </td>

                    <td style="padding:.875rem 1rem;">
                        @if($tx->status)
                            <x-ui.badge variant="success">Activa</x-ui.badge>
                        @else
                            <x-ui.badge variant="muted">Cancelada</x-ui.badge>
                        @endif
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="13" style="padding:3rem 1rem; text-align:center; color:var(--color-text-muted);">
                        No se encontraron transacciones con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </x-ui.table>
            </div>
        </x-ui.card>

    </x-layout.main>
</div>

<x-layout.footer />

</body>
</html>
