<!DOCTYPE html>
<html lang="en" style="background:var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fund Raising — FestiFondo</title>
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
        .field-error{font-size:.8rem;color:var(--color-error);margin-top:.25rem;display:none;}
        .field-error.show{display:block;}
        .progress-bar-bg{background:var(--color-bg-overlay);border-radius:var(--radius-full);height:6px;overflow:hidden;}
        .progress-bar-fill{height:100%;border-radius:var(--radius-full);
            background:linear-gradient(90deg,var(--color-primary),var(--color-accent));}
        /* Modal */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);
            z-index:1000;align-items:center;justify-content:center;}
        .modal-overlay.open{display:flex;}
        .modal-box{background:var(--color-bg-elevated);border:1px solid var(--color-border-subtle);
            border-radius:var(--radius-xl);padding:1.5rem;width:100%;max-width:520px;
            box-shadow:var(--shadow-lg);display:flex;flex-direction:column;gap:1rem;}
        .modal-box.sm{max-width:380px;}
        .modal-header{display:flex;align-items:center;justify-content:space-between;}
        .modal-title{font-size:1rem;font-weight:700;}
        .modal-close{background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:1.25rem;line-height:1;}
        .modal-footer{display:flex;justify-content:flex-end;gap:.75rem;padding-top:.75rem;
            border-top:1px solid var(--color-border);}
        /* Card */
        .card{background:var(--color-bg-elevated);border:1px solid var(--color-border);
            border-radius:var(--radius-lg);padding:1.25rem 1.5rem;}
        .card-title{font-size:.875rem;font-weight:700;color:var(--color-text-secondary);
            text-transform:uppercase;letter-spacing:.06em;margin-bottom:1rem;}
        /* Table */
        .data-table{width:100%;border-collapse:collapse;}
        .data-table th{padding:.625rem 1rem;font-size:.75rem;font-weight:600;
            color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;
            border-bottom:1px solid var(--color-border-subtle);text-align:left;}
        .data-table td{padding:.875rem 1rem;border-bottom:1px solid var(--color-border);
            font-size:.875rem;vertical-align:middle;}
        .data-table tr:last-child td{border-bottom:none;}
        .data-table tbody tr{transition:background .1s;}
        .data-table tbody tr:hover{background:var(--color-bg-overlay);}
        /* Button */
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;
            font-size:.8rem;font-weight:600;border-radius:var(--radius-md);cursor:pointer;
            border:none;transition:opacity .15s,background .15s;text-decoration:none;line-height:1.4;}
        .btn-primary{background:var(--color-primary);color:#fff;}
        .btn-primary:hover{background:var(--color-primary-hover);}
        .btn-ghost{background:transparent;color:var(--color-text-secondary);
            border:1px solid var(--color-border-subtle);}
        .btn-ghost:hover{background:var(--color-bg-overlay);color:var(--color-text-primary);}
        .btn-danger{background:var(--color-error-bg);color:var(--color-error);
            border:1px solid var(--color-error);}
        .btn-danger:hover{opacity:.85;}
        .btn:disabled{opacity:.5;cursor:not-allowed;}
        /* Form */
        .form-input,.form-select,.form-textarea{
            width:100%;background:var(--color-bg-elevated);border:1px solid var(--color-border-subtle);
            border-radius:var(--radius-md);padding:.55rem .875rem;color:var(--color-text-primary);
            font-size:.875rem;outline:none;}
        .form-input:focus,.form-textarea:focus{border-color:var(--color-primary);}
        .form-textarea{resize:vertical;}
        .form-label{display:block;font-size:.875rem;font-weight:500;
            color:var(--color-text-secondary);margin-bottom:.35rem;}
        /* Toast */
        .toast{position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:.75rem 1.25rem;
            border-radius:var(--radius-md);font-size:.875rem;font-weight:500;
            box-shadow:var(--shadow-lg);display:none;}
    </style>
</head>
<body style="background:var(--color-bg-base);color:var(--color-text-primary);min-height:100vh;display:flex;flex-direction:column;">

<x-layout.app-header />

<div style="display:flex;flex:1;">
    <x-layout.app-sidebar />
    <x-layout.main>

        <x-ui.breadcrumb :items="[
            ['label'=>'Home',      'url'=>'/'],
            ['label'=>'Financial', 'url'=>'#'],
            ['label'=>'Fund Raising'],
        ]" />

        {{-- Page title --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;">Fund Raising</h1>
                <p style="color:var(--color-text-muted);font-size:.875rem;margin-top:.2rem;">
                    Manage campaigns, member charges and late fees
                </p>
            </div>
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('campaigns.index') }}" class="btn btn-ghost">Campaigns →</a>
            </div>
        </div>

        @isset($loadError)
        <div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:var(--radius-md);
            background:var(--color-error-bg);border:1px solid var(--color-error);
            color:var(--color-error);font-size:.875rem;">
            {{ $loadError }}
        </div>
        @endisset

        {{-- ── STATS ──────────────────────────────────────────────────────────── --}}
        @php
            $membersWithBalance    = $membersWithBalance ?? [];
            $totalActiveMembers    = count($membersWithBalance);
            $membersInDebt         = count(array_filter($membersWithBalance, fn($m) => ($m['total_balance'] ?? 0) > 0));
            $totalFeesPending      = array_sum(array_column($membersWithBalance, 'fees_balance'));
            $totalPenaltiesPending = array_sum(array_column($membersWithBalance, 'penalties_balance'));
        @endphp
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
            <div class="card">
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Active Members</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;">{{ $totalActiveMembers }}</p>
                <p style="font-size:.75rem;color:var(--color-error);margin-top:.2rem;">
                    {{ $membersInDebt }} with outstanding balance
                </p>
            </div>
            <div class="card">
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Fees Pending</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;color:var(--color-warning);">
                    ${{ number_format($totalFeesPending, 2) }}
                </p>
            </div>
            <div class="card">
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Penalties Pending</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;color:var(--color-error);">
                    ${{ number_format($totalPenaltiesPending, 2) }}
                </p>
                <p style="font-size:.75rem;color:var(--color-text-muted);margin-top:.2rem;">
                    <a href="{{ route('campaigns.index') }}" style="color:var(--color-primary-light);">View Campaigns →</a>
                </p>
            </div>
        </div>

        {{-- ── MEMBERS TABLE ─────────────────────────────────────────────────── --}}
        <div class="card" style="margin-bottom:1.5rem;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:1rem;flex-wrap:wrap;">
                <p class="card-title" style="margin-bottom:0;">Member Balances</p>
                <div style="display:flex;gap:.75rem;">
                    <input class="form-input" id="search_members" placeholder="Search CI or name…"
                           style="max-width:220px;" oninput="filterMembers()" />
                    <select class="form-select" id="filter_debt" style="max-width:150px;" onchange="filterMembers()">
                        <option value="all">All</option>
                        <option value="debt">With balance</option>
                        <option value="ok">Up to date</option>
                    </select>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>CI / ID</th>
                            <th>Full Name</th>
                            <th>Fees Pending</th>
                            <th>Penalties</th>
                            <th>Total Balance</th>
                            <th>Total Paid</th>
                            <th>Paid in Penalties</th>
                            <th>Last Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($membersWithBalance as $member)
                        @php $hasDebt = ($member['total_balance'] ?? 0) > 0; @endphp
                        <tr class="mbr-row"
                            data-name="{{ strtolower(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) }}"
                            data-id="{{ strtolower($member['identification'] ?? '') }}"
                            data-debt="{{ $hasDebt ? '1' : '0' }}">
                            <td style="font-weight:600;color:var(--color-accent);">
                                {{ $member['identification'] ?? '—' }}
                            </td>
                            <td style="font-weight:500;color:var(--color-text-primary);">
                                {{ ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '') }}
                            </td>
                            <td>
                                @php $fb = (float)($member['fees_balance'] ?? 0); @endphp
                                <span style="color:{{ $fb > 0 ? 'var(--color-warning)' : 'var(--color-success)' }};font-weight:600;">
                                    ${{ number_format($fb, 2) }}
                                </span>
                            </td>
                            <td>
                                @php $pb = (float)($member['penalties_balance'] ?? 0); @endphp
                                <span style="color:{{ $pb > 0 ? 'var(--color-error)' : 'var(--color-text-muted)' }};">
                                    ${{ number_format($pb, 2) }}
                                </span>
                            </td>
                            <td>
                                @php $tb = (float)($member['total_balance'] ?? 0); @endphp
                                <span style="font-weight:700;
                                    color:{{ $tb > 0 ? 'var(--color-error)' : 'var(--color-success)' }};">
                                    ${{ number_format($tb, 2) }}
                                </span>
                            </td>
                            <td>
                                @php $tp = (float)($member['total_paid'] ?? 0); @endphp
                                <span style="font-weight:600;color:{{ $tp > 0 ? 'var(--color-success)' : 'var(--color-text-muted)' }};">
                                    ${{ number_format($tp, 2) }}
                                </span>
                            </td>
                            <td>
                                @php $pp = (float)($member['penalties_paid'] ?? 0); @endphp
                                <span style="color:{{ $pp > 0 ? 'var(--color-error)' : 'var(--color-text-muted)' }};">
                                    ${{ number_format($pp, 2) }}
                                </span>
                            </td>
                            <td style="font-size:.85rem;color:var(--color-text-muted);">
                                {{ $member['last_payment_date'] ?? '—' }}
                            </td>
                            <td>
                                <div style="display:flex;gap:.4rem;">
                                    <a href="{{ route('members.show', $member['uuid']) }}" class="btn btn-ghost">View</a>
                                    @if($hasDebt)
                                    <button class="btn btn-primary"
                                        onclick="openPayModal(
                                            {{ $member['member_oid'] }},
                                            '{{ addslashes(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) }}',
                                            {{ $member['total_balance'] ?? 0 }},
                                            {{ $member['fees_balance'] ?? 0 }},
                                            {{ $member['penalties_balance'] ?? 0 }}
                                        )">
                                        Pay
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="padding:2.5rem 1rem;text-align:center;color:var(--color-text-muted);">
                                No active members found.
                                <a href="{{ route('members.create') }}"
                                   style="color:var(--color-primary-light);margin-left:.4rem;">
                                    Add a member →
                                </a>
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

{{-- ── MODAL: Process Result ─────────────────────────────────────────────── --}}
<div id="modal-process-result" class="modal-overlay"
     onclick="if(event.target===this)closeModal('modal-process-result')">
    <div class="modal-box sm">
        <div class="modal-header">
            <span class="modal-title">Process Result</span>
            <button class="modal-close" onclick="closeModal('modal-process-result')">&times;</button>
        </div>
        <div id="process-result-body"
             style="color:var(--color-text-secondary);font-size:.9rem;line-height:1.8;"></div>
        <div class="modal-footer">
            <button class="btn btn-primary"
                    onclick="closeModal('modal-process-result');location.reload()">
                OK — Refresh
            </button>
        </div>
    </div>
</div>

{{-- ── MODAL: Apply Payment ──────────────────────────────────────────────── --}}
<div id="modal-pay" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-pay')">
    <div class="modal-box">
        <div class="modal-header">
            <span class="modal-title">Register Payment</span>
            <button class="modal-close" onclick="closeModal('modal-pay')">&times;</button>
        </div>
        <div>
            {{-- Member info --}}
            <div style="background:var(--color-bg-overlay);border-radius:var(--radius-md);
                padding:.75rem 1rem;margin-bottom:1rem;">
                <p style="font-size:.8rem;color:var(--color-text-muted);margin-bottom:.25rem;">Member</p>
                <p id="pay-member-name" style="font-weight:700;font-size:1rem;"></p>
            </div>
            {{-- Balance breakdown --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div style="text-align:center;padding:.6rem;background:var(--color-bg-overlay);
                    border-radius:var(--radius-md);">
                    <p style="font-size:.7rem;color:var(--color-text-muted);margin-bottom:.2rem;">Fees</p>
                    <p id="pay-fees" style="font-weight:700;color:var(--color-warning);"></p>
                </div>
                <div style="text-align:center;padding:.6rem;background:var(--color-bg-overlay);
                    border-radius:var(--radius-md);">
                    <p style="font-size:.7rem;color:var(--color-text-muted);margin-bottom:.2rem;">Penalties</p>
                    <p id="pay-penalties" style="font-weight:700;color:var(--color-error);"></p>
                </div>
                <div style="text-align:center;padding:.6rem;
                    background:var(--color-error-bg);border:1px solid var(--color-error);
                    border-radius:var(--radius-md);">
                    <p style="font-size:.7rem;color:var(--color-text-muted);margin-bottom:.2rem;">Total Due</p>
                    <p id="pay-total" style="font-weight:700;color:var(--color-error);"></p>
                </div>
            </div>
            {{-- Payment form --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label class="form-label">
                        Amount (USD) <span style="color:var(--color-error);">*</span>
                    </label>
                    <input id="pay_amount" class="form-input" type="number" step="0.01"
                           placeholder="0.00" oninput="updatePayHint()" />
                    <span id="pay-hint" style="font-size:.78rem;color:var(--color-text-muted);margin-top:.25rem;display:block;"></span>
                    <span class="field-error" id="err-pay-amount"></span>
                </div>
                <div>
                    <label class="form-label">Payment Date <span style="color:var(--color-error);">*</span></label>
                    <input id="pay_date" class="form-input" type="date"
                           value="{{ \Carbon\Carbon::today()->toDateString() }}" />
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Notes</label>
                    <textarea id="pay_notes" class="form-textarea" rows="2"
                              placeholder="Optional payment notes…"></textarea>
                </div>
            </div>
            <div id="pay-err" style="display:none;margin-top:.75rem;background:var(--color-error-bg);
                border:1px solid var(--color-error);border-radius:var(--radius-md);
                padding:.65rem 1rem;color:var(--color-error);font-size:.85rem;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-pay')">Cancel</button>
            <button class="btn btn-primary" id="btn-pay" onclick="submitPayment()">
                Apply Payment
            </button>
        </div>
    </div>
</div>

{{-- ── MODAL: Payment Result ─────────────────────────────────────────────── --}}
<div id="modal-pay-result" class="modal-overlay"
     onclick="if(event.target===this)closeModal('modal-pay-result')">
    <div class="modal-box sm">
        <div class="modal-header">
            <span class="modal-title">Payment Applied ✓</span>
            <button class="modal-close" onclick="closeModal('modal-pay-result')">&times;</button>
        </div>
        <div id="pay-result-body"
             style="color:var(--color-text-secondary);font-size:.9rem;line-height:1.8;"></div>
        <div class="modal-footer">
            <button class="btn btn-primary"
                    onclick="closeModal('modal-pay-result');location.reload()">
                OK — Refresh
            </button>
        </div>
    </div>
</div>

{{-- ── MODAL: Confirm Re-run ─────────────────────────────────────────────── --}}
<div id="modal-rerun" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-rerun')">
    <div class="modal-box sm">
        <div class="modal-header">
            <span class="modal-title">Already Ran Today</span>
            <button class="modal-close" onclick="closeModal('modal-rerun')">&times;</button>
        </div>
        <p style="color:var(--color-text-secondary);font-size:.9rem;">
            This process already ran today. Running it again is safe — no duplicate fees or penalties
            will be created (idempotent). Continue?
        </p>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-rerun')">Cancel</button>
            <button class="btn btn-primary"
                    onclick="closeModal('modal-rerun');runProcess()">
                Run Anyway
            </button>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

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

// ── Filter Members ────────────────────────────────────────────────────────────
function filterMembers() {
    const s = document.getElementById('search_members').value.toLowerCase().trim();
    const d = document.getElementById('filter_debt').value;
    document.querySelectorAll('.mbr-row').forEach(row => {
        const ms = !s || row.dataset.name.includes(s) || row.dataset.id.includes(s);
        const md = d === 'all'
            || (d === 'debt' && row.dataset.debt === '1')
            || (d === 'ok'   && row.dataset.debt === '0');
        row.style.display = ms && md ? '' : 'none';
    });
}

// ── Apply Payment ─────────────────────────────────────────────────────────────
let _payMemberOid   = null;
let _payTotalDue    = 0;

function openPayModal(memberOid, name, total, fees, penalties) {
    _payMemberOid = memberOid;
    _payTotalDue  = total;
    document.getElementById('pay-member-name').textContent = name;
    document.getElementById('pay-fees').textContent        = '$' + fees.toFixed(2);
    document.getElementById('pay-penalties').textContent   = '$' + penalties.toFixed(2);
    document.getElementById('pay-total').textContent       = '$' + total.toFixed(2);
    document.getElementById('pay_amount').value            = total.toFixed(2);
    document.getElementById('pay-hint').textContent        = '';
    document.getElementById('pay-err').style.display       = 'none';
    document.getElementById('err-pay-amount').textContent  = '';
    document.getElementById('err-pay-amount').classList.remove('show');
    updatePayHint();
    openModal('modal-pay');
}

function updatePayHint() {
    const amount = parseFloat(document.getElementById('pay_amount').value) || 0;
    const hint   = document.getElementById('pay-hint');
    if (amount <= 0)                  { hint.textContent = ''; return; }
    if (amount >= _payTotalDue)       { hint.style.color = 'var(--color-success)'; hint.textContent = 'Covers full balance.'; }
    else                              { hint.style.color = 'var(--color-warning)'; hint.textContent = `Partial payment — $${(_payTotalDue - amount).toFixed(2)} will remain.`; }
}

async function submitPayment() {
    document.getElementById('pay-err').style.display = 'none';
    document.getElementById('err-pay-amount').textContent = '';
    document.getElementById('err-pay-amount').classList.remove('show');

    const btn    = document.getElementById('btn-pay');
    const amount = parseFloat(document.getElementById('pay_amount').value);
    const date   = document.getElementById('pay_date').value;

    if (!amount || amount <= 0) {
        const el = document.getElementById('err-pay-amount');
        el.textContent = 'Amount must be greater than zero.'; el.classList.add('show'); return;
    }

    btn.disabled = true; btn.textContent = 'Applying…';

    try {
        const res  = await fetch('{{ route("transactions.apply-payment") }}', {
            method:  'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:    JSON.stringify({
                member_oid:       _payMemberOid,
                amount:           amount,
                transaction_date: date,
                notes:            document.getElementById('pay_notes').value.trim() || null,
            }),
        });
        const json = await res.json();

        if (res.ok && json.status) {
            closeModal('modal-pay');
            const d   = json.data;
            const msg = `<strong style="color:var(--color-success);">Payment registered ✓</strong><br>
                Amount paid: <strong>$${d.amount_paid.toFixed(2)}</strong><br>
                Applied to penalties: <span style="color:var(--color-error);">$${d.applied_to_penalties.toFixed(2)}</span><br>
                Applied to fees: <span style="color:var(--color-warning);">$${d.applied_to_fees.toFixed(2)}</span><br>
                ${d.overpayment > 0
                    ? `<span style="color:var(--color-info);">Overpayment: $${d.overpayment.toFixed(2)} (credit)</span>`
                    : ''}`;
            document.getElementById('pay-result-body').innerHTML = msg;
            openModal('modal-pay-result');
        } else if (res.status === 422 && json.errors) {
            Object.entries(json.errors).forEach(([f, msgs]) => {
                if (f === 'amount') {
                    const el = document.getElementById('err-pay-amount');
                    el.textContent = msgs[0]; el.classList.add('show');
                }
            });
        } else {
            const e = document.getElementById('pay-err');
            e.textContent = json.message || 'Unexpected error.'; e.style.display = 'block';
        }
    } catch {
        const e = document.getElementById('pay-err');
        e.textContent = 'Connection error.'; e.style.display = 'block';
    } finally {
        btn.disabled = false; btn.textContent = 'Apply Payment';
    }
}

// ── Process: Actualizar Cobros y Moras ────────────────────────────────────────
async function runProcess() {
    const btn = document.getElementById('btn-process');
    btn.disabled = true; btn.textContent = 'Processing…';

    try {
        const res  = await fetch('{{ route("fund-raising.process-charges") }}', {
            method:  'POST',
            headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
        });
        const json = await res.json();

        if (json.status) {
            const d = json.data;
            const msg = `<strong style="color:var(--color-success);">Process completed ✓</strong><br>
                Period: <code style="font-size:.9em;">${d.period}</code><br>
                <span style="color:var(--color-warning);">${d.fees_generated} fee(s) generated</span><br>
                <span style="color:var(--color-error);">${d.penalties_generated} penalty(ies) generated</span><br>
                ${d.members_processed} members processed`;
            document.getElementById('process-result-body').innerHTML = msg;
            openModal('modal-process-result');
        } else {
            showToast(json.message || 'Process failed.', 'error');
        }
    } catch {
        showToast('Connection error. Please try again.', 'error');
    } finally {
        btn.disabled = false; btn.textContent = '⟳ Actualizar Cobros y Moras';
    }
}
</script>

</body>
</html>
