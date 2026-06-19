<!DOCTYPE html>
<html lang="en" style="background:var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campaigns — FestiFondo</title>
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
        .card{background:var(--color-bg-elevated);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;}
        .data-table{width:100%;border-collapse:collapse;}
        .data-table th{text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;letter-spacing:.07em;color:var(--color-text-muted);text-transform:uppercase;border-bottom:1px solid var(--color-border);}
        .data-table td{padding:.8rem 1rem;font-size:.875rem;border-bottom:1px solid var(--color-border);color:var(--color-text-secondary);vertical-align:middle;}
        .data-table tr:last-child td{border-bottom:none;} .data-table tr:hover td{background:rgba(255,255,255,.02);}
        .btn{display:inline-flex;align-items:center;gap:.3rem;padding:.4rem .85rem;border-radius:var(--radius-md);font-size:.84rem;font-weight:500;cursor:pointer;border:none;transition:all .15s;text-decoration:none;}
        .btn-primary{background:var(--color-primary);color:#fff;} .btn-primary:hover{background:var(--color-primary-hover);}
        .btn-ghost{background:transparent;color:var(--color-text-secondary);border:1px solid var(--color-border-subtle);} .btn-ghost:hover{background:var(--color-bg-elevated);}
        .btn-danger{background:transparent;color:var(--color-error);border:1px solid var(--color-error);} .btn-danger:hover{background:var(--color-error-bg);}
        .btn:disabled{opacity:.5;cursor:not-allowed;}
        .badge{display:inline-flex;align-items:center;padding:.2rem .7rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:600;text-transform:capitalize;}
        .badge-draft{background:var(--color-info-bg);color:var(--color-info);}
        .badge-active{background:var(--color-success-bg);color:var(--color-success);}
        .badge-completed{background:rgba(6,182,212,.15);color:var(--color-accent);}
        .badge-cancelled{background:var(--color-bg-overlay);color:var(--color-text-muted);}
        .progress-bar-bg{background:var(--color-bg-overlay);border-radius:var(--radius-full);height:6px;overflow:hidden;min-width:80px;}
        .progress-bar-fill{height:100%;border-radius:var(--radius-full);background:linear-gradient(90deg,var(--color-primary),var(--color-accent));}
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:50;align-items:center;justify-content:center;padding:1rem;}
        .modal-overlay.open{display:flex;}
        .modal{background:var(--color-bg-elevated);border:1px solid var(--color-border-subtle);border-radius:var(--radius-xl);padding:1.75rem;width:100%;max-width:520px;box-shadow:var(--shadow-md);}
        .modal h3{font-size:1.05rem;font-weight:600;margin-bottom:1.2rem;}
        .form-group{margin-bottom:.85rem;}
        .form-group label{display:block;font-size:.82rem;font-weight:500;color:var(--color-text-secondary);margin-bottom:.3rem;}
        .form-control{width:100%;padding:.5rem .7rem;background:var(--color-bg-overlay);border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);color:var(--color-text-primary);font-size:.875rem;outline:none;}
        .form-control:focus{border-color:var(--color-primary);}
        .field-error{font-size:.78rem;color:var(--color-error);margin-top:.2rem;display:none;}
        .field-error.show{display:block;}
        .alert{padding:.7rem 1rem;border-radius:var(--radius-md);font-size:.875rem;margin-bottom:.9rem;}
        .alert-error{background:var(--color-error-bg);color:var(--color-error);}
        #toast{position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:.75rem 1.25rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;box-shadow:var(--shadow-md);display:none;}
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
        ['label'=>'Campaigns'],
    ]" />

    {{-- Page title --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;">Campaigns</h1>
            <p style="color:var(--color-text-muted);font-size:.875rem;margin-top:.2rem;">Manage fund raising campaigns and member enrollment</p>
        </div>
        <div style="display:flex;gap:.75rem;">
            <a href="{{ route('fund-raising.index') }}" class="btn btn-ghost">← Member Balances</a>
            <a href="{{ route('campaigns.create') }}" class="btn btn-ghost">Full Form</a>
            <button class="btn btn-primary" onclick="openModal('modal-create')">+ New Campaign</button>
        </div>
    </div>

    @isset($loadError)
    <div class="alert alert-error" style="margin-bottom:1rem;">{{ $loadError }}</div>
    @endisset

    {{-- Stats --}}
    @php
        $campaigns         = $campaigns ?? [];
        $activeCampaigns   = count(array_filter($campaigns, fn($c) => $c['campaign_status'] === 'active'));
        $totalRaised       = array_sum(array_column($campaigns, 'collected_amount'));
        $totalTarget       = array_sum(array_column($campaigns, 'target_amount'));
    @endphp
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
        <div class="card">
            <div style="font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.35rem;">Total Campaigns</div>
            <div style="font-size:1.5rem;font-weight:700;">{{ count($campaigns) }}</div>
        </div>
        <div class="card">
            <div style="font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.35rem;">Active</div>
            <div style="font-size:1.5rem;font-weight:700;color:var(--color-success);">{{ $activeCampaigns }}</div>
        </div>
        <div class="card">
            <div style="font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.35rem;">Total Raised</div>
            <div style="font-size:1.5rem;font-weight:700;color:var(--color-success);">${{ number_format($totalRaised, 2) }}</div>
        </div>
        <div class="card">
            <div style="font-size:.72rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.35rem;">Total Target</div>
            <div style="font-size:1.5rem;font-weight:700;color:var(--color-accent);">${{ number_format($totalTarget, 2) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Collected / Target</th>
                        <th>Start</th>
                        <th>End</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($campaigns as $c)
                <tr>
                    <td style="font-weight:600;color:var(--color-text-primary);">{{ $c['name'] }}</td>
                    <td><span class="badge badge-{{ $c['campaign_status'] }}">{{ $c['campaign_status'] }}</span></td>
                    <td style="min-width:130px;">
                        <div class="progress-bar-bg" style="margin-bottom:.3rem;">
                            <div class="progress-bar-fill" style="width:{{ min($c['progress_percent'],100) }}%;"></div>
                        </div>
                        <span style="font-size:.75rem;color:var(--color-text-muted);">{{ $c['progress_percent'] }}%</span>
                    </td>
                    <td>
                        <span style="color:var(--color-success);font-weight:600;">${{ number_format($c['collected_amount'],2) }}</span>
                        <span style="color:var(--color-text-muted);"> / ${{ number_format($c['target_amount'],2) }}</span>
                    </td>
                    <td style="font-size:.82rem;color:var(--color-text-muted);">{{ $c['start_date'] }}</td>
                    <td style="font-size:.82rem;color:var(--color-text-muted);">{{ $c['end_date'] ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:.35rem;">
                            <a href="{{ route('campaigns.show', $c['uuid']) }}" class="btn btn-ghost">View</a>
                            @if(!in_array($c['campaign_status'],['completed','cancelled']))
                                <a href="{{ route('campaigns.edit', $c['uuid']) }}" class="btn btn-ghost">Edit</a>
                                @if($c['campaign_status'] !== 'draft')
                                <button class="btn btn-danger"
                                    onclick="confirmCancel('{{ $c['uuid'] }}','{{ addslashes($c['name']) }}')">
                                    Cancel
                                </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:2.5rem 1rem;text-align:center;color:var(--color-text-muted);">
                        No campaigns yet.
                        <span style="color:var(--color-primary-light);cursor:pointer;margin-left:.4rem;"
                              onclick="openModal('modal-create')">Create the first one →</span>
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

{{-- Modal: Create --}}
<div class="modal-overlay" id="modal-create">
    <div class="modal">
        <h3>New Campaign</h3>
        <div id="create-err" class="alert alert-error" style="display:none;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div style="grid-column:1/-1;" class="form-group">
                <label>Name <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="c_name" placeholder="e.g. Annual Trip 2026">
                <span class="field-error" id="err-name"></span>
            </div>
            <div style="grid-column:1/-1;" class="form-group">
                <label>Description</label>
                <textarea class="form-control" id="c_desc" rows="2" placeholder="What is this campaign for?"></textarea>
            </div>
            <div class="form-group">
                <label>Target Amount (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="c_target" type="number" step="0.01" placeholder="0.00">
                <span class="field-error" id="err-target_amount"></span>
            </div>
            <div></div>
            <div class="form-group">
                <label>Cuota mensual (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="c_fee" type="number" step="0.01" min="0.01" placeholder="1.00" value="1.00">
                <span class="field-error" id="err-monthly_fee_amount"></span>
            </div>
            <div class="form-group">
                <label>Mora diaria (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="c_penalty" type="number" step="0.0001" min="0" placeholder="0.05" value="0.05">
                <span class="field-error" id="err-daily_penalty_rate"></span>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label>Día de cobro mensual <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="c_due_day" type="number" min="1" max="28" step="1" value="15"
                       style="max-width:120px;">
                <span class="field-error" id="err-due_day"></span>
                <small style="color:var(--color-text-muted);font-size:.75rem;display:block;margin-top:.2rem;">
                    Día del mes en que se genera el cobro (1–28). Máximo 28 para que aplique en todos los meses.
                </small>
            </div>
            <div class="form-group">
                <label>Start Date <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="c_start" type="date">
                <span class="field-error" id="err-start_date"></span>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input class="form-control" id="c_end" type="date">
            </div>
        </div>
        <div style="background:var(--color-info-bg);border:1px solid var(--color-info);border-radius:var(--radius-md);padding:.65rem 1rem;font-size:.82rem;color:var(--color-text-secondary);margin-bottom:.75rem;">
            Status will be <strong style="color:var(--color-info);">Draft</strong>. Activate when ready.
        </div>
        <div style="display:flex;gap:.6rem;justify-content:flex-end;">
            <button class="btn btn-ghost" onclick="closeModal('modal-create')">Cancel</button>
            <button class="btn btn-primary" id="btn-create" onclick="submitCreate()">Save Campaign</button>
        </div>
    </div>
</div>

{{-- Modal: Cancel confirm --}}
<div class="modal-overlay" id="modal-cancel">
    <div class="modal" style="max-width:380px;">
        <h3>Cancel Campaign</h3>
        <p style="color:var(--color-text-secondary);margin-bottom:1.5rem;">
            Cancel <strong id="cancel-name"></strong>? This action is irreversible.
        </p>
        <div style="display:flex;gap:.6rem;justify-content:flex-end;">
            <button class="btn btn-ghost" onclick="closeModal('modal-cancel')">Back</button>
            <button class="btn btn-danger" id="btn-cancel" onclick="submitCancel()">Yes, Cancel</button>
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

async function submitCreate() {
    document.querySelectorAll('.field-error').forEach(el => { el.textContent=''; el.classList.remove('show'); });
    document.getElementById('create-err').style.display = 'none';
    const btn = document.getElementById('btn-create');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
        const res  = await fetch('{{ route("campaigns.store") }}', {
            method:  'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:    JSON.stringify({
                name:                document.getElementById('c_name').value.trim() || null,
                description:         document.getElementById('c_desc').value.trim() || null,
                target_amount:       parseFloat(document.getElementById('c_target').value) || null,
                monthly_fee_amount:  parseFloat(document.getElementById('c_fee').value) || null,
                daily_penalty_rate:  parseFloat(document.getElementById('c_penalty').value) ?? null,
                due_day:             parseInt(document.getElementById('c_due_day').value) || null,
                start_date:          document.getElementById('c_start').value || null,
                end_date:            document.getElementById('c_end').value || null,
            }),
        });
        const json = await res.json();
        if (res.ok && json.status) {
            closeModal('modal-create');
            showToast('Campaign created.');
            setTimeout(() => location.reload(), 800);
        } else if (res.status === 422 && json.errors) {
            Object.entries(json.errors).forEach(([f,m]) => {
                const el = document.getElementById('err-'+f); if(el){el.textContent=m[0];el.classList.add('show');}
            });
        } else {
            const e = document.getElementById('create-err'); e.textContent = json.message || 'Error.'; e.style.display='block';
        }
    } catch { const e=document.getElementById('create-err'); e.textContent='Connection error.'; e.style.display='block'; }
    finally { btn.disabled=false; btn.textContent='Save Campaign'; }
}

let _cancelUuid = null;
function confirmCancel(uuid, name) { _cancelUuid=uuid; document.getElementById('cancel-name').textContent=name; openModal('modal-cancel'); }
async function submitCancel() {
    if (!_cancelUuid) return;
    const btn = document.getElementById('btn-cancel');
    btn.disabled=true; btn.textContent='Cancelling…';
    try {
        const res  = await fetch('{{ url("v1/financial/campaigns") }}/'+_cancelUuid, {
            method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
        });
        const json = await res.json();
        closeModal('modal-cancel');
        if (json.status) { showToast('Campaign cancelled.'); setTimeout(()=>location.reload(),800); }
        else              { showToast(json.message||'Error.','error'); }
    } catch { showToast('Connection error.','error'); }
    finally { btn.disabled=false; btn.textContent='Yes, Cancel'; _cancelUuid=null; }
}
</script>
</body>
</html>
