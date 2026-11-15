<!DOCTYPE html>
<html lang="es" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Amigo Secreto — FestiFondo</title>
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
            --color-secondary:      #0EA5E9;
            --color-accent:         #06B6D4;
            --color-text-primary:   #F8FAFC;
            --color-text-secondary: #94A3B8;
            --color-text-muted:     #64748B;
            --color-text-disabled:  #475569;
            --color-border:         #1E293B;
            --color-border-subtle:  #334155;
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

        .filter-bar {
            display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;
            padding: 1rem 1.25rem;
            background: var(--color-bg-elevated);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
        }
        .filter-group { display: flex; flex-direction: column; gap: 0.35rem; }
        .filter-group label {
            font-size: 0.75rem; font-weight: 500; color: var(--color-text-muted);
            text-transform: uppercase; letter-spacing: 0.04em;
        }
        .filter-select {
            padding: 0.45rem 0.75rem;
            background: var(--color-bg-overlay);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-md);
            color: var(--color-text-primary);
            font-size: 0.875rem;
            min-width: 200px;
        }
        .filter-select:focus { outline: none; border-color: var(--color-primary-light); }

        .btn-primary {
            padding: 0.45rem 1.1rem; background: var(--color-primary); color: #fff;
            border: none; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 500;
            cursor: pointer; transition: background 0.15s;
        }
        .btn-primary:hover:not(:disabled) { background: var(--color-primary-hover); }
        .btn-primary:disabled { opacity: .5; cursor: not-allowed; }

        .btn-ghost {
            padding: 0.45rem 0.9rem; background: transparent; color: var(--color-text-secondary);
            border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md);
            font-size: 0.875rem; font-weight: 500; cursor: pointer;
            transition: color 0.15s, border-color 0.15s;
        }
        .btn-ghost:hover { color: var(--color-text-primary); border-color: var(--color-text-secondary); }
        .btn-sm { padding: 0.3rem 0.7rem; font-size: 0.8rem; }

        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }

        .member-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: .5rem; }
        .member-item {
            display: flex; align-items: center; gap: .55rem;
            padding: .55rem .75rem;
            background: var(--color-bg-elevated);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-md);
            font-size: .875rem; color: var(--color-text-secondary);
            cursor: pointer; user-select: none;
            transition: border-color .15s, background .15s, color .15s;
        }
        .member-item:hover { border-color: var(--color-text-muted); }
        .member-item.checked {
            border-color: var(--color-primary-light);
            background: var(--color-primary-subtle);
            color: var(--color-text-primary);
        }
        .member-item input { accent-color: var(--color-primary-light); }

        .section-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .9rem; flex-wrap: wrap; }
        .muted { color: var(--color-text-muted); font-size: .8125rem; }

        .exclusion-form { display: flex; flex-wrap: wrap; gap: .5rem; align-items: flex-end; margin-bottom: 1rem; }
        .exclusion-form .filter-select { min-width: 150px; flex: 1; }
        .check-inline { display: flex; align-items: center; gap: .4rem; font-size: .8125rem; color: var(--color-text-secondary); width: 100%; }
        .check-inline input { accent-color: var(--color-primary-light); }

        .chip-list { display: flex; flex-direction: column; gap: .4rem; }
        .chip {
            display: flex; align-items: center; justify-content: space-between; gap: .5rem;
            padding: .5rem .75rem;
            background: var(--color-bg-elevated);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-md);
            font-size: .85rem; color: var(--color-text-secondary);
        }
        .chip strong { color: var(--color-text-primary); font-weight: 600; }
        .chip .no { color: var(--color-error); font-weight: 600; margin: 0 .3rem; }
        .chip button {
            background: transparent; border: none; color: var(--color-text-muted);
            cursor: pointer; font-size: 1rem; line-height: 1; padding: 0 .25rem;
        }
        .chip button:hover { color: var(--color-error); }

        .empty { padding: 1.25rem; text-align: center; color: var(--color-text-muted); font-size: .875rem; }

        .result-row {
            display: grid; grid-template-columns: 1fr auto 1fr auto; align-items: center; gap: .75rem;
            padding: .75rem 1rem;
            border-bottom: 1px solid var(--color-border);
            font-size: .9rem;
        }
        .result-row:last-child { border-bottom: none; }
        .result-giver { color: var(--color-text-primary); font-weight: 600; }
        .result-arrow { color: var(--color-text-muted); }
        .result-actions { display: flex; gap: .4rem; justify-content: flex-end; flex-wrap: wrap; }
        .result-row.admin { grid-template-columns: 1fr auto auto; }
        .result-row.guest { grid-template-columns: 1fr auto; }
        .btn-ghost.copied { color: var(--color-success); border-color: var(--color-success); }

        .badge {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .2rem .6rem; border-radius: var(--radius-full);
            font-size: .75rem; font-weight: 600; white-space: nowrap;
        }
        .badge.viewed  { background: var(--color-success-bg); color: var(--color-success); }
        .badge.pending { background: var(--color-warning-bg); color: var(--color-warning); }

        .history-row.current { background: var(--color-primary-subtle); box-shadow: inset 3px 0 0 var(--color-primary-light); }

        .alert {
            border-radius: var(--radius-md); padding: .875rem 1rem; margin-bottom: 1.25rem;
            font-size: .875rem; display: none;
        }
        .alert.error { background: var(--color-error-bg); border: 1px solid var(--color-error); color: #FCA5A5; }
        .alert.show { display: block; }

        /* ── Overlay de sorteo ─────────────────────────────── */
        .draw-overlay {
            position: fixed; inset: 0; z-index: 1000;
            background: rgba(10, 15, 30, .92);
            backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity .3s;
        }
        .draw-overlay.show { display: flex; }
        .draw-overlay.visible { opacity: 1; }
        .draw-stage { position: relative; width: min(92vw, 640px); aspect-ratio: 1; }
        .draw-stage svg { width: 100%; height: 100%; overflow: visible; }
        .draw-center {
            position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
            text-align: center; pointer-events: none;
        }
        .draw-center-icon { font-size: 2.75rem; animation: pulse 1s ease-in-out infinite; }
        .draw-center-text { color: var(--color-text-secondary); font-size: .9rem; margin: .35rem 0 .6rem; font-weight: 500; }
        .draw-progress { width: 120px; height: 4px; background: var(--color-bg-overlay); border-radius: 99px; overflow: hidden; margin: 0 auto; }
        .draw-progress div { height: 100%; width: 0; background: linear-gradient(90deg, var(--color-primary), var(--color-accent)); }
        @keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.15); } }

        .draw-name {
            font-size: 15px; font-weight: 600; fill: var(--color-text-secondary);
            transition: fill .2s;
        }
        .draw-name.hot { fill: var(--color-text-primary); }
        .draw-dot { fill: var(--color-primary-light); }
        .draw-line {
            fill: none; stroke-width: 2; stroke-linecap: round;
            animation: line-life 0.9s ease-out forwards;
        }
        @keyframes line-life {
            0%   { stroke-dashoffset: var(--len); opacity: .95; }
            55%  { stroke-dashoffset: 0; opacity: .95; }
            100% { stroke-dashoffset: 0; opacity: 0; }
        }

        @media (max-width: 640px) {
            .draw-name { font-size: 20px; }
            .result-row, .result-row.admin, .result-row.guest { grid-template-columns: 1fr; gap: .4rem; }
            .result-actions { justify-content: flex-start; }
        }
    </style>
</head>
<body style="background: var(--color-bg-base); color: var(--color-text-primary); min-height: 100vh; display: flex; flex-direction: column;">

<x-layout.app-header />

<div style="display: flex; flex: 1;">

    <x-layout.app-sidebar />

    <x-layout.main>

        <x-ui.breadcrumb :items="[
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Juegos'],
            ['label' => 'Amigo Secreto'],
        ]" />

        <div style="margin: 1.5rem 0;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--color-text-primary);">🎁 Amigo Secreto</h1>
            <p style="color: var(--color-text-muted); font-size: .875rem; margin-top: .2rem;">
                @auth
                    Selecciona una campaña, elige a los participantes, agrega exclusiones y realiza el sorteo
                @else
                    Juego de intercambio de regalos entre los integrantes de una campaña
                @endauth
            </p>
        </div>

        @guest
            {{-- ── Invitados: solo información ───────────────────────────── --}}
            <x-ui.card>
                <div style="text-align:center; padding:1.5rem .5rem;">
                    <div style="font-size:3rem; margin-bottom:.75rem;">🎁</div>
                    <p style="font-size:1.1rem; font-weight:600; color:var(--color-text-primary);">¿Ya te llegó tu enlace?</p>
                    <p class="muted" style="max-width:440px; margin:.5rem auto 0; line-height:1.6;">
                        El administrador realiza el sorteo y le envía a cada participante un enlace personal.
                        Ábrelo para descubrir a tu amigo secreto. Recuerda: solo se puede ver una vez.
                    </p>
                    <a href="{{ route('auth.login') }}" class="btn-ghost" style="display:inline-block; margin-top:1.25rem; text-decoration:none;">
                        Soy el administrador · Iniciar sesión
                    </a>
                </div>
            </x-ui.card>
        @else

        {{-- ── 1. Campaña ─────────────────────────────────────────────────── --}}
        <div class="filter-bar">
            <div class="filter-group">
                <label for="campaign">Campaña</label>
                <select id="campaign" class="filter-select">
                    <option value="">Selecciona una campaña…</option>
                    @foreach ($campaigns as $c)
                        <option value="{{ $c['uuid'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <span id="loading" class="muted" style="display:none; padding-bottom:.5rem;">Cargando integrantes…</span>
        </div>

        <div id="alert" class="alert error"></div>

        <div id="setup" style="display:none;">
            <div class="grid-2">

                {{-- ── 2. Participantes ────────────────────────────────────── --}}
                <x-ui.card title="Participantes">
                    <div class="section-head">
                        <span class="muted"><span id="selected-count">0</span> seleccionados</span>
                        <div style="display:flex; gap:.4rem;">
                            <button type="button" class="btn-ghost btn-sm" onclick="selectAll(true)">Todos</button>
                            <button type="button" class="btn-ghost btn-sm" onclick="selectAll(false)">Ninguno</button>
                        </div>
                    </div>
                    <div id="member-list" class="member-list"></div>
                </x-ui.card>

                {{-- ── 3. Exclusiones ──────────────────────────────────────── --}}
                <x-ui.card title="Exclusiones">
                    <p class="muted" style="margin-bottom:.9rem;">
                        Indica a quién <strong>no</strong> le puede tocar cada persona (por ejemplo, porque ya le tocó el año pasado).
                    </p>
                    <div class="exclusion-form">
                        <div class="filter-group" style="flex:1;">
                            <label for="ex-giver">A esta persona</label>
                            <select id="ex-giver" class="filter-select"></select>
                        </div>
                        <div class="filter-group" style="flex:1;">
                            <label for="ex-receiver">No le puede tocar</label>
                            <select id="ex-receiver" class="filter-select"></select>
                        </div>
                        <button type="button" class="btn-primary" onclick="addExclusion()">Agregar</button>
                        <label class="check-inline">
                            <input type="checkbox" id="ex-both"> Aplicar en ambos sentidos
                        </label>
                    </div>
                    <div id="exclusion-list" class="chip-list"></div>
                </x-ui.card>

            </div>

            <div style="display:flex; justify-content:flex-end; margin-bottom:1.5rem;">
                <button type="button" id="draw-btn" class="btn-primary" style="padding:.6rem 1.5rem; font-size:.95rem;" onclick="draw()">
                    🎲 Realizar sorteo
                </button>
            </div>
        </div>

        {{-- ── 4. Resultado ───────────────────────────────────────────────── --}}
        <div id="result" style="display:none;">
            <x-ui.card title="Resultado del sorteo" padding="0">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:.5rem; padding:.75rem 1rem; border-bottom:1px solid var(--color-border); flex-wrap:wrap;">
                    <span class="muted">
                        <span id="draw-info"></span>
                        <span id="draw-date"></span>
                    </span>
                    <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
                        <button type="button" class="btn-ghost btn-sm" onclick="openDraw(draw_.uuid)">↻ Actualizar estado</button>
                        <button type="button" class="btn-ghost btn-sm" id="redraw-btn" onclick="draw()">🎲 Volver a sortear</button>
                    </div>
                </div>
                <div id="result-list"></div>
            </x-ui.card>
        </div>

        {{-- ── 5. Historial ───────────────────────────────────────────────── --}}
        <div style="margin-top:1.5rem;">
            <x-ui.card title="Historial de sorteos" padding="0">
                <div id="history-list"><div class="empty">Cargando…</div></div>
            </x-ui.card>
        </div>

        @endguest

    </x-layout.main>
</div>

@auth
{{-- ── Animación del sorteo (5 s) ─────────────────────────────────────── --}}
<div id="draw-overlay" class="draw-overlay">
    <div class="draw-stage">
        <svg id="draw-svg" viewBox="-300 -300 600 600" preserveAspectRatio="xMidYMid meet"></svg>
        <div class="draw-center">
            <div class="draw-center-icon">🎁</div>
            <div class="draw-center-text">Sorteando…</div>
            <div class="draw-progress"><div id="draw-progress-bar"></div></div>
        </div>
    </div>
</div>

@endauth

<x-layout.footer />

@auth
<script>
    const CSRF        = document.querySelector('meta[name="csrf-token"]').content;
    const MEMBERS_URL = @json(url('/v1/games/secret-santa/campaigns')) + '/';
    const DRAW_URL    = @json(route('games.secret-santa.draw'));
    const BASE_URL    = @json(url('/v1/games/secret-santa'));
    const IS_ADMIN    = @json(auth()->check());

    let members    = [];   // [{oid, name}]
    let selected   = new Set();
    let exclusions = [];   // [{giver, receiver}]
    let draw_      = null; // {uuid, created_at, assignments: [{giver_name, token, url, viewed}]}

    const $ = id => document.getElementById(id);
    const nameOf = oid => (members.find(m => m.oid === oid) || {}).name || '#' + oid;
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function showError(msg) { const a = $('alert'); a.textContent = msg; a.classList.add('show'); }
    function clearError()   { $('alert').classList.remove('show'); }

    $('campaign').addEventListener('change', async e => {
        draw_ = null;
        renderResult();
        if (await loadCampaign(e.target.value)) loadLatestDraw();
    });

    /** Carga los integrantes de una campaña en el panel de configuración. */
    async function loadCampaign(uuid) {
        clearError();
        $('setup').style.display = 'none';
        members = []; selected = new Set(); exclusions = [];
        if (!uuid) return false;

        $('loading').style.display = 'inline';
        try {
            const res  = await fetch(MEMBERS_URL + encodeURIComponent(uuid) + '/members', { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            members  = json.data || [];
            selected = new Set(members.map(m => m.oid));
            if (members.length < 2) {
                showError('Esta campaña tiene menos de 2 integrantes activos.');
                return false;
            }
            // Exclusiones guardadas de la campaña
            const exRes  = await fetch(exclusionsUrl(uuid), { headers: { 'Accept': 'application/json' } });
            const exJson = await exRes.json();
            exclusions = exJson.data || [];
            renderAll();
            $('setup').style.display = 'block';
            return true;
        } catch (err) {
            showError('No se pudieron cargar los integrantes de la campaña.');
            return false;
        } finally {
            $('loading').style.display = 'none';
        }
    }

    function renderAll() { renderMembers(); renderExclusionSelects(); renderExclusions(); }

    function renderMembers() {
        $('member-list').innerHTML = members.map(m => `
            <label class="member-item ${selected.has(m.oid) ? 'checked' : ''}">
                <input type="checkbox" ${selected.has(m.oid) ? 'checked' : ''} onchange="toggleMember(${m.oid}, this.checked)">
                <span>${esc(m.name)}</span>
            </label>`).join('');
        $('selected-count').textContent = selected.size;
    }

    function toggleMember(oid, on) {
        on ? selected.add(oid) : selected.delete(oid);
        renderAll();
    }

    function selectAll(on) {
        selected = on ? new Set(members.map(m => m.oid)) : new Set();
        renderAll();
    }

    const exclusionsUrl = uuid => `${BASE_URL}/campaigns/${encodeURIComponent(uuid)}/exclusions`;
    // Solo cuentan para el sorteo las exclusiones entre personas que participan
    const activeExclusions = () => exclusions.filter(e => selected.has(e.giver) && selected.has(e.receiver));

    async function saveExclusion(method, body) {
        clearError();
        try {
            const res = await fetch(exclusionsUrl($('campaign').value), {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(body),
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.error || json.message);
            exclusions = json.data;
            renderExclusions();
        } catch (err) {
            showError(err.message || 'No se pudo guardar la exclusión.');
        }
    }

    function renderExclusionSelects() {
        const opts = members.filter(m => selected.has(m.oid))
            .map(m => `<option value="${m.oid}">${esc(m.name)}</option>`).join('');
        const g = $('ex-giver').value, r = $('ex-receiver').value;
        $('ex-giver').innerHTML    = opts;
        $('ex-receiver').innerHTML = opts;
        if (g) $('ex-giver').value = g;
        if (r) $('ex-receiver').value = r;
        if ($('ex-giver').value === $('ex-receiver').value && $('ex-receiver').options.length > 1) {
            $('ex-receiver').selectedIndex = $('ex-giver').selectedIndex === 0 ? 1 : 0;
        }
    }

    function addExclusion() {
        clearError();
        const giver    = parseInt($('ex-giver').value, 10);
        const receiver = parseInt($('ex-receiver').value, 10);
        if (!giver || !receiver) return;
        if (giver === receiver) { showError('Elige dos personas distintas.'); return; }

        saveExclusion('POST', { giver, receiver, both: $('ex-both').checked });
    }

    function removeExclusion(i) {
        const e = exclusions[i];
        saveExclusion('DELETE', { giver: e.giver, receiver: e.receiver });
    }

    function renderExclusions() {
        $('exclusion-list').innerHTML = exclusions.length
            ? exclusions.map((e, i) => {
                const inactive = !(selected.has(e.giver) && selected.has(e.receiver));
                return `
                <div class="chip" style="${inactive ? 'opacity:.5;' : ''}" title="${inactive ? 'No aplica: alguno no participa en este sorteo' : ''}">
                    <span>A <strong>${esc(nameOf(e.giver))}</strong><span class="no">no</span>le toca <strong>${esc(nameOf(e.receiver))}</strong></span>
                    <button type="button" title="Quitar" onclick="removeExclusion(${i})">✕</button>
                </div>`;
            }).join('')
            : '<div class="empty">Sin exclusiones</div>';
    }

    async function draw() {
        clearError();
        if (selected.size < 2) { showError('Selecciona al menos 2 participantes.'); return; }
        if (!$('campaign').value) { showError('Selecciona una campaña.'); return; }
        if (draw_ && draw_.active && !confirm('Ya existe un sorteo para esta campaña. Si sorteas de nuevo, se generan enlaces nuevos y los anteriores dejan de funcionar. ¿Continuar?')) return;

        const btn = $('draw-btn');
        btn.disabled = true;
        btn.textContent = 'Sorteando…';
        const animation = playDrawAnimation(members.filter(m => selected.has(m.oid)).map(m => m.name), 5000);
        try {
            const resPromise = fetch(DRAW_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    campaign_uuid: $('campaign').value,
                    participants:  [...selected],
                    exclusions:    activeExclusions(),
                }),
            });
            // Si el servidor responde con error, no tiene sentido esperar los 5 s
            const res  = await resPromise;
            const json = await res.json();
            if (res.ok && json.success) await animation.done;
            animation.stop();
            if (!res.ok || !json.success) {
                const msg = json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : json.message);
                showError(msg || 'No se pudo realizar el sorteo.');
                return;
            }
            draw_ = json.data;
            renderResult();
            loadHistory();
            $('result').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (err) {
            animation.stop();
            showError('Error de conexión al realizar el sorteo.');
        } finally {
            btn.disabled = false;
            btn.textContent = '🎲 Realizar sorteo';
        }
    }

    /**
     * Animación puramente visual: los nombres se ubican en círculo y se cruzan
     * líneas al azar entre ellos. No refleja el resultado real del sorteo,
     * que se calcula en el servidor.
     */
    function playDrawAnimation(names, duration) {
        const overlay = $('draw-overlay');
        const svg     = $('draw-svg');
        const NS      = 'http://www.w3.org/2000/svg';
        const colors  = ['#3B82F6', '#06B6D4', '#10B981', '#F59E0B', '#0EA5E9'];
        const R       = 200;
        const rand    = n => Math.floor(Math.random() * n);

        svg.innerHTML = '';
        const linesG = document.createElementNS(NS, 'g');
        const namesG = document.createElementNS(NS, 'g');
        svg.append(linesG, namesG);

        const nodes = names.map((name, i) => {
            const angle = (i / names.length) * Math.PI * 2 - Math.PI / 2;
            const x = Math.cos(angle) * R, y = Math.sin(angle) * R;
            const dot = document.createElementNS(NS, 'circle');
            dot.setAttribute('cx', x); dot.setAttribute('cy', y); dot.setAttribute('r', 5);
            dot.setAttribute('class', 'draw-dot');
            const label = document.createElementNS(NS, 'text');
            const lx = Math.cos(angle) * (R + 16), ly = Math.sin(angle) * (R + 16);
            label.setAttribute('x', lx); label.setAttribute('y', ly);
            label.setAttribute('dominant-baseline', 'middle');
            label.setAttribute('text-anchor', Math.abs(Math.cos(angle)) < .2 ? 'middle' : (Math.cos(angle) > 0 ? 'start' : 'end'));
            label.setAttribute('class', 'draw-name');
            label.textContent = name.split(' ')[0];
            namesG.append(dot, label);
            return { x, y, label };
        });

        overlay.classList.add('show');
        requestAnimationFrame(() => overlay.classList.add('visible'));

        const start = performance.now();
        let timer = null, raf = null, stopped = false;

        const spark = () => {
            if (nodes.length < 2) return;
            const a = rand(nodes.length);
            let b = rand(nodes.length - 1); if (b >= a) b++;
            const A = nodes[a], B = nodes[b];
            // Curva que pasa cerca del centro para que las líneas se "entrelacen"
            const cx = (A.x + B.x) * .15 + (Math.random() - .5) * 80;
            const cy = (A.y + B.y) * .15 + (Math.random() - .5) * 80;
            const path = document.createElementNS(NS, 'path');
            path.setAttribute('d', `M${A.x},${A.y} Q${cx},${cy} ${B.x},${B.y}`);
            path.setAttribute('class', 'draw-line');
            path.setAttribute('stroke', colors[rand(colors.length)]);
            linesG.appendChild(path);
            const len = path.getTotalLength();
            path.style.strokeDasharray = len;
            path.style.setProperty('--len', len);
            A.label.classList.add('hot'); B.label.classList.add('hot');
            setTimeout(() => { path.remove(); A.label.classList.remove('hot'); B.label.classList.remove('hot'); }, 900);
        };

        const tick = () => {
            if (stopped) return;
            const t = Math.min((performance.now() - start) / duration, 1);
            $('draw-progress-bar').style.width = (t * 100) + '%';
            // Más rápido al principio, se calma al final
            const count = t < .8 ? 3 : 1;
            for (let i = 0; i < count; i++) spark();
            timer = setTimeout(tick, 60 + t * 140);
        };
        tick();

        // Rotación lenta del círculo
        const spin = now => {
            if (stopped) return;
            const deg = ((now - start) / duration) * 90;
            svg.style.transform = `rotate(${deg}deg)`;
            namesG.querySelectorAll('text').forEach(t => t.setAttribute('transform', `rotate(${-deg} ${t.getAttribute('x')} ${t.getAttribute('y')})`));
            raf = requestAnimationFrame(spin);
        };
        raf = requestAnimationFrame(spin);

        const done = new Promise(r => setTimeout(r, duration));

        return {
            done,
            stop() {
                stopped = true;
                clearTimeout(timer); cancelAnimationFrame(raf);
                overlay.classList.remove('visible');
                setTimeout(() => { overlay.classList.remove('show'); svg.innerHTML = ''; svg.style.transform = ''; }, 300);
            },
        };
    }

    async function loadLatestDraw() {
        const uuid = $('campaign').value;
        if (!uuid) return;
        try {
            const res  = await fetch(`${BASE_URL}/campaigns/${encodeURIComponent(uuid)}/latest-draw`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            draw_ = json.data;
            renderResult();
        } catch (err) {
            showError('No se pudo cargar el último sorteo.');
        }
    }

    const fmtDate = s => s ? new Date(s.replace(' ', 'T')).toLocaleString('es') : '';

    function renderResult() {
        if (!draw_) { $('result').style.display = 'none'; return; }
        $('result').style.display = 'block';
        $('draw-info').innerHTML = draw_.active
            ? 'Envía a cada persona su enlace. Al abrirlo verá a su amigo secreto <strong>una sola vez</strong>.'
            : '<span class="badge pending">Reemplazado</span> Este sorteo fue reemplazado por uno más nuevo; sus enlaces ya no funcionan.';
        $('draw-date').textContent = `· ${draw_.campaign_name || ''} · ${fmtDate(draw_.created_at)}`;
        $('redraw-btn').style.display = draw_.active ? '' : 'none';

        $('result-list').innerHTML = draw_.assignments.map((a, i) => `
            <div class="result-row admin">
                <span class="result-giver">${esc(a.giver_name)}</span>
                <span class="badge ${a.viewed ? 'viewed' : 'pending'}">${a.viewed ? '✓ Visto' : '● Pendiente'}</span>
                <div class="result-actions">
                    ${draw_.active ? `<button type="button" class="btn-ghost btn-sm" id="copy-${i}" onclick="copyLink(${i})">🔗 Copiar enlace</button>` : ''}
                    ${draw_.active && a.viewed ? `<button type="button" class="btn-ghost btn-sm" onclick="resetView(${i})">↺ Restaurar</button>` : ''}
                </div>
            </div>`).join('');
        renderHistoryHighlight();
    }

    // ── Historial ──────────────────────────────────────────────────────────
    let history_ = [];

    async function loadHistory() {
        try {
            const res  = await fetch(`${BASE_URL}/draws`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            history_ = json.data || [];
            renderHistory();
            return history_;
        } catch (err) {
            $('history-list').innerHTML = '<div class="empty">No se pudo cargar el historial.</div>';
            return [];
        }
    }

    function renderHistory() {
        if (!history_.length) {
            $('history-list').innerHTML = '<div class="empty">Aún no se ha realizado ningún sorteo.</div>';
            return;
        }
        $('history-list').innerHTML = history_.map(d => `
            <div class="result-row admin history-row" data-uuid="${d.uuid}">
                <div>
                    <div class="result-giver">${esc(d.campaign_name)}</div>
                    <div class="muted">${fmtDate(d.created_at)} · ${d.participants} participantes · ${d.viewed}/${d.participants} vistos</div>
                </div>
                <span class="badge ${d.active ? 'viewed' : 'pending'}">${d.active ? '● Activo' : 'Reemplazado'}</span>
                <div class="result-actions">
                    <button type="button" class="btn-ghost btn-sm" onclick="openDraw('${d.uuid}', true)">Ver</button>
                </div>
            </div>`).join('');
        renderHistoryHighlight();
    }

    function renderHistoryHighlight() {
        document.querySelectorAll('.history-row').forEach(r =>
            r.classList.toggle('current', !!draw_ && r.dataset.uuid === draw_.uuid));
    }

    /** Abre un sorteo del historial y carga su campaña para poder volver a sortear. */
    async function openDraw(uuid, scroll = false) {
        clearError();
        try {
            const res  = await fetch(`${BASE_URL}/draws/${uuid}`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.error);
            draw_ = json.data;
            if ($('campaign').value !== draw_.campaign_uuid) {
                $('campaign').value = draw_.campaign_uuid;
                await loadCampaign(draw_.campaign_uuid);
            }
            renderResult();
            loadHistory();
            if (scroll) $('result').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (err) {
            showError(err.message || 'No se pudo abrir el sorteo.');
        }
    }

    // Al cargar la página: mostrar el historial y abrir el sorteo activo más reciente
    (async () => {
        const list = await loadHistory();
        const latest = list.find(d => d.active);
        if (latest) openDraw(latest.uuid);
    })();

    async function copyLink(i) {
        const url = draw_.assignments[i].url;
        try {
            await navigator.clipboard.writeText(url);
        } catch (err) {
            // Fallback para http sin permisos de portapapeles
            const t = document.createElement('textarea');
            t.value = url; document.body.appendChild(t); t.select();
            document.execCommand('copy'); t.remove();
        }
        const btn = $('copy-' + i);
        btn.textContent = '✓ Copiado';
        btn.classList.add('copied');
        setTimeout(() => { btn.textContent = '🔗 Copiar enlace'; btn.classList.remove('copied'); }, 1800);
    }

    async function resetView(i) {
        const a = draw_.assignments[i];
        if (!confirm(`¿Permitir que ${a.giver_name} vuelva a ver su amigo secreto?`)) return;
        try {
            const res = await fetch(`${BASE_URL}/r/${a.token}/reset`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            if (!res.ok) throw new Error();
            a.viewed = false;
            renderResult();
            loadHistory();
        } catch (err) {
            showError('No se pudo restaurar el enlace.');
        }
    }
</script>
@endauth

</body>
</html>
