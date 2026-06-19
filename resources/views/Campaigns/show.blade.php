<!DOCTYPE html>
<html lang="en" style="background:var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $campaign['name'] }} — FestiFondo</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Instrument Sans',ui-sans-serif,system-ui,sans-serif;}
        :root{
            --color-bg-base:#0A0F1E;--color-bg-surface:#0F172A;--color-bg-elevated:#1E293B;
            --color-bg-overlay:#263347;--color-primary:#2563EB;--color-primary-hover:#1D4ED8;
            --color-primary-light:#3B82F6;--color-primary-subtle:#1E3A5F;
            --color-secondary:#0EA5E9;--color-accent:#06B6D4;
            --color-text-primary:#F8FAFC;--color-text-secondary:#94A3B8;
            --color-text-muted:#64748B;--color-text-disabled:#475569;
            --color-border:#1E293B;--color-border-subtle:#334155;
            --color-success:#10B981;--color-success-bg:#064E3B;
            --color-warning:#F59E0B;--color-warning-bg:#451A03;
            --color-error:#EF4444;--color-error-bg:#450A0A;
            --color-info:#3B82F6;--color-info-bg:#1E3A5F;
            --shadow-sm:0 1px 3px rgba(0,0,0,.4);--shadow-md:0 4px 12px rgba(0,0,0,.5);
            --shadow-lg:0 8px 24px rgba(0,0,0,.6);
            --radius-sm:4px;--radius-md:8px;--radius-lg:12px;--radius-xl:16px;--radius-full:9999px;
        }
        body{background:var(--color-bg-base);color:var(--color-text-primary);min-height:100vh;display:flex;flex-direction:column;}
        .badge{display:inline-flex;align-items:center;padding:.2rem .7rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:600;text-transform:capitalize;}
        .badge-draft{background:#1e293b;color:#94a3b8;} .badge-active{background:var(--color-success-bg);color:var(--color-success);}
        .badge-completed{background:var(--color-info-bg);color:var(--color-info);} .badge-cancelled{background:var(--color-error-bg);color:var(--color-error);}
        .cards-row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem;}
        @media(max-width:900px){.cards-row{grid-template-columns:1fr 1fr;}}
        .stat-card{background:var(--color-bg-surface);border:1px solid var(--color-border);border-radius:var(--radius-xl);padding:1.1rem 1.25rem;}
        .stat-card .lbl{font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.35rem;}
        .stat-card .val{font-size:1.3rem;font-weight:700;}
        .progress-bar-bg{background:var(--color-bg-overlay);border-radius:var(--radius-full);height:6px;overflow:hidden;}
        .progress-bar-fill{height:100%;border-radius:var(--radius-full);background:linear-gradient(90deg,var(--color-primary),var(--color-accent));}
        .card{background:var(--color-bg-elevated);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;margin-bottom:1.5rem;}
        .card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;flex-wrap:wrap;gap:.75rem;}
        .card-title{font-size:.875rem;font-weight:700;color:var(--color-text-secondary);text-transform:uppercase;letter-spacing:.06em;}
        .data-table{width:100%;border-collapse:collapse;}
        .data-table th{text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;letter-spacing:.07em;color:var(--color-text-muted);text-transform:uppercase;border-bottom:1px solid var(--color-border);}
        .data-table td{padding:.75rem 1rem;font-size:.875rem;border-bottom:1px solid var(--color-border);color:var(--color-text-secondary);vertical-align:middle;}
        .data-table tr:last-child td{border-bottom:none;} .data-table tr:hover td{background:rgba(255,255,255,.02);}
        .btn{display:inline-flex;align-items:center;gap:.3rem;padding:.4rem .85rem;border-radius:var(--radius-md);font-size:.84rem;font-weight:500;cursor:pointer;border:none;transition:all .15s;text-decoration:none;}
        .btn-primary{background:var(--color-primary);color:#fff;} .btn-primary:hover{background:var(--color-primary-hover);}
        .btn-ghost{background:transparent;color:var(--color-text-secondary);border:1px solid var(--color-border-subtle);} .btn-ghost:hover{background:var(--color-bg-elevated);}
        .btn-danger{background:transparent;color:var(--color-error);border:1px solid var(--color-error);} .btn-danger:hover{background:var(--color-error-bg);}
        .btn:disabled{opacity:.5;cursor:not-allowed;}
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:50;align-items:center;justify-content:center;padding:1rem;}
        .modal-overlay.open{display:flex;}
        .modal{background:var(--color-bg-elevated);border:1px solid var(--color-border-subtle);border-radius:var(--radius-xl);padding:1.75rem;width:100%;max-width:500px;box-shadow:var(--shadow-md);}
        .modal h3{font-size:1.05rem;font-weight:600;margin-bottom:1.2rem;}
        .form-group{margin-bottom:.9rem;}
        .form-group label{display:block;font-size:.8rem;font-weight:500;color:var(--color-text-secondary);margin-bottom:.3rem;}
        .form-control{width:100%;padding:.5rem .7rem;background:var(--color-bg-overlay);border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);color:var(--color-text-primary);font-size:.875rem;outline:none;}
        .form-control:focus{border-color:var(--color-primary);}
        .field-error{font-size:.78rem;color:var(--color-error);margin-top:.2rem;display:none;}
        .field-error.show{display:block;}
        .alert{padding:.7rem 1rem;border-radius:var(--radius-md);font-size:.875rem;margin-bottom:.9rem;}
        .alert-error{background:var(--color-error-bg);color:var(--color-error);}
        .alert-success{background:var(--color-success-bg);color:var(--color-success);}
        .chk-list{max-height:220px;overflow-y:auto;border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);padding:.4rem;}
        .chk-item{display:flex;align-items:center;gap:.6rem;padding:.35rem .5rem;border-radius:var(--radius-md);cursor:pointer;}
        .chk-item:hover{background:var(--color-bg-overlay);}
        .chk-item input[type=checkbox]{accent-color:var(--color-primary);width:14px;height:14px;}
        .chk-item label{cursor:pointer;font-size:.86rem;color:var(--color-text-secondary);}
        #toast{position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:.75rem 1.25rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;box-shadow:var(--shadow-lg);display:none;}
    </style>
</head>
<body>

<x-layout.app-header />

<div style="display:flex;flex:1;">
<x-layout.app-sidebar />
<x-layout.main>

    <x-ui.breadcrumb :items="[
        ['label'=>'Home',      'url'=>'/'],
        ['label'=>'Financial', 'url'=>'#'],
        ['label'=>'Campaigns', 'url'=>'/v1/financial/campaigns'],
        ['label'=>$campaign['name']],
    ]" />

    {{-- Page title --}}
    <div style="display:flex;align-items:center;gap:1rem;margin:1.5rem 0;flex-wrap:wrap;">
        <h1 style="font-size:1.5rem;font-weight:700;flex:1;">{{ $campaign['name'] }}</h1>
        <span class="badge badge-{{ $campaign['campaign_status'] }}">{{ ucfirst($campaign['campaign_status']) }}</span>
        @if($campaign['campaign_status'] === 'draft')
            <button class="btn btn-primary" id="btn-activate" onclick="activateCampaign()"
                    style="background:var(--color-success);">
                ▶ Activar campaña
            </button>
        @endif
        <a href="{{ route('campaigns.edit', $campaign['uuid']) }}" class="btn btn-ghost">Editar</a>
        <button class="btn" id="btn-share"
            onclick="copyWhatsApp()"
            style="background:#25D366;color:#fff;gap:.4rem;"
            title="Copiar resumen para WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            Compartir
        </button>
    </div>

    @php
        $pct       = $campaign['target_amount'] > 0
            ? min(100, round(($campaign['collected_amount'] / $campaign['target_amount']) * 100, 1))
            : 0;
        $remaining = max(0, $campaign['target_amount'] - $campaign['collected_amount']);
    @endphp

    {{-- Stats --}}
    <div class="cards-row">
        <div class="stat-card">
            <div class="lbl">Target</div>
            <div class="val" style="color:var(--color-accent);">${{ number_format($campaign['target_amount'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Collected</div>
            <div class="val" style="color:var(--color-success);">${{ number_format($campaign['collected_amount'], 2) }}</div>
            <div class="progress-bar-bg" style="margin-top:.5rem;"><div class="progress-bar-fill" style="width:{{ $pct }}%;"></div></div>
            <small style="color:var(--color-text-muted);font-size:.72rem;">{{ $pct }}%</small>
        </div>
        <div class="stat-card">
            <div class="lbl">Remaining</div>
            <div class="val" style="color:var(--color-warning);">${{ number_format($remaining, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Members</div>
            <div class="val" style="color:var(--color-primary-light);">{{ count($members) }}</div>
        </div>
    </div>

    {{-- ── Billing & Penalty Update ──────────────────────────────────────── --}}
    @php
        $lastExec = $lastExecution ?? null;
        $ranToday = $lastExec
            && \Carbon\Carbon::parse($lastExec->execution_date)->isToday()
            && $lastExec->execution_status === 'completed';
    @endphp
    <div style="margin-bottom:1.5rem;padding:1.25rem 1.5rem;
        background:linear-gradient(135deg,var(--color-primary-subtle),var(--color-bg-elevated));
        border:1px solid var(--color-primary);border-radius:var(--radius-lg);
        display:flex;align-items:center;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;">
        <div>
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:.4rem;">
                Billing &amp; Penalty Update
            </h2>
            @if($lastExec)
                <p style="font-size:.85rem;color:var(--color-text-secondary);">
                    Last run:
                    <strong style="color:var(--color-text-primary);">
                        {{ \Carbon\Carbon::parse($lastExec->execution_date)->format('M d, Y') }}
                    </strong>
                    — {{ $lastExec->fees_generated }} fees,
                    {{ $lastExec->penalties_generated }} penalties,
                    {{ $lastExec->members_processed }} members
                    @if($ranToday)
                        <span style="color:var(--color-success);font-weight:600;margin-left:.5rem;">
                            ✓ Already ran today
                        </span>
                    @endif
                </p>
            @else
                <p style="font-size:.85rem;color:var(--color-text-muted);">
                    No executions recorded yet.
                </p>
            @endif
        </div>
        <button class="btn btn-primary" id="btn-process" style="white-space:nowrap;"
            onclick="{{ $ranToday ? 'openModal(\'modal-rerun\')' : 'runProcess()' }}">
            ⟳ Actualizar Cobros y Moras
        </button>
    </div>

    {{-- ── Members table ───────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Enrolled Members</span>
            <button class="btn btn-primary" onclick="openAddModal()">+ Add Member</button>
        </div>

        <div id="members-alert" style="display:none;" class="alert alert-success"></div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>CI / ID</th>
                        <th>Full Name</th>
                        <th>Fees Pending</th>
                        <th>Penalties</th>
                        <th>Total Balance</th>
                        <th>Paid in Campaign</th>
                        <th>Penalties Paid</th>
                        <th>Last Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($members as $m)
                @php
                    $hasDebt = ($m['total_balance'] ?? 0) > 0;
                    $fb = (float)($m['fees_balance'] ?? 0);
                    $pb = (float)($m['penalties_balance'] ?? 0);
                    $tb = (float)($m['total_balance'] ?? 0);
                    $tp = (float)($m['total_paid_in_campaign'] ?? 0);
                    $pp = (float)($m['penalties_paid'] ?? 0);
                @endphp
                <tr>
                    <td style="font-weight:600;color:var(--color-accent);">{{ $m['identification'] ?? '—' }}</td>
                    <td style="font-weight:500;color:var(--color-text-primary);">
                        {{ ($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '') }}
                    </td>
                    <td>
                        <span style="color:{{ $fb > 0 ? 'var(--color-warning)' : 'var(--color-success)' }};font-weight:600;">
                            ${{ number_format($fb, 2) }}
                        </span>
                    </td>
                    <td>
                        <span style="color:{{ $pb > 0 ? 'var(--color-error)' : 'var(--color-text-muted)' }};">
                            ${{ number_format($pb, 2) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-weight:700;color:{{ $tb > 0 ? 'var(--color-error)' : 'var(--color-success)' }};">
                            ${{ number_format($tb, 2) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-weight:600;color:{{ $tp > 0 ? 'var(--color-success)' : 'var(--color-text-muted)' }};">
                            ${{ number_format($tp, 2) }}
                        </span>
                    </td>
                    <td>
                        <span style="color:{{ $pp > 0 ? 'var(--color-error)' : 'var(--color-text-muted)' }};">
                            ${{ number_format($pp, 2) }}
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:var(--color-text-muted);">{{ $m['last_payment_date'] ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:.35rem;">
                            <button class="btn btn-ghost" onclick="openHistory(
                                {{ $m['member_oid'] }},
                                '{{ addslashes(($m['first_name'] ?? '').' '.($m['last_name'] ?? '')) }}'
                            )">History</button>
                            @if($hasDebt)
                            <button class="btn btn-primary" onclick="openPayModal(
                                {{ $m['member_oid'] }},
                                '{{ addslashes(($m['first_name'] ?? '').' '.($m['last_name'] ?? '')) }}',
                                {{ $tb }}, {{ $fb }}, {{ $pb }},
                                {{ $campaign['oid'] }}
                            )">Pay</button>
                            @endif
                            <button class="btn btn-danger" onclick="confirmRemove('{{ $m['cm_uuid'] }}','{{ addslashes(($m['first_name'] ?? '').' '.($m['last_name'] ?? '')) }}')">Remove</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="padding:2.5rem 1rem;text-align:center;color:var(--color-text-muted);">
                        No members enrolled yet. Click "+ Add Member" to start.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layout.main>
</div>

<x-layout.footer />

{{-- ── Modals ──────────────────────────────────────────────────────────────── --}}

{{-- Add Member --}}
<div class="modal-overlay" id="modal-add">
    <div class="modal">
        <h3>Add Members</h3>
        <div id="add-err" class="alert alert-error" style="display:none;"></div>
        <div class="form-group">
            <label>Search</label>
            <input class="form-control" id="add-search" placeholder="Name or CI…" oninput="filterAvail()">
        </div>
        <div class="form-group">
            <label>Available members</label>
            <div class="chk-list" id="avail-list">
                <p style="color:var(--color-text-muted);padding:.5rem;">Loading…</p>
            </div>
        </div>
        <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:1rem;">
            <button class="btn btn-ghost" onclick="closeModal('modal-add')">Cancel</button>
            <button class="btn btn-primary" id="btn-enroll" onclick="submitEnroll()">Enroll Selected</button>
        </div>
    </div>
</div>

{{-- Remove confirm --}}
<div class="modal-overlay" id="modal-remove">
    <div class="modal" style="max-width:380px;">
        <h3>Remove Member</h3>
        <p style="color:var(--color-text-secondary);margin-bottom:1.5rem;">
            Remove <strong id="remove-name"></strong> from this campaign? Their payment history is preserved.
        </p>
        <div style="display:flex;gap:.6rem;justify-content:flex-end;">
            <button class="btn btn-ghost" onclick="closeModal('modal-remove')">Cancel</button>
            <button class="btn btn-danger" id="btn-remove" onclick="submitRemove()">Remove</button>
        </div>
    </div>
</div>

{{-- Pay --}}
<div class="modal-overlay" id="modal-pay">
    <div class="modal">
        <h3>Pay — <span id="pay-name" style="color:var(--color-accent);"></span></h3>
        <div id="pay-err" class="alert alert-error" style="display:none;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;background:var(--color-bg-overlay);border-radius:var(--radius-md);padding:.8rem;margin-bottom:.9rem;font-size:.84rem;">
            <div><span style="color:var(--color-text-muted);">Fees:</span> <strong id="pi-fees" style="color:var(--color-warning);"></strong></div>
            <div><span style="color:var(--color-text-muted);">Penalties:</span> <strong id="pi-pen" style="color:var(--color-error);"></strong></div>
            <div style="grid-column:1/-1;border-top:1px solid var(--color-border);padding-top:.4rem;margin-top:.2rem;">
                <span style="color:var(--color-text-muted);">Total due:</span>
                <strong id="pi-total" style="color:var(--color-error);"></strong>
            </div>
        </div>
        <div class="form-group">
            <label>Amount</label>
            <input type="number" class="form-control" id="pay-amount" step="0.01" min="0.01" oninput="updateHint()">
            <span class="field-error" id="err-amount"></span>
            <small id="pay-hint" style="color:var(--color-text-muted);font-size:.78rem;margin-top:.2rem;display:block;"></small>
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="date" class="form-control" id="pay-date">
        </div>
        <div class="form-group">
            <label>Notes</label>
            <input class="form-control" id="pay-notes" placeholder="Optional note…">
        </div>
        <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.5rem;">
            <button class="btn btn-ghost" onclick="closeModal('modal-pay')">Cancel</button>
            <button class="btn btn-primary" id="btn-pay" onclick="submitPayment()">Apply Payment</button>
        </div>
    </div>
</div>

{{-- Pay Result --}}
<div class="modal-overlay" id="modal-pay-result">
    <div class="modal" style="max-width:400px;">
        <h3>Payment Registered</h3>
        <div id="pay-result" style="line-height:1.8;margin-bottom:1.2rem;font-size:.9rem;"></div>
        <div style="text-align:right;">
            <button class="btn btn-primary" onclick="closeModal('modal-pay-result');location.reload();">Close & Refresh</button>
        </div>
    </div>
</div>

{{-- Payment History --}}
<div class="modal-overlay" id="modal-history" onclick="if(event.target===this)closeModal('modal-history')">
    <div class="modal" style="max-width:700px;width:100%;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
            <h3 style="margin-bottom:0;">Payment History — <span id="history-name" style="color:var(--color-accent);"></span></h3>
            <button onclick="closeModal('modal-history')" style="background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:1.3rem;line-height:1;">&times;</button>
        </div>
        <div id="history-body" style="overflow-x:auto;">
            <p style="color:var(--color-text-muted);padding:1rem 0;text-align:center;">Loading…</p>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
            <button class="btn btn-ghost" onclick="closeModal('modal-history')">Close</button>
        </div>
    </div>
</div>

{{-- Re-run confirm --}}
<div class="modal-overlay" id="modal-rerun">
    <div class="modal" style="max-width:380px;">
        <h3>Already Ran Today</h3>
        <p style="color:var(--color-text-secondary);margin-bottom:1.5rem;font-size:.9rem;">
            This process already ran today. Running again is safe — no duplicate fees or penalties will be created. Continue?
        </p>
        <div style="display:flex;gap:.6rem;justify-content:flex-end;">
            <button class="btn btn-ghost" onclick="closeModal('modal-rerun')">Cancel</button>
            <button class="btn btn-primary" onclick="closeModal('modal-rerun');runProcess()">Run Anyway</button>
        </div>
    </div>
</div>

{{-- Process Result --}}
<div class="modal-overlay" id="modal-process-result">
    <div class="modal" style="max-width:380px;">
        <h3>Process Result</h3>
        <div id="process-result-body" style="color:var(--color-text-secondary);font-size:.9rem;line-height:1.8;margin-bottom:1.2rem;"></div>
        <div style="text-align:right;">
            <button class="btn btn-primary" onclick="closeModal('modal-process-result');location.reload();">OK — Refresh</button>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
const CAMP_NAME  = '{{ addslashes($campaign["name"]) }}';
@php
$membersForJs = array_map(fn($m) => [
    'name'          => trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')),
    'total_balance' => (float)($m['total_balance'] ?? 0),
    'total_paid'    => (float)($m['total_paid_in_campaign'] ?? 0),
], $members);
@endphp
const MEMBERS_DATA = @json($membersForJs);

function copyWhatsApp() {
    const fmt    = n => '$' + n.toFixed(2);
    const paid   = MEMBERS_DATA.filter(m => m.total_balance <= 0).sort((a,b) => a.name.localeCompare(b.name));
    const debted = MEMBERS_DATA.filter(m => m.total_balance >  0).sort((a,b) => b.total_balance - a.total_balance);

    const totalCollected = MEMBERS_DATA.reduce((s, m) => s + m.total_paid, 0);
    const totalOwed      = MEMBERS_DATA.reduce((s, m) => s + m.total_balance, 0);

    let text = '📊 *Transparencia Financiera*\n';
    text += '📣 ' + CAMP_NAME + '\n';
    text += '💰 Recaudado: ' + fmt(totalCollected) + '   |   ⚠️ Deuda total: ' + fmt(totalOwed) + '\n\n';

    if (paid.length) {
        text += '✅ *Al día:*\n';
        paid.forEach(m => {
            text += `• ${m.name} — Pagado: ${fmt(m.total_paid)}\n`;
        });
    }

    if (debted.length) {
        text += '\n⚠️ *Con deuda:*\n';
        debted.forEach(m => {
            text += `• ${m.name} — Debe: ${fmt(m.total_balance)}\n`;
        });
    }

    navigator.clipboard.writeText(text.trim()).then(() => {
        const btn = document.getElementById('btn-share');
        const orig = btn.innerHTML;
        btn.innerHTML = '✓ ¡Copiado!';
        btn.style.background = 'var(--color-success)';
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.style.background = '#25D366';
        }, 2500);
    }).catch(() => {
        showToast('No se pudo copiar al portapapeles.', 'error');
    });
}
const ENROLL_URL = '{{ route("campaigns.members.enroll",  $campaign["uuid"]) }}';
const AVAIL_URL  = '{{ route("campaigns.members.available",$campaign["uuid"]) }}';
const REMOVE_BASE= '{{ url("v1/financial/campaigns/".$campaign["uuid"]."/members") }}';
const PAY_URL    = '{{ route("transactions.apply-payment") }}';
const PROCESS_URL= '{{ route("fund-raising.process-charges") }}';

let _avail = [], _rmUuid = null;
let _payMbr = null, _payTotal = 0, _payFees = 0, _payPen = 0, _payCmp = null;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.style.background = type === 'success' ? 'var(--color-success-bg)' : 'var(--color-error-bg)';
    t.style.color      = type === 'success' ? 'var(--color-success)'    : 'var(--color-error)';
    t.style.display    = 'block';
    t.textContent      = msg;
    setTimeout(() => { t.style.display = 'none'; }, 3500);
}

// ── Activate Campaign ─────────────────────────────────────────────────────────
async function activateCampaign() {
    const btn = document.getElementById('btn-activate');
    btn.disabled = true; btn.textContent = 'Activando…';
    try {
        const r = await fetch(UPDATE_URL, {
            method: 'PUT',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify({ ...CAMP_PAYLOAD, campaign_status: 'active' }),
        });
        const j = await r.json();
        if (r.ok && j.status) {
            showToast('Campaña activada exitosamente. ✓', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(j.message || 'Error al activar la campaña.', 'error');
            btn.disabled = false; btn.textContent = '▶ Activar campaña';
        }
    } catch {
        showToast('Error de conexión.', 'error');
        btn.disabled = false; btn.textContent = '▶ Activar campaña';
    }
}

// ── Process Charges ──────────────────────────────────────────────────────────
async function runProcess() {
    const btn = document.getElementById('btn-process');
    btn.disabled = true; btn.textContent = 'Processing…';
    try {
        const r = await fetch(PROCESS_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify({ campaign_uuid: CAMP_UUID }),
        });
        const j = await r.json();
        if (r.ok && j.status) {
            const d = j.data;
            document.getElementById('process-result-body').innerHTML =
                `<strong style="color:var(--color-success);">Process completed ✓</strong><br>
                Members processed: <strong>${d.members_processed ?? 0}</strong><br>
                Fees generated: <strong>${d.fees_generated ?? 0}</strong><br>
                Penalties generated: <strong>${d.penalties_generated ?? 0}</strong>`;
            openModal('modal-process-result');
        } else if (r.status === 409) {
            showToast(j.message || 'Process locked.', 'error');
        } else {
            showToast(j.message || 'Error running process.', 'error');
        }
    } catch { showToast('Connection error.', 'error'); }
    finally { btn.disabled = false; btn.textContent = '⟳ Actualizar Cobros y Moras'; }
}

// ── Add Member ───────────────────────────────────────────────────────────────
async function openAddModal() {
    document.getElementById('add-err').style.display = 'none';
    document.getElementById('add-search').value = '';
    openModal('modal-add');
    const list = document.getElementById('avail-list');
    list.innerHTML = '<p style="color:var(--color-text-muted);padding:.5rem;">Loading…</p>';
    try {
        const r = await fetch(AVAIL_URL, {headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}});
        const j = await r.json();
        _avail = j.status ? j.data : [];
        renderAvail(_avail);
    } catch {
        list.innerHTML = '<p style="color:var(--color-error);padding:.5rem;">Error loading members.</p>';
    }
}

function renderAvail(list) {
    const el = document.getElementById('avail-list');
    if (!list.length) {
        el.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:.75rem;">No available members.</p>';
        return;
    }
    el.innerHTML = list.map(m => `
        <div class="chk-item">
            <input type="checkbox" id="c${m.oid}" value="${m.oid}">
            <label for="c${m.oid}">${m.first_name} ${m.last_name} <span style="color:var(--color-text-muted);font-size:.78rem;">${m.identification}</span></label>
        </div>`).join('');
}

function filterAvail() {
    const q = document.getElementById('add-search').value.toLowerCase();
    renderAvail(_avail.filter(m =>
        (m.first_name+' '+m.last_name+' '+m.identification).toLowerCase().includes(q)));
}

async function submitEnroll() {
    const checked = [...document.querySelectorAll('#avail-list input:checked')];
    const err = document.getElementById('add-err');
    if (!checked.length) { err.textContent='Select at least one member.'; err.style.display='block'; return; }
    err.style.display='none';
    const btn = document.getElementById('btn-enroll');
    btn.disabled=true; btn.textContent='Enrolling…';
    try {
        const r = await fetch(ENROLL_URL,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({member_oids:checked.map(c=>parseInt(c.value))})
        });
        const j = await r.json();
        if(r.ok && j.status){
            closeModal('modal-add');
            showBanner(j.message||'Members enrolled.');
            setTimeout(()=>location.reload(),1200);
        } else {
            err.textContent=j.message||'Error enrolling.'; err.style.display='block';
        }
    } catch { err.textContent='Connection error.'; err.style.display='block'; }
    finally { btn.disabled=false; btn.textContent='Enroll Selected'; }
}

// ── Remove Member ────────────────────────────────────────────────────────────
function confirmRemove(uuid, name) {
    _rmUuid = uuid;
    document.getElementById('remove-name').textContent = name;
    openModal('modal-remove');
}

async function submitRemove() {
    const btn = document.getElementById('btn-remove');
    btn.disabled=true; btn.textContent='Removing…';
    try {
        const r = await fetch(REMOVE_BASE+'/'+_rmUuid,{
            method:'DELETE',
            headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
        });
        const j = await r.json();
        if(r.ok && j.status){
            closeModal('modal-remove');
            showBanner('Member removed from campaign.');
            setTimeout(()=>location.reload(),1000);
        } else { showToast(j.message||'Error removing.','error'); }
    } catch { showToast('Connection error.','error'); }
    finally { btn.disabled=false; btn.textContent='Remove'; }
}

// ── Pay Member ───────────────────────────────────────────────────────────────
function openPayModal(memberOid, name, total, fees, pen, campaignOid) {
    _payMbr=memberOid; _payTotal=total; _payFees=fees; _payPen=pen; _payCmp=campaignOid;
    document.getElementById('pay-name').textContent      = name;
    document.getElementById('pi-fees').textContent       = '$'+fees.toFixed(2);
    document.getElementById('pi-pen').textContent        = '$'+pen.toFixed(2);
    document.getElementById('pi-total').textContent      = '$'+total.toFixed(2);
    document.getElementById('pay-amount').value          = total.toFixed(2);
    document.getElementById('pay-date').value            = new Date().toISOString().slice(0,10);
    document.getElementById('pay-notes').value           = '';
    document.getElementById('pay-err').style.display     = 'none';
    document.getElementById('err-amount').classList.remove('show');
    updateHint();
    openModal('modal-pay');
}

function updateHint() {
    const hint = document.getElementById('pay-hint');
    const amt  = parseFloat(document.getElementById('pay-amount').value)||0;
    if(amt<=0){hint.textContent='';return;}
    if(amt>=_payTotal){hint.style.color='var(--color-success)';hint.textContent='Covers full balance.';}
    else{hint.style.color='var(--color-warning)';hint.textContent=`Partial — $${(_payTotal-amt).toFixed(2)} will remain.`;}
}

async function submitPayment() {
    document.getElementById('pay-err').style.display='none';
    document.getElementById('err-amount').classList.remove('show');
    const amt = parseFloat(document.getElementById('pay-amount').value);
    if(!amt||amt<=0){ const el=document.getElementById('err-amount'); el.textContent='Amount must be > 0.'; el.classList.add('show'); return; }
    const btn=document.getElementById('btn-pay');
    btn.disabled=true; btn.textContent='Applying…';
    try {
        const r = await fetch(PAY_URL,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({
                member_oid:_payMbr, campaign_oid:_payCmp,
                amount:amt, transaction_date:document.getElementById('pay-date').value,
                notes:document.getElementById('pay-notes').value.trim()||null
            })
        });
        const j = await r.json();
        if(r.ok && j.status){
            closeModal('modal-pay');
            const d=j.data;
            document.getElementById('pay-result').innerHTML=
                `<strong style="color:var(--color-success);">Payment registered ✓</strong><br>
                Amount: <strong>$${d.amount_paid.toFixed(2)}</strong><br>
                To penalties: <span style="color:var(--color-error);">$${d.applied_to_penalties.toFixed(2)}</span><br>
                To fees: <span style="color:var(--color-warning);">$${d.applied_to_fees.toFixed(2)}</span>
                ${d.overpayment>0?`<br><span style="color:var(--color-info);">Overpayment: $${d.overpayment.toFixed(2)}</span>`:''}`;
            openModal('modal-pay-result');
        } else if(r.status===422 && j.errors){
            Object.entries(j.errors).forEach(([f,m])=>{
                if(f==='amount'){const el=document.getElementById('err-amount');el.textContent=m[0];el.classList.add('show');}
            });
        } else {
            const e=document.getElementById('pay-err');
            e.textContent=j.message||'Unexpected error.'; e.style.display='block';
        }
    } catch { const e=document.getElementById('pay-err'); e.textContent='Connection error.'; e.style.display='block'; }
    finally { btn.disabled=false; btn.textContent='Apply Payment'; }
}

function showBanner(msg){
    const el=document.getElementById('members-alert');
    el.textContent=msg; el.style.display='block';
    setTimeout(()=>el.style.display='none',3000);
}

// ── Payment History ───────────────────────────────────────────────────────────
const CAMP_UUID    = '{{ $campaign["uuid"] }}';
const UPDATE_URL   = '{{ route("campaigns.update", $campaign["uuid"]) }}';
const CAMP_PAYLOAD = {
    name:                '{{ addslashes($campaign["name"]) }}',
    description:         '{{ addslashes($campaign["description"] ?? "") }}',
    target_amount:       {{ (float)($campaign["target_amount"] ?? 0) }},
    monthly_fee_amount:  {{ (float)($campaign["monthly_fee_amount"] ?? 1.00) }},
    daily_penalty_rate:  {{ (float)($campaign["daily_penalty_rate"] ?? 0.05) }},
    due_day:             {{ (int)($campaign["due_day"] ?? 15) }},
    start_date:          '{{ $campaign["start_date"] ?? "" }}',
    end_date:            '{{ $campaign["end_date"] ?? "" }}',
};

async function openHistory(memberOid, name) {
    document.getElementById('history-name').textContent = name;
    document.getElementById('history-body').innerHTML =
        '<p style="color:var(--color-text-muted);padding:1rem 0;text-align:center;">Loading…</p>';
    openModal('modal-history');

    try {
        const r = await fetch(
            `/v1/financial/campaigns/${CAMP_UUID}/members/${memberOid}/transactions`,
            { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } }
        );
        const j = await r.json();
        const rows = j.status ? j.data : [];

        if (!rows.length) {
            document.getElementById('history-body').innerHTML =
                '<p style="color:var(--color-text-muted);padding:1.5rem 0;text-align:center;">No payments recorded for this member in this campaign.</p>';
            return;
        }

        const totalPaid = rows.reduce((s, t) => s + parseFloat(t.amount || 0), 0);

        document.getElementById('history-body').innerHTML = `
            <div style="margin-bottom:.75rem;display:flex;gap:1.5rem;font-size:.84rem;">
                <span style="color:var(--color-text-muted);">Payments: <strong style="color:var(--color-text-primary);">${rows.length}</strong></span>
                <span style="color:var(--color-text-muted);">Total: <strong style="color:var(--color-success);">$${totalPaid.toFixed(2)}</strong></span>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:.84rem;">
                <thead>
                    <tr style="border-bottom:1px solid var(--color-border);">
                        <th style="text-align:left;padding:.4rem .75rem;font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.06em;">#</th>
                        <th style="text-align:left;padding:.4rem .75rem;font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.06em;">Date</th>
                        <th style="text-align:right;padding:.4rem .75rem;font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.06em;">Amount</th>
                        <th style="text-align:left;padding:.4rem .75rem;font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.06em;">Type</th>
                        <th style="text-align:left;padding:.4rem .75rem;font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.06em;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map((t, i) => `
                    <tr style="border-bottom:1px solid var(--color-border);">
                        <td style="padding:.6rem .75rem;color:var(--color-text-muted);font-size:.78rem;">${i + 1}</td>
                        <td style="padding:.6rem .75rem;color:var(--color-text-secondary);">${t.transaction_date ?? '—'}</td>
                        <td style="padding:.6rem .75rem;text-align:right;font-weight:700;color:var(--color-success);">$${parseFloat(t.amount || 0).toFixed(2)}</td>
                        <td style="padding:.6rem .75rem;">
                            <span style="font-size:.75rem;padding:.15rem .55rem;border-radius:9999px;background:var(--color-success-bg);color:var(--color-success);">
                                ${t.transaction_type ?? 'income'}
                            </span>
                        </td>
                        <td style="padding:.6rem .75rem;color:var(--color-text-muted);font-size:.8rem;">${t.notes ?? '—'}</td>
                    </tr>`).join('')}
                </tbody>
            </table>`;
    } catch {
        document.getElementById('history-body').innerHTML =
            '<p style="color:var(--color-error);padding:1rem 0;text-align:center;">Error loading history.</p>';
    }
}
</script>
</body>
</html>
