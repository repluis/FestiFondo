<!DOCTYPE html>
<html lang="en" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $member['full_name'] }} — FestiFondo</title>
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
        .detail-row {
            display:grid; grid-template-columns:180px 1fr;
            padding:.75rem 0; border-bottom:1px solid var(--color-border);
            align-items:start; gap:1rem;
        }
        .detail-row:last-child { border-bottom:none; }
        .detail-label { font-size:.8rem; color:var(--color-text-muted); font-weight:600;
                        text-transform:uppercase; letter-spacing:.04em; padding-top:.1rem; }
        .detail-value { font-size:.9rem; color:var(--color-text-primary); word-break:break-word; }
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
            ['label'=>$member['full_name']],
        ]" />

        {{-- Page header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <x-ui.avatar :name="$member['full_name']" size="52px" />
                <div>
                    <h1 style="font-size:1.4rem;font-weight:700;">{{ $member['full_name'] }}</h1>
                    <div style="display:flex;align-items:center;gap:.5rem;margin-top:.3rem;">
                        <span style="font-size:.85rem;color:var(--color-accent);font-weight:600;">
                            {{ $member['identification'] }}
                        </span>
                        @if($member['status'])
                            <x-ui.badge variant="success">Active</x-ui.badge>
                        @else
                            <x-ui.badge variant="muted">Inactive</x-ui.badge>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('members.index') }}">
                    <x-ui.button variant="ghost">← Members</x-ui.button>
                </a>
                <a href="{{ route('members.edit', $member['uuid']) }}">
                    <x-ui.button variant="secondary">Edit</x-ui.button>
                </a>
                @if($member['status'])
                    <x-ui.button variant="danger"
                        onclick="confirmDeactivate('{{ $member['uuid'] }}', '{{ addslashes($member['full_name']) }}')">
                        Deactivate
                    </x-ui.button>
                @endif
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">

            {{-- Main info card --}}
            <x-ui.card title="Member Information">
                <div>
                    <div class="detail-row">
                        <span class="detail-label">Identification (CI)</span>
                        <span class="detail-value" style="color:var(--color-accent);font-weight:600;">
                            {{ $member['identification'] }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value">{{ $member['full_name'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">
                            @if($member['email'])
                                <a href="mailto:{{ $member['email'] }}"
                                   style="color:var(--color-primary-light);text-decoration:none;">
                                    {{ $member['email'] }}
                                </a>
                            @else
                                <span style="color:var(--color-text-muted);">—</span>
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">{{ $member['phone'] ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address</span>
                        <span class="detail-value" style="white-space:pre-line;">
                            {{ $member['address'] ?: '—' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Join Date</span>
                        <span class="detail-value">{{ $member['joined_at'] }}</span>
                    </div>
                    @if($member['notes'])
                    <div class="detail-row">
                        <span class="detail-label">Notes</span>
                        <span class="detail-value" style="color:var(--color-text-secondary);
                            white-space:pre-line;font-style:italic;">
                            {{ $member['notes'] }}
                        </span>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Sidebar info --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">

                <x-ui.card title="Status">
                    <div style="text-align:center;padding:.5rem 0;">
                        @if($member['status'])
                            <x-ui.badge variant="success" style="font-size:.9rem;padding:.4rem 1rem;">Active</x-ui.badge>
                            <p style="color:var(--color-text-muted);font-size:.8rem;margin-top:.75rem;">
                                This member is active and eligible for contributions.
                            </p>
                        @else
                            <x-ui.badge variant="error" style="font-size:.9rem;padding:.4rem 1rem;">Inactive</x-ui.badge>
                            <p style="color:var(--color-text-muted);font-size:.8rem;margin-top:.75rem;">
                                This member is inactive and excluded from billing.
                            </p>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card title="Record Info">
                    <div style="display:flex;flex-direction:column;gap:.6rem;">
                        <div>
                            <p style="font-size:.75rem;color:var(--color-text-muted);margin-bottom:.2rem;">Created</p>
                            <p style="font-size:.85rem;color:var(--color-text-secondary);">
                                {{ $member['created_at'] ? \Carbon\Carbon::parse($member['created_at'])->format('M d, Y H:i') : '—' }}
                            </p>
                        </div>
                        <div>
                            <p style="font-size:.75rem;color:var(--color-text-muted);margin-bottom:.2rem;">Last updated</p>
                            <p style="font-size:.85rem;color:var(--color-text-secondary);">
                                {{ $member['updated_at'] ? \Carbon\Carbon::parse($member['updated_at'])->format('M d, Y H:i') : '—' }}
                            </p>
                        </div>
                    </div>
                </x-ui.card>

            </div>
        </div>

    </x-layout.main>
</div>

<x-layout.footer />

{{-- Confirm Deactivate Modal --}}
<x-ui.modal id="modal-deactivate" title="Deactivate Member" size="sm">
    <p style="color:var(--color-text-secondary);">
        Are you sure you want to deactivate
        <strong id="deactivate-name" style="color:var(--color-text-primary);">{{ $member['full_name'] }}</strong>?
        This sets the member as inactive. No data will be deleted.
    </p>
    <x-slot:footer>
        <x-ui.button variant="ghost" onclick="closeModal('modal-deactivate')">Cancel</x-ui.button>
        <x-ui.button variant="danger" onclick="submitDeactivate()" id="btn-deactivate">
            Yes, Deactivate
        </x-ui.button>
    </x-slot:footer>
</x-ui.modal>

<div id="toast" style="position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:.75rem 1.25rem;
    border-radius:var(--radius-md);font-size:.875rem;font-weight:500;box-shadow:var(--shadow-lg);display:none;"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.style.background = type === 'success' ? 'var(--color-success-bg)' : 'var(--color-error-bg)';
    t.style.color      = type === 'success' ? 'var(--color-success)' : 'var(--color-error)';
    t.style.display    = 'block';
    t.textContent      = msg;
    setTimeout(() => { t.style.display = 'none'; }, 3500);
}

let _uuid = null;
function confirmDeactivate(uuid, name) {
    _uuid = uuid;
    document.getElementById('deactivate-name').textContent = name;
    openModal('modal-deactivate');
}

async function submitDeactivate() {
    if (!_uuid) return;
    const btn = document.getElementById('btn-deactivate');
    btn.disabled = true;
    btn.textContent = 'Deactivating…';

    try {
        const res  = await fetch('{{ url("v1/financial/members") }}/' + _uuid, {
            method:  'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const json = await res.json();
        closeModal('modal-deactivate');
        if (json.status) {
            showToast('Member deactivated successfully.');
            setTimeout(() => { window.location.href = '{{ route("members.index") }}'; }, 900);
        } else {
            showToast(json.message || 'Could not deactivate.', 'error');
        }
    } catch {
        showToast('Connection error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Yes, Deactivate';
    }
}
</script>

</body>
</html>
