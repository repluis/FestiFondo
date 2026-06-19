<!DOCTYPE html>
<html lang="es" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes — FestiFondo</title>
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

        .report-card {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 1.5rem;
            background: var(--color-bg-elevated);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-lg);
            text-decoration: none;
            transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
            cursor: pointer;
        }
        .report-card:hover {
            border-color: var(--color-primary-light);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        .report-card-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: var(--radius-md);
            background: var(--color-primary-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.375rem;
        }
        .report-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text-primary);
        }
        .report-card-desc {
            font-size: 0.8125rem;
            color: var(--color-text-muted);
            line-height: 1.5;
        }
        .report-card-arrow {
            margin-top: auto;
            font-size: 0.8125rem;
            color: var(--color-primary-light);
            font-weight: 500;
        }
    </style>
</head>
<body style="background: var(--color-bg-base); color: var(--color-text-primary); min-height: 100vh; display: flex; flex-direction: column;">

<x-layout.app-header />

<div style="display: flex; flex: 1;">

    <x-layout.app-sidebar />

    <x-layout.main>

        <x-ui.breadcrumb :items="[
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Reportes'],
        ]" />

        <div style="margin: 1.5rem 0;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--color-text-primary);">Reportes</h1>
            <p style="color: var(--color-text-muted); font-size: .875rem; margin-top: .2rem;">
                Consulta y exporta información del sistema
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem;">

            <a href="{{ route('reports.transactions') }}" class="report-card">
                <div class="report-card-icon">📋</div>
                <div>
                    <p class="report-card-title">Reporte de Transacciones</p>
                    <p class="report-card-desc">
                        Visualiza todas las transacciones realizadas. Filtra por miembro, campaña, tipo y rango de fechas.
                    </p>
                </div>
                <span class="report-card-arrow">Ver reporte →</span>
            </a>

        </div>

    </x-layout.main>
</div>

<x-layout.footer />

</body>
</html>
