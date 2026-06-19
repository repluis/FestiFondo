<!DOCTYPE html>
<html lang="en" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Members — FestiFondo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }

        :root {
            --color-bg-base:        #0A0F1E;
            --color-bg-surface:     #0F172A;
            --color-bg-elevated:    #1E293B;
            --color-bg-overlay:     #263347;
            --color-primary:        #2563EB;
            --color-primary-hover:  #1D4ED8;
            --color-primary-light:  #3B82F6;
            --color-primary-subtle: #1E3A5F;
            --color-secondary:       #0EA5E9;
            --color-accent:       #06B6D4;
            --color-text-primary:   #F8FAFC;
            --color-text-secondary: #94A3B8;
            --color-text-muted:     #64748B;
            --color-text-disabled:  #475569;
            --color-border:        #1E293B;
            --color-border-subtle: #334155;
            --color-success:    #10B981;
            --color-success-bg: #064E3B;
            --color-warning:    #F59E0B;
            --color-warning-bg: #451A03;
            --color-error:      #EF4444;
            --color-error-bg:   #450A0A;
            --color-info:       #3B82F6;
            --color-info-bg:    #1E3A5F;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.4);
            --shadow-md: 0 4px 12px rgba(0,0,0,.5);
            --shadow-lg: 0 8px 24px rgba(0,0,0,.6);
            --radius-sm: 4px; --radius-md: 8px; --radius-lg: 12px;
            --radius-xl: 16px; --radius-full: 9999px;
        }

        .field-error { font-size: 0.8rem; color: var(--color-error); margin-top: 0.25rem; display: none; }
        .field-error.show { display: block; }
        .toast {
            position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
            padding: 0.75rem 1.25rem; border-radius: var(--radius-md);
            font-size: 0.875rem; font-weight: 500; box-shadow: var(--shadow-lg);
            display: none; align-items: center; gap: 0.5rem;
        }
        .toast.success { background: var(--color-success-bg); color: var(--color-success); display: flex; }
        .toast.error   { background: var(--color-error-bg);   color: var(--color-error);   display: flex; }
    </style>
</head>
<body style="background: var(--color-bg-base); color: var(--color-text-primary); min-height: 100vh; display: flex; flex-direction: column;">

{{-- HEADER --}}
<x-layout.app-header />

{{-- BODY --}}
<div style="display: flex; flex: 1;">

    <x-layout.app-sidebar />

    <x-layout.main>

        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Home',      'url' => '/'],
            ['label' => 'Financial', 'url' => '#'],
            ['label' => 'Members'],
        ]" />

        {{-- Page title --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);">Members</h1>
                <p style="color:var(--color-text-muted);font-size:.875rem;margin-top:.2rem;">
                    Manage member records and contribution status
                </p>
            </div>
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('members.create') }}">
                    <x-ui.button variant="ghost">Full Form</x-ui.button>
                </a>
                <x-ui.button variant="primary" onclick="openModal('modal-create')">+ New Member</x-ui.button>
            </div>
        </div>

        {{-- Stats --}}
        @php
            $activeCount   = count(array_filter($members ?? [], fn($m) => $m['status'] === true));
            $inactiveCount = count(array_filter($members ?? [], fn($m) => $m['status'] === false));
        @endphp
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
            <x-ui.card>
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Total Members</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;">{{ count($members ?? []) }}</p>
            </x-ui.card>
            <x-ui.card>
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Active</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;color:var(--color-success);">{{ $activeCount }}</p>
            </x-ui.card>
            <x-ui.card>
                <p style="font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;">Inactive</p>
                <p style="font-size:1.75rem;font-weight:700;margin-top:.25rem;color:var(--color-text-muted);">{{ $inactiveCount }}</p>
            </x-ui.card>
        </div>

        {{-- Load error --}}
        @isset($loadError)
            <x-ui.alert type="error" title="Error">{{ $loadError }}</x-ui.alert>
        @endisset

        {{-- Table card --}}
        <x-ui.card title="Members List">

            {{-- Filters --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:1rem;">
                <x-form.input name="search" placeholder="Search by name, CI or email…"
                              style="max-width:280px;" oninput="filterTable()" />
                <x-form.select name="filter_status" style="max-width:160px;"
                               :options="['all'=>'All statuses','1'=>'Active','0'=>'Inactive']"
                               onchange="filterTable()" />
            </div>

            <x-ui.table :headers="['CI / ID','Full Name','Email','Phone','Joined','Status','Actions']">
                @forelse($members ?? [] as $member)
                <tr class="member-row"
                    data-name="{{ strtolower($member['full_name']) }}"
                    data-code="{{ strtolower($member['identification']) }}"
                    data-email="{{ strtolower($member['email'] ?? '') }}"
                    data-status="{{ $member['status'] ? '1' : '0' }}"
                    style="border-bottom:1px solid var(--color-border);transition:background .1s;"
                    onmouseover="this.style.background='var(--color-bg-overlay)'"
                    onmouseout="this.style.background='transparent'">

                    <td style="padding:.875rem 1rem;font-weight:600;color:var(--color-accent);">
                        {{ $member['identification'] }}
                    </td>
                    <td style="padding:.875rem 1rem;color:var(--color-text-primary);font-weight:500;">
                        {{ $member['full_name'] }}
                    </td>
                    <td style="padding:.875rem 1rem;color:var(--color-text-secondary);">
                        {{ $member['email'] ?? '—' }}
                    </td>
                    <td style="padding:.875rem 1rem;color:var(--color-text-secondary);">
                        {{ $member['phone'] ?? '—' }}
                    </td>
                    <td style="padding:.875rem 1rem;color:var(--color-text-muted);font-size:.85rem;">
                        {{ $member['joined_at'] }}
                    </td>
                    <td style="padding:.875rem 1rem;">
                        @if($member['status'])
                            <x-ui.badge variant="success">Active</x-ui.badge>
                        @else
                            <x-ui.badge variant="muted">Inactive</x-ui.badge>
                        @endif
                    </td>
                    <td style="padding:.875rem 1rem;">
                        <div style="display:flex;gap:.4rem;align-items:center;">
                            <a href="{{ route('members.show', $member['uuid']) }}">
                                <x-ui.button variant="ghost" size="sm">View</x-ui.button>
                            </a>
                            <a href="{{ route('members.edit', $member['uuid']) }}">
                                <x-ui.button variant="ghost" size="sm">Edit</x-ui.button>
                            </a>
                            @if($member['status'])
                                <x-ui.button variant="danger" size="sm"
                                    onclick="confirmDeactivate('{{ $member['uuid'] }}', '{{ addslashes($member['full_name']) }}')">
                                    Deactivate
                                </x-ui.button>
                            @else
                                <x-ui.button variant="primary" size="sm"
                                    onclick="confirmActivate('{{ $member['uuid'] }}', '{{ addslashes($member['full_name']) }}')">
                                    Activate
                                </x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:3rem 1rem;text-align:center;color:var(--color-text-muted);">
                        No members found.
                        <a href="{{ route('members.create') }}" style="color:var(--color-primary-light);margin-left:.5rem;">
                            Add the first one →
                        </a>
                    </td>
                </tr>
                @endforelse
            </x-ui.table>

        </x-ui.card>

    </x-layout.main>
</div>

<x-layout.footer />

{{-- ── MODAL: Create Member ────────────────────────────────────────────────── --}}
<x-ui.modal id="modal-create" title="New Member" size="lg">
    <form id="form-create" onsubmit="submitCreate(event)">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <x-form.input label="Identification (CI)" name="c_identification" placeholder="e.g. 1234567890" :required="true" />
                <span class="field-error" id="err-identification"></span>
            </div>
            <div>
                <x-form.input label="Join Date" name="c_joined_at" type="date" :required="true" />
                <span class="field-error" id="err-joined_at"></span>
            </div>
            <div>
                <x-form.input label="First Name" name="c_first_name" placeholder="e.g. Juan" :required="true" />
                <span class="field-error" id="err-first_name"></span>
            </div>
            <div>
                <x-form.input label="Last Name" name="c_last_name" placeholder="e.g. Pérez" :required="true" />
                <span class="field-error" id="err-last_name"></span>
            </div>
            <div>
                <x-form.input label="Email" name="c_email" type="email" placeholder="juan@example.com" />
                <span class="field-error" id="err-email"></span>
            </div>
            <div>
                <x-form.input label="Phone" name="c_phone" placeholder="+593 99 000 0000" />
                <span class="field-error" id="err-phone"></span>
            </div>
        </div>

        <div style="margin-top:1rem;">
            <x-form.textarea label="Address" name="c_address" placeholder="Full address…" rows="2" />
        </div>
        <div style="margin-top:1rem;">
            <x-form.textarea label="Notes" name="c_notes" placeholder="Internal notes…" rows="2" />
        </div>

        <div id="create-general-error" style="display:none;margin-top:1rem;">
            <x-ui.alert type="error" title="Error"><span id="create-general-error-msg"></span></x-ui.alert>
        </div>
    </form>

    <x-slot:footer>
        <x-ui.button variant="ghost" onclick="closeModal('modal-create'); clearCreateForm()">Cancel</x-ui.button>
        <x-ui.button variant="primary" onclick="submitCreate(event)" id="btn-create">
            Save Member
        </x-ui.button>
    </x-slot:footer>
</x-ui.modal>

{{-- ── MODAL: Confirm Deactivate ───────────────────────────────────────────── --}}
<x-ui.modal id="modal-deactivate" title="Deactivate Member" size="sm">
    <p style="color:var(--color-text-secondary);">
        Are you sure you want to deactivate
        <strong id="deactivate-name" style="color:var(--color-text-primary);"></strong>?
        This action sets the member as inactive. No data will be deleted.
    </p>

    <x-slot:footer>
        <x-ui.button variant="ghost" onclick="closeModal('modal-deactivate')">Cancel</x-ui.button>
        <x-ui.button variant="danger" onclick="submitDeactivate()" id="btn-deactivate">
            Yes, Deactivate
        </x-ui.button>
    </x-slot:footer>
</x-ui.modal>

{{-- ── MODAL: Confirm Activate ─────────────────────────────────────────────── --}}
<x-ui.modal id="modal-activate" title="Activate Member" size="sm">
    <p style="color:var(--color-text-secondary);">
        Are you sure you want to activate
        <strong id="activate-name" style="color:var(--color-text-primary);"></strong>?
        The member will be set as active again.
    </p>

    <x-slot:footer>
        <x-ui.button variant="ghost" onclick="closeModal('modal-activate')">Cancel</x-ui.button>
        <x-ui.button variant="primary" onclick="submitActivate()" id="btn-activate">
            Yes, Activate
        </x-ui.button>
    </x-slot:footer>
</x-ui.modal>

{{-- Toast notification --}}
<div id="toast" class="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Filter ────────────────────────────────────────────────────────────────────
function filterTable() {
    const search = document.getElementById('search').value.toLowerCase().trim();
    const status = document.getElementById('filter_status').value;

    document.querySelectorAll('.member-row').forEach(row => {
        const matchSearch = !search ||
            row.dataset.name.includes(search) ||
            row.dataset.code.includes(search) ||
            row.dataset.email.includes(search);
        const matchStatus = status === 'all' || row.dataset.status === status;
        row.style.display = matchSearch && matchStatus ? '' : 'none';
    });
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.className = 'toast ' + type;
    t.textContent = msg;
    setTimeout(() => { t.className = 'toast'; }, 3500);
}

// ── Create ────────────────────────────────────────────────────────────────────
function clearCreateForm() {
    ['c_identification','c_first_name','c_last_name','c_email','c_phone','c_joined_at','c_address','c_notes']
        .forEach(n => { const el = document.getElementById(n); if (el) el.value = ''; });
    document.querySelectorAll('.field-error').forEach(e => e.classList.remove('show'));
    document.getElementById('create-general-error').style.display = 'none';
}

async function submitCreate(e) {
    if (e && e.preventDefault) e.preventDefault();

    // Clear previous errors
    document.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
    document.getElementById('create-general-error').style.display = 'none';

    const btn = document.getElementById('btn-create');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const payload = {
        identification: document.getElementById('c_identification').value.trim(),
        first_name:  document.getElementById('c_first_name').value.trim(),
        last_name:   document.getElementById('c_last_name').value.trim(),
        email:       document.getElementById('c_email').value.trim() || null,
        phone:       document.getElementById('c_phone').value.trim() || null,
        address:     document.getElementById('c_address').value.trim() || null,
        notes:       document.getElementById('c_notes').value.trim() || null,
        joined_at:   document.getElementById('c_joined_at').value,
    };

    try {
        const res = await fetch('{{ route("members.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });

        const json = await res.json();

        if (res.ok && json.status) {
            closeModal('modal-create');
            clearCreateForm();
            showToast('Member created successfully.');
            setTimeout(() => location.reload(), 800);
        } else if (res.status === 422 && json.errors) {
            Object.entries(json.errors).forEach(([field, msgs]) => {
                const el = document.getElementById('err-' + field);
                if (el) { el.textContent = msgs[0]; el.classList.add('show'); }
            });
        } else {
            document.getElementById('create-general-error-msg').textContent = json.message || 'Unexpected error.';
            document.getElementById('create-general-error').style.display = 'block';
        }
    } catch (err) {
        document.getElementById('create-general-error-msg').textContent = 'Connection error. Please try again.';
        document.getElementById('create-general-error').style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Member';
    }
}

// ── Activate ──────────────────────────────────────────────────────────────────
let _activateUuid = null;

function confirmActivate(uuid, name) {
    _activateUuid = uuid;
    document.getElementById('activate-name').textContent = name;
    openModal('modal-activate');
}

async function submitActivate() {
    if (!_activateUuid) return;

    const btn = document.getElementById('btn-activate');
    btn.disabled = true;
    btn.textContent = 'Activating…';

    try {
        const url = '{{ url("v1/financial/members") }}/' + _activateUuid + '/activate';
        const res = await fetch(url, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });

        const json = await res.json();

        closeModal('modal-activate');

        if (json.status) {
            showToast('Member activated successfully.');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.message || 'Could not activate member.', 'error');
        }
    } catch {
        showToast('Connection error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Yes, Activate';
        _activateUuid = null;
    }
}

// ── Deactivate ────────────────────────────────────────────────────────────────
let _deactivateUuid = null;

function confirmDeactivate(uuid, name) {
    _deactivateUuid = uuid;
    document.getElementById('deactivate-name').textContent = name;
    openModal('modal-deactivate');
}

async function submitDeactivate() {
    if (!_deactivateUuid) return;

    const btn = document.getElementById('btn-deactivate');
    btn.disabled = true;
    btn.textContent = 'Deactivating…';

    try {
        const url = '{{ url("v1/financial/members") }}/' + _deactivateUuid;
        const res = await fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });

        const json = await res.json();

        closeModal('modal-deactivate');

        if (json.status) {
            showToast('Member deactivated successfully.');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.message || 'Could not deactivate member.', 'error');
        }
    } catch {
        showToast('Connection error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Yes, Deactivate';
        _deactivateUuid = null;
    }
}
</script>

</body>
</html>
