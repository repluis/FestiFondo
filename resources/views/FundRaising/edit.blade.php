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
        .readonly-field{background:var(--color-bg-overlay);border:1px solid var(--color-border);
            border-radius:var(--radius-md);padding:.55rem .875rem;color:var(--color-text-disabled);
            font-size:.875rem;width:100%;}
    </style>
</head>
<body style="background:var(--color-bg-base);color:var(--color-text-primary);min-height:100vh;display:flex;flex-direction:column;">

<x-layout.app-header />

<div style="display:flex;flex:1;">
    <x-layout.app-sidebar />
    <x-layout.main>

        @php
        $transitions = [
            'draft'     => ['active' => 'Activate', 'cancelled' => 'Cancel'],
            'active'    => ['completed' => 'Complete', 'cancelled' => 'Cancel'],
            'completed' => [],
            'cancelled' => [],
        ];
        $allowedStatuses = $transitions[$campaign['fund_raising_status']] ?? [];
        @endphp

        <x-ui.breadcrumb :items="[
            ['label'=>'Home',        'url'=>'/'],
            ['label'=>'Financial',   'url'=>'#'],
            ['label'=>'Fund Raising','url'=>route('fund-raising.index')],
            ['label'=>$campaign['name'],'url'=>route('fund-raising.show',$campaign['uuid'])],
            ['label'=>'Edit'],
        ]" />

        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;">Edit Campaign</h1>
                <p style="color:var(--color-text-muted);font-size:.875rem;margin-top:.2rem;">
                    {{ $campaign['name'] }}
                    @include('FundRaising._status_badge', ['status' => $campaign['fund_raising_status']])
                </p>
            </div>
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('fund-raising.show', $campaign['uuid']) }}">
                    <x-ui.button variant="ghost">← View</x-ui.button>
                </a>
            </div>
        </div>

        <x-ui.card>
            <form id="form-edit" onsubmit="submitEdit(event)">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

                    <div style="grid-column:1/-1;">
                        <x-form.input label="Campaign Name" name="name" :required="true"
                                      value="{{ $campaign['name'] }}" />
                        <span class="field-error" id="err-name"></span>
                    </div>

                    <div style="grid-column:1/-1;">
                        <x-form.textarea label="Description" name="description" rows="3">{{ $campaign['description'] ?? '' }}</x-form.textarea>
                        <span class="field-error" id="err-description"></span>
                    </div>

                    <div>
                        <x-form.input label="Target Amount (USD)" name="target_amount" type="number"
                                      value="{{ $campaign['target_amount'] }}" :required="true" />
                        <span class="field-error" id="err-target_amount"></span>
                    </div>

                    <div>
                        @if(count($allowedStatuses) > 0)
                            <label style="font-size:.875rem;font-weight:500;color:var(--color-text-secondary);">
                                Status Transition
                            </label>
                            <select id="fund_raising_status" name="fund_raising_status"
                                style="margin-top:.35rem;width:100%;background:var(--color-bg-elevated);
                                    border:1px solid var(--color-border-subtle);border-radius:var(--radius-md);
                                    padding:.55rem .875rem;color:var(--color-text-primary);font-size:.875rem;">
                                <option value="{{ $campaign['fund_raising_status'] }}" selected>
                                    Keep current ({{ ucfirst($campaign['fund_raising_status']) }})
                                </option>
                                @foreach($allowedStatuses as $value => $label)
                                    <option value="{{ $value }}">→ {{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="field-error" id="err-fund_raising_status"></span>
                        @else
                            <label style="font-size:.875rem;font-weight:500;color:var(--color-text-secondary);">
                                Status
                            </label>
                            <div class="readonly-field" style="margin-top:.35rem;">
                                {{ ucfirst($campaign['fund_raising_status']) }}
                            </div>
                            <p style="font-size:.75rem;color:var(--color-text-muted);margin-top:.25rem;">
                                Terminal status — no further transitions allowed.
                            </p>
                        @endif
                    </div>

                    <div>
                        <x-form.input label="Start Date" name="start_date" type="date" :required="true"
                                      value="{{ $campaign['start_date'] }}" />
                        <span class="field-error" id="err-start_date"></span>
                    </div>
                    <div>
                        <x-form.input label="End Date" name="end_date" type="date"
                                      value="{{ $campaign['end_date'] ?? '' }}" />
                        <span class="field-error" id="err-end_date"></span>
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
                    <a href="{{ route('fund-raising.index') }}">
                        <x-ui.button variant="ghost" type="button">Cancel</x-ui.button>
                    </a>
                    <x-ui.button variant="primary" type="submit" id="btn-submit">
                        Update Campaign
                    </x-ui.button>
                </div>

            </form>
        </x-ui.card>

    </x-layout.main>
</div>

<x-layout.footer />

<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const UPDATE_URL = '{{ route("fund-raising.update", $campaign["uuid"]) }}';

async function submitEdit(e) {
    e.preventDefault();
    document.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
    document.getElementById('page-error-msg').style.display   = 'none';
    document.getElementById('page-success-msg').style.display = 'none';

    const btn = document.getElementById('btn-submit');
    btn.disabled = true; btn.textContent = 'Saving…';

    const get = id => document.getElementById(id)?.value?.trim() || null;

    const payload = {
        name:                 get('name'),
        description:          get('description'),
        target_amount:        get('target_amount') ? parseFloat(get('target_amount')) : null,
        start_date:           get('start_date'),
        end_date:             get('end_date'),
        fund_raising_status:  get('fund_raising_status'),
    };

    try {
        const res  = await fetch(UPDATE_URL, {
            method:  'PUT',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:    JSON.stringify(payload),
        });
        const json = await res.json();

        if (res.ok && json.status) {
            const s = document.getElementById('page-success-msg');
            s.textContent = 'Campaign updated. Redirecting…';
            s.style.display = 'block';
            setTimeout(() => { window.location.href = '{{ route("fund-raising.index") }}'; }, 900);
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
        btn.disabled = false; btn.textContent = 'Update Campaign';
    }
}
</script>
</body>
</html>
