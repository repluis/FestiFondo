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
    </style>
</head>
<body style="background:var(--color-bg-base);color:var(--color-text-primary);min-height:100vh;display:flex;flex-direction:column;">

<x-layout.app-header />

<div style="display:flex;flex:1;">
    <x-layout.app-sidebar />
    <x-layout.main>

        <x-ui.breadcrumb :items="[
            ['label'=>'Home',        'url'=>'/'],
            ['label'=>'Financial',   'url'=>'#'],
            ['label'=>'Fund Raising','url'=>route('fund-raising.index')],
            ['label'=>'New Campaign'],
        ]" />

        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;">New Campaign</h1>
                <p style="color:var(--color-text-muted);font-size:.875rem;margin-top:.2rem;">
                    Define a new fund raising campaign. It starts in <em>draft</em> status.
                </p>
            </div>
            <a href="{{ route('fund-raising.index') }}">
                <x-ui.button variant="ghost">← Back</x-ui.button>
            </a>
        </div>

        <x-ui.card>
            <form id="form-create" onsubmit="submitCreate(event)">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

                    <div style="grid-column:1/-1;">
                        <x-form.input label="Campaign Name" name="name" placeholder="e.g. Annual Trip 2026" :required="true" />
                        <span class="field-error" id="err-name"></span>
                    </div>

                    <div style="grid-column:1/-1;">
                        <x-form.textarea label="Description" name="description" placeholder="What is this campaign for?" rows="3" />
                        <span class="field-error" id="err-description"></span>
                    </div>

                    <div>
                        <x-form.input label="Target Amount (USD)" name="target_amount" type="number"
                                      placeholder="0.00" :required="true" />
                        <span class="field-error" id="err-target_amount"></span>
                    </div>

                    <div>
                        {{-- empty cell for alignment --}}
                    </div>

                    <div>
                        <x-form.input label="Start Date" name="start_date" type="date" :required="true" />
                        <span class="field-error" id="err-start_date"></span>
                    </div>
                    <div>
                        <x-form.input label="End Date" name="end_date" type="date" />
                        <span class="field-error" id="err-end_date"></span>
                    </div>

                </div>

                <div style="margin-top:1rem;padding:.75rem 1rem;border-radius:var(--radius-md);
                    background:var(--color-info-bg);border:1px solid var(--color-info);font-size:.85rem;
                    color:var(--color-text-secondary);">
                    The campaign will be created with <strong style="color:var(--color-info);">Draft</strong> status.
                    Activate it when ready to start collecting.
                </div>

                <div id="page-error-msg" style="display:none;margin-top:1rem;
                    background:var(--color-error-bg);border:1px solid var(--color-error);
                    border-radius:var(--radius-md);padding:.75rem 1rem;
                    color:var(--color-error);font-size:.875rem;"></div>

                <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.5rem;
                    padding-top:1rem;border-top:1px solid var(--color-border);">
                    <a href="{{ route('fund-raising.index') }}">
                        <x-ui.button variant="ghost" type="button">Cancel</x-ui.button>
                    </a>
                    <x-ui.button variant="primary" type="submit" id="btn-submit">
                        Create Campaign
                    </x-ui.button>
                </div>

            </form>
        </x-ui.card>

    </x-layout.main>
</div>

<x-layout.footer />

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function submitCreate(e) {
    e.preventDefault();
    document.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
    document.getElementById('page-error-msg').style.display = 'none';

    const btn = document.getElementById('btn-submit');
    btn.disabled = true; btn.textContent = 'Creating…';

    const get = id => document.getElementById(id)?.value?.trim() || null;

    const payload = {
        name:          get('name'),
        description:   get('description'),
        target_amount: get('target_amount') ? parseFloat(get('target_amount')) : null,
        start_date:    get('start_date'),
        end_date:      get('end_date'),
    };

    try {
        const res  = await fetch('{{ route("fund-raising.store") }}', {
            method:  'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:    JSON.stringify(payload),
        });
        const json = await res.json();

        if (res.ok && json.status) {
            window.location.href = '{{ route("fund-raising.index") }}';
        } else if (res.status === 422 && json.errors) {
            Object.entries(json.errors).forEach(([f, msgs]) => {
                const el = document.getElementById('err-' + f);
                if (el) { el.textContent = msgs[0]; el.classList.add('show'); }
            });
        } else {
            const e = document.getElementById('page-error-msg');
            e.textContent = json.message || 'Unexpected error.';
            e.style.display = 'block';
        }
    } catch {
        const e = document.getElementById('page-error-msg');
        e.textContent = 'Connection error. Please try again.';
        e.style.display = 'block';
    } finally {
        btn.disabled = false; btn.textContent = 'Create Campaign';
    }
}
</script>
</body>
</html>
