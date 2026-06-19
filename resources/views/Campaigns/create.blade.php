<!DOCTYPE html>
<html lang="en" style="background:var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>New Campaign — FestiFondo</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Instrument Sans',ui-sans-serif,system-ui,sans-serif;}
        :root{
            --color-bg-base:#0A0F1E;--color-bg-surface:#0F172A;--color-bg-elevated:#1E293B;
            --color-bg-overlay:#263347;--color-primary:#2563EB;--color-primary-hover:#1D4ED8;
            --color-primary-light:#3B82F6;--color-primary-subtle:#1E3A5F;--color-accent:#06B6D4;
            --color-text-primary:#F8FAFC;--color-text-secondary:#94A3B8;--color-text-muted:#64748B;
            --color-border:#1E293B;--color-border-subtle:#334155;
            --color-success:#10B981;--color-success-bg:#064E3B;
            --color-error:#EF4444;--color-error-bg:#450A0A;
            --color-info:#3B82F6;--color-info-bg:#1E3A5F;
            --shadow-sm:0 1px 3px rgba(0,0,0,.4);--shadow-md:0 4px 12px rgba(0,0,0,.5);
            --radius-md:8px;--radius-lg:12px;--radius-xl:16px;--radius-full:9999px;
        }
        body{background:var(--color-bg-base);color:var(--color-text-primary);min-height:100vh;display:flex;flex-direction:column;}
        .card{background:var(--color-bg-elevated);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:1.75rem;max-width:680px;}
        .form-group{margin-bottom:1rem;}
        .form-group label{display:block;font-size:.84rem;font-weight:500;color:var(--color-text-secondary);margin-bottom:.35rem;}
        .form-control{width:100%;padding:.55rem .8rem;background:var(--color-bg-overlay);border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);color:var(--color-text-primary);font-size:.875rem;outline:none;}
        .form-control:focus{border-color:var(--color-primary);}
        textarea.form-control{resize:vertical;}
        .field-error{font-size:.78rem;color:var(--color-error);margin-top:.2rem;display:none;}
        .field-error.show{display:block;}
        .alert{padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;margin-bottom:1rem;}
        .alert-error{background:var(--color-error-bg);color:var(--color-error);}
        .alert-success{background:var(--color-success-bg);color:var(--color-success);}
        .btn{display:inline-flex;align-items:center;gap:.3rem;padding:.45rem .9rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;cursor:pointer;border:none;transition:all .15s;text-decoration:none;}
        .btn-primary{background:var(--color-primary);color:#fff;} .btn-primary:hover{background:var(--color-primary-hover);}
        .btn-ghost{background:transparent;color:var(--color-text-secondary);border:1px solid var(--color-border-subtle);} .btn-ghost:hover{background:var(--color-bg-elevated);}
        .btn:disabled{opacity:.5;cursor:not-allowed;}
        .chk-list{max-height:220px;overflow-y:auto;border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);padding:.4rem;}
        .chk-item{display:flex;align-items:center;gap:.6rem;padding:.35rem .5rem;border-radius:var(--radius-md);}
        .chk-item:hover{background:var(--color-bg-overlay);}
        .chk-item input[type=checkbox]{accent-color:var(--color-primary);width:14px;height:14px;}
        .chk-item label{cursor:pointer;font-size:.86rem;color:var(--color-text-secondary);}
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
        ['label'=>'New Campaign'],
    ]" />

    <div style="margin:1.5rem 0;">
        <h1 style="font-size:1.5rem;font-weight:700;">New Campaign</h1>
    </div>

    <div class="card">
        <div id="form-err" class="alert alert-error" style="display:none;"></div>
        <div id="form-ok"  class="alert alert-success" style="display:none;"></div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group" style="grid-column:1/-1;">
                <label>Campaign Name <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_name" placeholder="e.g. Annual Trip 2026">
                <span class="field-error" id="err-name"></span>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label>Description</label>
                <textarea class="form-control" id="f_desc" rows="3" placeholder="What is this campaign for?"></textarea>
            </div>
            <div class="form-group">
                <label>Target Amount (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_target" type="number" step="0.01" placeholder="0.00">
                <span class="field-error" id="err-target_amount"></span>
            </div>
            <div></div>
            <div class="form-group">
                <label>Cuota mensual (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_fee" type="number" step="0.01" min="0.01" placeholder="1.00" value="1.00">
                <span class="field-error" id="err-monthly_fee_amount"></span>
                <small style="color:var(--color-text-muted);font-size:.75rem;">Monto que se cobra a cada miembro por mes.</small>
            </div>
            <div class="form-group">
                <label>Mora diaria (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_penalty" type="number" step="0.0001" min="0" placeholder="0.05" value="0.05">
                <span class="field-error" id="err-daily_penalty_rate"></span>
                <small style="color:var(--color-text-muted);font-size:.75rem;">Monto por cada día de atraso en el pago.</small>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label>Día de cobro mensual <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_due_day" type="number" min="1" max="28" step="1" value="15"
                       style="max-width:120px;">
                <span class="field-error" id="err-due_day"></span>
                <small style="color:var(--color-text-muted);font-size:.75rem;display:block;margin-top:.2rem;">
                    Día del mes en que vence el cobro (1–28). Máximo 28 para que aplique en todos los meses.
                </small>
            </div>
            <div class="form-group">
                <label>Start Date <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_start" type="date">
                <span class="field-error" id="err-start_date"></span>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input class="form-control" id="f_end" type="date">
            </div>
        </div>

        <div class="form-group" style="margin-top:.5rem;">
            <label>Enroll Members (optional)</label>
            <input class="form-control" id="mem-search" placeholder="Search member…" oninput="filterMembers()" style="margin-bottom:.5rem;">
            <div class="chk-list" id="mem-list">
                <p style="color:var(--color-text-muted);padding:.5rem;font-size:.85rem;">Loading members…</p>
            </div>
        </div>

        <div style="margin-top:.5rem;background:var(--color-info-bg);border:1px solid var(--color-info);border-radius:var(--radius-md);padding:.65rem 1rem;font-size:.82rem;color:var(--color-text-secondary);">
            Status: <strong style="color:var(--color-info);">Draft</strong>. Actívala cuando esté lista para cobrar.
        </div>

        <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.25rem;">
            <a href="{{ route('campaigns.index') }}" class="btn btn-ghost">Cancel</a>
            <button class="btn btn-primary" id="btn-save" onclick="submitForm()">Save Campaign</button>
        </div>
    </div>

</x-layout.main>
</div>

<x-layout.footer />

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let _members = [];

async function loadMembers() {
    try {
        const r = await fetch('{{ url("v1/financial/members") }}?status=1', {headers:{'Accept':'application/json'}});
        if (!r.ok) return;
        const j = await r.json();
        _members = j.data ?? j ?? [];
        renderMembers(_members);
    } catch {
        document.getElementById('mem-list').innerHTML = '<p style="color:var(--color-text-muted);padding:.5rem;font-size:.85rem;">Could not load members.</p>';
    }
}

function renderMembers(list) {
    const el = document.getElementById('mem-list');
    if (!list.length) { el.innerHTML = '<p style="color:var(--color-text-muted);padding:.5rem;font-size:.85rem;">No members available.</p>'; return; }
    el.innerHTML = list.map(m => `
        <div class="chk-item">
            <input type="checkbox" id="m${m.oid}" value="${m.oid}">
            <label for="m${m.oid}">${m.first_name} ${m.last_name} <span style="color:var(--color-text-muted);font-size:.78rem;">${m.identification}</span></label>
        </div>`).join('');
}

function filterMembers() {
    const q = document.getElementById('mem-search').value.toLowerCase();
    renderMembers(_members.filter(m => (m.first_name+' '+m.last_name+' '+m.identification).toLowerCase().includes(q)));
}

async function submitForm() {
    document.querySelectorAll('.field-error').forEach(el => { el.textContent=''; el.classList.remove('show'); });
    document.getElementById('form-err').style.display = 'none';
    const btn = document.getElementById('btn-save');
    btn.disabled = true; btn.textContent = 'Saving…';
    const checkedOids = [...document.querySelectorAll('#mem-list input:checked')].map(c => parseInt(c.value));
    try {
        const res = await fetch('{{ route("campaigns.store") }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify({
                name:                document.getElementById('f_name').value.trim() || null,
                description:         document.getElementById('f_desc').value.trim() || null,
                target_amount:       parseFloat(document.getElementById('f_target').value) || null,
                monthly_fee_amount:  parseFloat(document.getElementById('f_fee').value) || null,
                daily_penalty_rate:  parseFloat(document.getElementById('f_penalty').value) ?? null,
                due_day:             parseInt(document.getElementById('f_due_day').value) || null,
                start_date:          document.getElementById('f_start').value || null,
                end_date:            document.getElementById('f_end').value || null,
                member_oids:         checkedOids,
            }),
        });
        const json = await res.json();
        if (res.ok && json.status) {
            const ok = document.getElementById('form-ok');
            ok.textContent = 'Campaign created! Redirecting…'; ok.style.display='block';
            setTimeout(() => location.href = '{{ route("campaigns.index") }}', 1200);
        } else if (res.status === 422 && json.errors) {
            Object.entries(json.errors).forEach(([f,m]) => {
                const el = document.getElementById('err-'+f); if(el){el.textContent=m[0];el.classList.add('show');}
            });
        } else {
            const e = document.getElementById('form-err'); e.textContent=json.message||'Error.'; e.style.display='block';
        }
    } catch { const e=document.getElementById('form-err'); e.textContent='Connection error.'; e.style.display='block'; }
    finally { btn.disabled=false; btn.textContent='Save Campaign'; }
}

loadMembers();
</script>
</body>
</html>
