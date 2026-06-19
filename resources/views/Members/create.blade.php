<!DOCTYPE html>
<html lang="en" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>New Member — FestiFondo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        :root {
            --color-bg-base:#0A0F1E; --color-bg-surface:#0F172A; --color-bg-elevated:#1E293B;
            --color-bg-overlay:#263347; --color-primary:#2563EB; --color-primary-hover:#1D4ED8;
            --color-primary-light:#3B82F6; --color-primary-subtle:#1E3A5F;
            --color-secondary:#0EA5E9; --color-accent:#06B6D4;
            --color-text-primary:#F8FAFC; --color-text-secondary:#94A3B8;
            --color-text-muted:#64748B; --color-text-disabled:#475569;
            --color-border:#1E293B; --color-border-subtle:#334155;
            --color-success:#10B981; --color-success-bg:#064E3B;
            --color-warning:#F59E0B; --color-warning-bg:#451A03;
            --color-error:#EF4444; --color-error-bg:#450A0A;
            --color-info:#3B82F6; --color-info-bg:#1E3A5F;
            --shadow-sm:0 1px 3px rgba(0,0,0,.4); --shadow-md:0 4px 12px rgba(0,0,0,.5);
            --shadow-lg:0 8px 24px rgba(0,0,0,.6);
            --radius-sm:4px; --radius-md:8px; --radius-lg:12px; --radius-xl:16px; --radius-full:9999px;
        }
        .field-error { font-size: 0.8rem; color: var(--color-error); margin-top: 0.25rem; display: none; }
        .field-error.show { display: block; }
    </style>
</head>
<body style="background:var(--color-bg-base);color:var(--color-text-primary);min-height:100vh;display:flex;flex-direction:column;">

<x-layout.app-header />

<div style="display:flex;flex:1;">
    <x-layout.app-sidebar />
    <x-layout.main>

        <x-ui.breadcrumb :items="[
            ['label'=>'Home',     'url'=>'/'],
            ['label'=>'Financial','url'=>'#'],
            ['label'=>'Members',  'url'=>route('members.index')],
            ['label'=>'New Member'],
        ]" />

        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;">New Member</h1>
                <p style="color:var(--color-text-muted);font-size:.875rem;margin-top:.2rem;">
                    Fill in the information below to register a new member.
                </p>
            </div>
            <a href="{{ route('members.index') }}">
                <x-ui.button variant="ghost">← Back to Members</x-ui.button>
            </a>
        </div>

        <div id="page-error" style="display:none;margin-bottom:1rem;"></div>

        <x-ui.card>
            <form id="form-create" onsubmit="submitCreate(event)">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

                    <div>
                        <x-form.input label="Identification (CI)" name="identification" placeholder="e.g. 1234567890" :required="true" />
                        <span class="field-error" id="err-identification"></span>
                    </div>
                    <div>
                        <x-form.input label="Join Date" name="joined_at" type="date" :required="true" />
                        <span class="field-error" id="err-joined_at"></span>
                    </div>

                    <div>
                        <x-form.input label="First Name" name="first_name" placeholder="e.g. Juan" :required="true" />
                        <span class="field-error" id="err-first_name"></span>
                    </div>
                    <div>
                        <x-form.input label="Last Name" name="last_name" placeholder="e.g. Pérez" :required="true" />
                        <span class="field-error" id="err-last_name"></span>
                    </div>

                    <div>
                        <x-form.input label="Email" name="email" type="email" placeholder="juan@example.com" />
                        <span class="field-error" id="err-email"></span>
                    </div>
                    <div>
                        <x-form.input label="Phone" name="phone" placeholder="+593 99 000 0000" />
                        <span class="field-error" id="err-phone"></span>
                    </div>

                    <div style="grid-column:1/-1;">
                        <x-form.textarea label="Address" name="address" placeholder="Full address (optional)…" rows="2" />
                        <span class="field-error" id="err-address"></span>
                    </div>
                    <div style="grid-column:1/-1;">
                        <x-form.textarea label="Notes" name="notes" placeholder="Internal notes (optional)…" rows="3" />
                        <span class="field-error" id="err-notes"></span>
                    </div>

                </div>

                <div id="page-error-msg" style="display:none;margin-top:1rem;
                    background:var(--color-error-bg);border:1px solid var(--color-error);
                    border-radius:var(--radius-md);padding:.75rem 1rem;
                    color:var(--color-error);font-size:.875rem;"></div>

                <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.5rem;
                    padding-top:1rem;border-top:1px solid var(--color-border);">
                    <a href="{{ route('members.index') }}">
                        <x-ui.button variant="ghost" type="button">Cancel</x-ui.button>
                    </a>
                    <x-ui.button variant="primary" type="submit" id="btn-submit">
                        Save Member
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
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const get = id => document.getElementById(id)?.value?.trim() || null;

    const payload = {
        identification: get('identification'),
        first_name:  get('first_name'),
        last_name:   get('last_name'),
        email:       get('email'),
        phone:       get('phone'),
        address:     get('address'),
        notes:       get('notes'),
        joined_at:   get('joined_at'),
    };

    try {
        const res  = await fetch('{{ route("members.store") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();

        if (res.ok && json.status) {
            window.location.href = '{{ route("members.index") }}';
        } else if (res.status === 422 && json.errors) {
            Object.entries(json.errors).forEach(([field, msgs]) => {
                const el = document.getElementById('err-' + field);
                if (el) { el.textContent = msgs[0]; el.classList.add('show'); }
            });
        } else {
            const errEl = document.getElementById('page-error-msg');
            errEl.textContent = json.message || 'Unexpected error. Please try again.';
            errEl.style.display = 'block';
        }
    } catch {
        const errEl = document.getElementById('page-error-msg');
        errEl.textContent = 'Connection error. Please try again.';
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Member';
    }
}
</script>

</body>
</html>
