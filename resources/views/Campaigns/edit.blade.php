<!DOCTYPE html>
<html lang="en" style="background:var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Campaign — FestiFondo</title>
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
        ['label'=>$campaign['name'], 'url'=>route('campaigns.show', $campaign['uuid'])],
        ['label'=>'Edit'],
    ]" />

    <div style="margin:1.5rem 0;">
        <h1 style="font-size:1.5rem;font-weight:700;">Edit: {{ $campaign['name'] }}</h1>
    </div>

    <div class="card">
        <div id="form-err" class="alert alert-error" style="display:none;"></div>
        <div id="form-ok"  class="alert alert-success" style="display:none;"></div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group" style="grid-column:1/-1;">
                <label>Campaign Name <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_name" value="{{ $campaign['name'] }}">
                <span class="field-error" id="err-name"></span>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label>Description</label>
                <textarea class="form-control" id="f_desc" rows="3">{{ $campaign['description'] }}</textarea>
            </div>
            <div class="form-group">
                <label>Target Amount (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_target" type="number" step="0.01" value="{{ $campaign['target_amount'] }}">
                <span class="field-error" id="err-target_amount"></span>
            </div>
            <div class="form-group">
                <label>Cuota mensual (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_fee" type="number" step="0.01" min="0.01" value="{{ $campaign['monthly_fee_amount'] ?? 1.00 }}">
                <span class="field-error" id="err-monthly_fee_amount"></span>
                <small style="color:var(--color-text-muted);font-size:.75rem;">Monto mensual por miembro.</small>
            </div>
            <div class="form-group">
                <label>Mora diaria (USD) <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_penalty" type="number" step="0.0001" min="0" value="{{ $campaign['daily_penalty_rate'] ?? 0.05 }}">
                <span class="field-error" id="err-daily_penalty_rate"></span>
                <small style="color:var(--color-text-muted);font-size:.75rem;">USD por día de atraso.</small>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label>Día de cobro mensual <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_due_day" type="number" min="1" max="28" step="1"
                       value="{{ $campaign['due_day'] ?? 15 }}" style="max-width:120px;">
                <span class="field-error" id="err-due_day"></span>
                <small style="color:var(--color-text-muted);font-size:.75rem;display:block;margin-top:.2rem;">
                    Día del mes en que vence el cobro (1–28).
                </small>
            </div>
            <div class="form-group">
                <label>Status <span style="color:var(--color-error);">*</span></label>
                <select class="form-control" id="f_status">
                    @foreach(['draft','active','completed','cancelled'] as $st)
                    <option value="{{ $st }}" {{ $campaign['campaign_status'] === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <span class="field-error" id="err-campaign_status"></span>
            </div>
            <div class="form-group">
                <label>Start Date <span style="color:var(--color-error);">*</span></label>
                <input class="form-control" id="f_start" type="date" value="{{ $campaign['start_date'] }}">
                <span class="field-error" id="err-start_date"></span>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input class="form-control" id="f_end" type="date" value="{{ $campaign['end_date'] ?? '' }}">
            </div>
        </div>

        <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.25rem;">
            <a href="{{ route('campaigns.show', $campaign['uuid']) }}" class="btn btn-ghost">Cancel</a>
            <button class="btn btn-primary" id="btn-save" onclick="submitUpdate()">Save Changes</button>
        </div>
    </div>

</x-layout.main>
</div>

<x-layout.footer />

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const UUID = '{{ $campaign["uuid"] }}';

async function submitUpdate() {
    document.querySelectorAll('.field-error').forEach(el => { el.textContent=''; el.classList.remove('show'); });
    document.getElementById('form-err').style.display = 'none';
    const btn = document.getElementById('btn-save');
    btn.disabled=true; btn.textContent='Saving…';
    try {
        const res = await fetch('{{ route("campaigns.update", $campaign["uuid"]) }}', {
            method: 'PUT',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify({
                name:                document.getElementById('f_name').value.trim(),
                description:         document.getElementById('f_desc').value.trim()||null,
                target_amount:       parseFloat(document.getElementById('f_target').value),
                monthly_fee_amount:  parseFloat(document.getElementById('f_fee').value),
                daily_penalty_rate:  parseFloat(document.getElementById('f_penalty').value),
                due_day:             parseInt(document.getElementById('f_due_day').value),
                start_date:          document.getElementById('f_start').value,
                end_date:            document.getElementById('f_end').value || null,
                campaign_status:     document.getElementById('f_status').value,
            }),
        });
        const json = await res.json();
        if (res.ok && json.status) {
            const ok = document.getElementById('form-ok'); ok.textContent='Saved! Redirecting…'; ok.style.display='block';
            setTimeout(() => location.href = '{{ route("campaigns.show", $campaign["uuid"]) }}', 1200);
        } else if (res.status === 422 && json.errors) {
            Object.entries(json.errors).forEach(([f,m]) => {
                const el = document.getElementById('err-'+f); if(el){el.textContent=m[0];el.classList.add('show');}
            });
        } else {
            const e = document.getElementById('form-err'); e.textContent=json.message||'Error.'; e.style.display='block';
        }
    } catch { const e=document.getElementById('form-err'); e.textContent='Connection error.'; e.style.display='block'; }
    finally { btn.disabled=false; btn.textContent='Save Changes'; }
}
</script>
</body>
</html>
