<!DOCTYPE html>
<html lang="es" style="background: var(--color-bg-base);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Tu Amigo Secreto — FestiFondo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }

        :root {
            --color-bg-base:        #0A0F1E;
            --color-bg-surface:     #0F172A;
            --color-bg-elevated:    #1E293B;
            --color-bg-overlay:     #263347;
            --color-primary:        #2563EB;
            --color-primary-light:  #3B82F6;
            --color-primary-subtle: #1E3A5F;
            --color-accent:         #06B6D4;
            --color-text-primary:   #F8FAFC;
            --color-text-secondary: #94A3B8;
            --color-text-muted:     #64748B;
            --color-border:         #1E293B;
            --color-border-subtle:  #334155;
            --color-success:    #10B981;
            --color-warning:    #F59E0B;
            --color-error:      #EF4444;
            --shadow-lg: 0 8px 24px rgba(0,0,0,.6);
            --radius-md: 8px; --radius-lg: 12px; --radius-xl: 16px;
        }

        .stage {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem; position: relative; overflow: hidden;
        }
        .panel {
            width: 100%; max-width: 480px; text-align: center;
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border-subtle);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem 1.5rem;
            position: relative; z-index: 1;
        }
        .hello { color: var(--color-text-secondary); font-size: .95rem; margin-bottom: .35rem; }
        .title { font-size: 1.6rem; font-weight: 700; color: var(--color-text-primary); }
        .hint  { color: var(--color-text-muted); font-size: .8125rem; margin-top: 1.5rem; line-height: 1.5; }

        /* ── Regalo ─────────────────────────────────────────── */
        .gift { position: relative; width: 120px; height: 120px; margin: 2rem auto 1.5rem; }
        .gift-box, .gift-lid {
            position: absolute; left: 50%; transform: translateX(-50%);
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border-radius: 6px;
        }
        .gift-box { bottom: 0; width: 100px; height: 72px; }
        .gift-lid { bottom: 70px; width: 116px; height: 24px; transform-origin: left bottom; }
        .gift-box::after, .gift-lid::after {
            content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 16px;
            transform: translateX(-50%); background: #F8FAFC; opacity: .9;
        }
        .gift-bow {
            position: absolute; bottom: 92px; left: 50%; transform: translateX(-50%);
            font-size: 1.9rem; line-height: 1;
        }
        .gift.shaking { animation: shake .5s ease-in-out infinite; }
        .gift.open .gift-lid { animation: lid-open .6s forwards; }
        .gift.open .gift-bow { animation: lid-open .6s forwards; }

        @keyframes shake {
            0%, 100% { transform: rotate(0); }
            20% { transform: rotate(-8deg) scale(1.03); }
            40% { transform: rotate(8deg) scale(1.06); }
            60% { transform: rotate(-6deg) scale(1.03); }
            80% { transform: rotate(6deg); }
        }
        @keyframes lid-open {
            to { transform: translateX(-50%) translateY(-40px) rotate(-35deg); opacity: 0; }
        }

        .countdown {
            font-size: 2.5rem; font-weight: 800; color: var(--color-primary-light);
            height: 3rem; line-height: 3rem;
        }
        .countdown span { display: inline-block; animation: pop 1s ease-out; }
        @keyframes pop {
            0% { transform: scale(.3); opacity: 0; }
            40% { transform: scale(1.25); opacity: 1; }
            100% { transform: scale(1); opacity: .9; }
        }

        .name {
            display: none;
            font-size: 2.1rem; font-weight: 800; margin-top: .5rem;
            background: linear-gradient(90deg, var(--color-success), var(--color-accent));
            -webkit-background-clip: text; background-clip: text; color: transparent;
            animation: reveal .8s cubic-bezier(.2, 1.4, .4, 1) forwards;
            overflow-wrap: anywhere;
        }
        @keyframes reveal {
            from { transform: scale(.4) translateY(20px); opacity: 0; filter: blur(8px); }
            to   { transform: scale(1) translateY(0); opacity: 1; filter: blur(0); }
        }

        .confetti {
            position: absolute; top: -12px; width: 10px; height: 14px; border-radius: 2px;
            animation: fall linear forwards; z-index: 0;
        }
        @keyframes fall { to { transform: translateY(110vh) rotate(720deg); } }

        .icon-big { font-size: 3.5rem; margin: 1.5rem 0 1rem; }
        .msg { color: var(--color-text-secondary); font-size: .95rem; line-height: 1.6; }

        @media (prefers-reduced-motion: reduce) {
            .gift.shaking, .countdown span, .name, .confetti { animation: none !important; }
            .name { opacity: 1; }
        }
    </style>
</head>
<body style="background: var(--color-bg-base); color: var(--color-text-primary); min-height: 100vh; display: flex; flex-direction: column;">

<div class="stage" id="stage">
    <div class="panel">

        @if (!$preview)
            {{-- Enlace inválido --}}
            <div class="icon-big">🔍</div>
            <p class="title" style="font-size:1.3rem;">Enlace no válido</p>
            <p class="msg" style="margin-top:.5rem;">Este enlace no existe o pertenece a un sorteo que ya fue reemplazado.</p>

        @elseif ($preview['viewed'])
            {{-- Ya visto --}}
            <p class="hello">Hola, {{ $preview['giver_name'] }}</p>
            <div class="icon-big">🔒</div>
            <p class="title" style="font-size:1.3rem;">Ya revelaste a tu amigo secreto</p>
            <p class="msg" style="margin-top:.5rem;">
                Por seguridad solo se puede ver una vez. Si lo olvidaste, pídele al administrador que restaure tu enlace.
            </p>

        @else
            {{-- Revelación --}}
            <p class="hello">Hola, {{ $preview['giver_name'] }} 👋</p>
            <p class="title">Tu amigo secreto es…</p>

            <div class="gift shaking" id="gift">
                <div class="gift-bow">🎀</div>
                <div class="gift-lid"></div>
                <div class="gift-box"></div>
            </div>

            <div class="countdown" id="countdown"></div>
            <p class="name" id="name"></p>
            <p class="msg" id="error" style="display:none;"></p>

            <p class="hint" id="hint" style="display:none;">
                🤫 Guárdalo en secreto. Este enlace ya no volverá a mostrar el nombre.
            </p>
        @endif

    </div>
</div>

@if ($preview && !$preview['viewed'])
<script>
    (async function () {
        const $ = id => document.getElementById(id);
        const ANIMATION_MS = 3000;

        // Se pide el nombre al cargar (esto lo marca como visto) y en paralelo corre la animación de 3 s
        const request = fetch(@json(route('games.secret-santa.reveal', $token)), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        }).then(r => r.json()).catch(() => ({ success: false, error: 'Error de conexión. Recarga la página para intentar de nuevo.' }));

        const steps = ['3', '2', '1'];
        for (const s of steps) {
            $('countdown').innerHTML = `<span>${s}</span>`;
            await new Promise(r => setTimeout(r, ANIMATION_MS / steps.length));
        }

        const json = await request;
        $('countdown').style.display = 'none';

        if (!json.success) {
            $('gift').classList.remove('shaking');
            $('error').textContent = json.error || 'No se pudo revelar tu amigo secreto.';
            $('error').style.display = 'block';
            return;
        }

        $('gift').classList.remove('shaking');
        $('gift').classList.add('open');
        $('name').textContent = json.data.receiver_name;
        $('name').style.display = 'block';
        $('hint').style.display = 'block';
        confetti();
    })();

    function confetti() {
        const colors = ['#2563EB', '#06B6D4', '#10B981', '#F59E0B', '#F8FAFC', '#EF4444'];
        const stage = document.getElementById('stage');
        for (let i = 0; i < 90; i++) {
            const c = document.createElement('div');
            c.className = 'confetti';
            c.style.left = Math.random() * 100 + '%';
            c.style.background = colors[i % colors.length];
            c.style.animationDuration = (2.5 + Math.random() * 2.5) + 's';
            c.style.animationDelay = (Math.random() * .6) + 's';
            c.style.transform = `rotate(${Math.random() * 360}deg)`;
            stage.appendChild(c);
            setTimeout(() => c.remove(), 6000);
        }
    }
</script>
@endif

</body>
</html>
