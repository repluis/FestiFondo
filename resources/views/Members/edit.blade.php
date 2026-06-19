<!DOCTYPE html>
<html lang="en" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Member — FestiFondo</title>
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
        .field-error { font-size:0.8rem; color:var(--color-error); margin-top:0.25rem; display:none; }
        .field-error.show { display:block; }
        .readonly-field {
            background:var(--color-bg-overlay); border:1px solid var(--color-border);
            border-radius:var(--radius-md); padding:.55rem .875rem;
            color:var(--color-text-disabled); font-size:.875rem; width:100%;
        }
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
            ['label'=>$member['full_name'],'url'=>route('members.show',$member['uuid'])],
            ['label'=>'Edit'],
        ]" />

        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;">Edit Member</h1>
                <p style="color:var(--color-text-muted);font-size:.875rem;margin-top:.2rem;">
                    Editing: <strong style="color:var(--color-accent);">{{ $member['identification'] }}</strong>
                    — {{ $member['full_name'] }}
                </p>
            </div>
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('members.show', $member['uuid']) }}">
                    <x-ui.button variant="ghost">← View Detail</x-ui.button>
                </a>
                <a href="{{ route('members.index') }}">
                    <x-ui.button variant="ghost">Members List</x-ui.button>
                </a>
            </div>
        </div>

        <x-ui.card>
            <form id="form-edit" onsubmit="submitEdit(event)">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

                    {{-- Identification — read only --}}
                    <div>
                        <label style="font-size:.875rem;font-weight:500;color:var(--color-text-secondary);">
                            Identification (CI)
                        </label>
                        <div class="readonly-field" style="margin-top:.35rem;">
                            {{ $member['identification'] }}
                        </div>
                        <p style="font-size:.75rem;color:var(--color-text-muted);margin-top:.25rem;">
                            Cannot be changed after creation.
                        </p>
                    </div>

                    <div>
                        <x-form.input label="Join Date" name="joined_at" type="date" :required="true"
                                      value="{{ $member['joined_at'] }}" />
                        <span class="field-error" id="err-joined_at"></span>
                    </div>

                    <div>
                        <x-form.input label="First Name" name="first_name" placeholder="e.g. Juan" :required="true"
                                      value="{{ $member['first_name'] }}" />
                        <span class="field-error" id="err-first_name"></span>
                    </div>
                    <div>
                        <x-form.input label="Last Name" name="last_name" placeholder="e.g. Pérez" :required="true"
                                      value="{{ $member['last_name'] }}" />
                        <span class="field-error" id="err-last_name"></span>
                    </div>

                    <div>
                        <x-form.input label="Email" name="email" type="email" placeholder="juan@example.com"
                                      value="{{ $member['email'] ?? '' }}" />
                        <span class="field-error" id="err-email"></span>
                    </div>
                    <div>
                        <x-form.input label="Phone" name="phone" placeholder="+593 99 000 0000"
                                      value="{{ $member['phone'] ?? '' }}" />
                        <span class="field-error" id="err-phone"></span>
                    </div>

                    <div style="grid-column:1/-1;">
                        <x-form.textarea label="Address" name="address" rows="2"
                                         placeholder="Full address (optional)…">{{ $member['address'] ?? '' }}</x-form.textarea>
                        <span class="field-error" id="err-address"></span>
                    </div>
                    <div style="grid-column:1/-1;">
                        <x-form.textarea label="Notes" name="notes" rows="3"
                                         placeholder="Internal notes (optional)…">{{ $member['notes'] ?? '' }}</x-form.textarea>
                        <span class="field-error" id="err-notes"></span>
                    </div>

                </div>

                <div id="page-error-msg" style="display:none;margin-top:1rem;
                    background:var(--color-error-bg);border:1px solid var(--color-error);
                    border-radius:var(--radius-md);padding:.75rem 1rem;
                    color:var(--color-error);font-size:.875rem;"></div>

                <div id="page-success-msg" style="display:none;margin-top:1rem;
                    background:var(--color-success-bg);border:1px solid var(--color-success);
                    border-radius:var(--radius-md);padding:.75rem 1rem;
                    color:var(--color-success);font-size:.875rem;"></div>

                <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.5rem;
                    padding-top:1rem;border-top:1px solid var(--color-border);">
                    <a href="{{ route('members.index') }}">
                        <x-ui.button variant="ghost" type="button">Cancel</x-ui.button>
                    </a>
                    <x-ui.button variant="primary" type="submit" id="btn-submit">
                        Update Member
                    </x-ui.button>
                </div>

            </form>
        </x-ui.card>

    </x-layout.main>
</div>

<x-layout.footer />

<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const MEMBER_UUID = '{{ $member['uuid'] }}';
const UPDATE_URL  = '{{ route("members.update", $member["uuid"]) }}';

async function submitEdit(e) {
    e.preventDefault();

    document.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
    document.getElementById('page-error-msg').style.display   = 'none';
    document.getElementById('page-success-msg').style.display = 'none';

    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const get = id => document.getElementById(id)?.value?.trim() || null;

    const payload = {
        first_name: get('first_name'),
        last_name:  get('last_name'),
        email:      get('email'),
        phone:      get('phone'),
        address:    get('address'),
        notes:      get('notes'),
        joined_at:  get('joined_at'),
    };

    try {
        const res  = await fetch(UPDATE_URL, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();

        if (res.ok && json.status) {
            const s = document.getElementById('page-success-msg');
            s.textContent = 'Member updated successfully. Redirecting…';
            s.style.display = 'block';
            setTimeout(() => { window.location.href = '{{ route("members.index") }}'; }, 1000);
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
        btn.textContent = 'Update Member';
    }
}
</script>

</body>
</html>
