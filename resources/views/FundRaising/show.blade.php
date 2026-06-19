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
            --shadow-md:0 4px 12px rgba(0,0,0,.5);
            --radius-md:8px;--radius-lg:12px;--radius-xl:16px;--radius-full:9999px;
        }
        body{background:var(--color-bg-base);color:var(--color-text-primary);min-height:100vh;padding:1.5rem 2rem;}
        .page-header{display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap;}
        .back-link{color:var(--color-text-muted);text-decoration:none;font-size:.9rem;padding:.35rem .7rem;border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);}
        .back-link:hover{color:var(--color-accent);border-color:var(--color-accent);}
        h1{font-size:1.5rem;font-weight:700;flex:1;}
        .badge{display:inline-flex;align-items:center;padding:.2rem .7rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:600;text-transform:capitalize;}
        .badge-draft{background:#1e293b;color:#94a3b8;} .badge-active{background:var(--color-success-bg);color:var(--color-success);}
        .badge-completed{background:var(--color-info-bg);color:var(--color-info);} .badge-cancelled{background:var(--color-error-bg);color:var(--color-error);}
        .cards-row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem;}
        @media(max-width:900px){.cards-row{grid-template-columns:1fr 1fr;}}
        @media(max-width:500px){.cards-row{grid-template-columns:1fr;}}
        .stat-card{background:var(--color-bg-surface);border:1px solid var(--color-border);border-radius:var(--radius-xl);padding:1.1rem 1.25rem;}
        .stat-card .lbl{font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.35rem;}
        .stat-card .val{font-size:1.3rem;font-weight:700;}
        .progress-bar-bg{background:var(--color-bg-overlay);border-radius:var(--radius-full);height:6px;overflow:hidden;}
        .progress-bar-fill{height:100%;border-radius:var(--radius-full);background:linear-gradient(90deg,var(--color-primary),var(--color-accent));}
        .card{background:var(--color-bg-surface);border:1px solid var(--color-border);border-radius:var(--radius-xl);padding:1.5rem;margin-bottom:1.5rem;}
        .card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;flex-wrap:wrap;gap:.75rem;}
        .card-title{font-size:.9rem;font-weight:600;letter-spacing:.08em;color:var(--color-text-muted);text-transform:uppercase;}
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
        .modal{background:var(--color-bg-surface);border:1px solid var(--color-border-subtle);border-radius:var(--radius-xl);padding:1.75rem;width:100%;max-width:500px;box-shadow:var(--shadow-md);}
        .modal h3{font-size:1.05rem;font-weight:600;margin-bottom:1.2rem;}
        .form-group{margin-bottom:.9rem;}
        .form-group label{display:block;font-size:.8rem;font-weight:500;color:var(--color-text-secondary);margin-bottom:.3rem;}
        .form-control{width:100%;padding:.5rem .7rem;background:var(--color-bg-elevated);border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);color:var(--color-text-primary);font-size:.875rem;outline:none;}
        .form-control:focus{border-color:var(--color-primary);}
        .field-error{font-size:.78rem;color:var(--color-error);margin-top:.2rem;display:none;}
        .field-error.show{display:block;}
        .alert{padding:.7rem 1rem;border-radius:var(--radius-md);font-size:.875rem;margin-bottom:.9rem;}
        .alert-error{background:var(--color-error-bg);color:var(--color-error);}
        .alert-success{background:var(--color-success-bg);color:var(--color-success);}
        .chk-list{max-height:220px;overflow-y:auto;border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);padding:.4rem;}
        .chk-item{display:flex;align-items:center;gap:.6rem;padding:.35rem .5rem;border-radius:var(--radius-md);cursor:pointer;}
        .chk-item:hover{background:var(--color-bg-elevated);}
        .chk-item input[type=checkbox]{accent-color:var(--color-primary);width:14px;height:14px;}
        .chk-item label{cursor:pointer;font-size:.86rem;color:var(--color-text-secondary);}
    </style>
</head>
<body>

<div class="page-header">
    <a href="{{ route('fund-raising.index') }}" class="back-link">← Campaigns</a>
    <h1>{{ $campaign['name'] }}</h1>
    <span class="badge badge-{{ $campaign['fund_raising_status'] }}">{{ $campaign['fund_raising_status'] }}</span>
    <a href="{{ route('fund-raising.edit', $campaign['uuid']) }}" class="btn btn-ghost">Edit</a>
</div>

@php
    $pct = $campaign['target_amount'] > 0
        ? min(100, round(($campaign['collected_amount'] / $campaign['target_amount']) * 100, 1))
        : 0;
    $remaining = max(0, $campaign['target_amount'] - $campaign['collected_amount']);
@endphp

{{-- Stats row --}}
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

{{-- Members table --}}
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

{{-- ── Modals ─────────────────────────────────────────────────────────────── --}}

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
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;background:var(--color-bg-elevated);border-radius:var(--radius-md);padding:.8rem;margin-bottom:.9rem;font-size:.84rem;">
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

<script>
const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
const ENROLL_URL   = '{{ route("fund-raising.members.enroll",  $campaign["uuid"]) }}';
const AVAIL_URL    = '{{ route("fund-raising.members.available",$campaign["uuid"]) }}';
const REMOVE_BASE  = '{{ url("v1/financial/fund-raising/".$campaign["uuid"]."/members") }}';
const PAY_URL      = '{{ route("transactions.apply-payment") }}';

let _avail = [], _rmUuid = null;
let _payMbr = null, _payTotal = 0, _payFees = 0, _payPen = 0, _payCmp = null;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

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
        } else { alert(j.message||'Error removing.'); }
    } catch { alert('Connection error.'); }
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
</script>
</body>
</html>
