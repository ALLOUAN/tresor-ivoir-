<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ __('Vérifiez votre e-mail') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    @php
        $vEmail = auth()->user()->email;
        $vDomain = strtolower((string) \Illuminate\Support\Str::after($vEmail, '@'));
        // Raccourci vers la messagerie de la personne (fournisseurs courants seulement).
        $vInbox = match (true) {
            in_array($vDomain, ['gmail.com', 'googlemail.com'], true) => ['Gmail', 'https://mail.google.com/mail/u/0/#inbox'],
            (bool) preg_match('/^(outlook|hotmail|live|msn)\./', $vDomain) => ['Outlook', 'https://outlook.live.com/mail/0/'],
            (bool) preg_match('/^yahoo\./', $vDomain) => ['Yahoo Mail', 'https://mail.yahoo.com/'],
            in_array($vDomain, ['icloud.com', 'me.com'], true) => ['iCloud Mail', 'https://www.icloud.com/mail'],
            default => null,
        };
    @endphp
    <style>
        /* Vérification de l'e-mail : dernière étape. Code en 6 cases (un seul vrai champ, collage et
           remplissage automatique compatibles), validité affichée, renvoi temporisé. Aplats. */
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #F3F8F5; color: #0E3B2A; font-family: 'Montserrat', sans-serif; }
        .ve-stage { position: relative; overflow: hidden; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 28px 16px 40px; }
        .ve-stage > .ivm .ivm-g { opacity: .22 !important; }
        .ve-brand { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 12px; margin-bottom: 20px; padding: 8px 16px 8px 8px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; text-decoration: none; color: #0E3B2A; }
        .ve-brand img { display: block; }
        .ve-brand b { font-family: 'Montserrat', serif; font-size: 17px; }
        .ve-card { position: relative; z-index: 1; width: 100%; max-width: 540px; overflow: hidden; border-radius: 28px; background: #fff; border: 1px solid #D8E9E0; }

        .ve-head { position: relative; overflow: hidden; padding: 26px 30px 30px; background: #00874F; color: #fff; text-align: center; }
        .ve-head .ivm-g { opacity: .1 !important; }
        .ve-head .ivm-g > * { stroke: #F7C396 !important; }
        .ve-head .ivm-band { height: 6px; }
        .ve-head > :not(.ivm):not(.ivm-band) { position: relative; z-index: 1; }
        .ve-step { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #F7C396; }
        .ve-icon { width: 64px; height: 64px; margin: 16px auto 0; border-radius: 20px; display: flex; align-items: center; justify-content: center; background: #fff; color: #EB6E00; }
        .ve-icon .ico { width: 32px; height: 32px; font-size: 32px; }
        .ve-icon .ico svg { width: 32px; height: 32px; }
        .ve-title { margin: 14px 0 0; font-family: 'Montserrat', serif; font-size: clamp(25px, 3vw, 30px); font-weight: 700; line-height: 1.15; color: #fff; }
        .ve-sub { margin: 8px auto 0; max-width: 40ch; font-size: 15px; line-height: 1.55; color: #D8E9E0; }
        .ve-sub b { color: #fff; overflow-wrap: anywhere; white-space: nowrap; }
        @media (max-width: 520px) { .ve-sub b { white-space: normal; } }

        .ve-body { padding: 24px 30px 26px; }
        .ve-alert { display: flex; align-items: flex-start; gap: 10px; margin: 0 0 18px; padding: 12px 14px; border-radius: 14px; font-size: 14px; line-height: 1.5; }
        .ve-alert .ico { flex: none; width: 18px; height: 18px; margin-top: 1px; }
        .ve-alert.is-ok { background: #F3F8F5; border: 1px solid #D8E9E0; color: #00874F; }
        .ve-alert.is-warn { background: #F3F8F5; border: 1px solid #D8E9E0; color: #B85600; }

        .ve-label { display: block; margin: 0 0 10px; text-align: center; font-size: 14px; font-weight: 700; color: #0E3B2A; }
        .ve-otp { position: relative; display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; max-width: 400px; margin: 0 auto; }
        .ve-otp span { height: 62px; border-radius: 16px; border: 1.5px solid #D8E9E0; background: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; color: #0E3B2A; font-variant-numeric: tabular-nums; transition: border-color .15s ease, background .15s ease, box-shadow .15s ease; }
        .ve-otp span.is-filled { background: #fff; border-color: #F7C396; }
        .ve-otp span.is-active { border-color: #EB6E00; background: #fff; box-shadow: 0 0 0 4px rgba(235,110,0,.12); }
        .ve-otp span.is-active:empty::after { content: ''; width: 2px; height: 26px; background: #EB6E00; animation: ve-blink 1s steps(1) infinite; }
        @keyframes ve-blink { 50% { opacity: 0; } }
        .ve-otp.is-error span { border-color: #c0362c; background: #fff; }
        /* Le vrai champ couvre les cases : clavier, collage et code proposé par le téléphone fonctionnent. */
        html .ve-otp input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; border: 0 !important; background: transparent !important; color: transparent !important; caret-color: transparent; font-size: 16px; letter-spacing: 1em; cursor: text; }
        .ve-err { margin: 10px 0 0; text-align: center; font-size: 13.5px; font-weight: 600; color: #b42318; }

        .ve-meta { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px 16px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 13px; color: #557466; }
        .ve-meta li { display: inline-flex; align-items: center; gap: 6px; }
        .ve-meta li > .ico { width: 15px; height: 15px; margin: 0; padding: 0; border: 0; border-radius: 0; background: none; color: #00874F; }

        .ve-btn { display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 52px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; font: inherit; font-size: 15.5px; font-weight: 800; text-decoration: none; cursor: pointer; transition: background .15s ease; }
        .ve-btn:hover { background: #B85600; }
        .ve-btn .ico { width: 18px; height: 18px; }
        .ve-btn[disabled] { opacity: .55; cursor: default; }
        .ve-btn.is-ghost { background: #fff; color: #0E3B2A; border: 1.5px solid #D8E9E0; }
        .ve-btn.is-ghost:hover:not([disabled]) { border-color: #EB6E00; color: #B85600; background: #fff; }
        .ve-submit { margin-top: 20px; }
        .ve-inbox { margin-top: 10px; }

        .ve-help { margin: 22px 0 0; padding: 16px; border-radius: 18px; background: #FFFFFF; border: 1px solid #D8E9E0; }
        .ve-help p { margin: 0 0 10px; font-size: 13.5px; font-weight: 700; color: #0E3B2A; }
        .ve-help ul { display: grid; gap: 6px; margin: 0 0 14px; padding: 0; list-style: none; font-size: 13.5px; line-height: 1.45; color: #24503D; }
        .ve-help li { display: flex; gap: 8px; }
        .ve-help li::before { content: ''; flex: none; width: 6px; height: 6px; margin-top: 7px; border-radius: 2px; transform: rotate(45deg); background: #EB6E00; }
        .ve-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px; margin: 18px 0 0; font-size: 13.5px; color: #557466; }
        .ve-link { border: 0; padding: 0; background: none; font: inherit; font-weight: 700; color: #B85600; cursor: pointer; text-decoration: none; }
        .ve-link:hover { text-decoration: underline; text-underline-offset: 3px; }
        .ve-copy { position: relative; z-index: 1; margin: 18px 0 0; font-size: 12.5px; color: #557466; }
        .ve-card a:focus-visible, .ve-card button:focus-visible, .ve-brand:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

        @media (max-width: 520px) {
            .ve-stage { justify-content: flex-start; padding: 16px 12px 28px; }
            .ve-brand { margin-bottom: 14px; }
            .ve-card { border-radius: 22px; }
            .ve-head { padding: 20px 18px 24px; }
            .ve-icon { width: 54px; height: 54px; margin-top: 12px; border-radius: 16px; }
            .ve-title { font-size: 23px; }
            .ve-sub { font-size: 14px; }
            .ve-body { padding: 18px 16px 20px; }
            .ve-otp { gap: 6px; }
            .ve-otp span { height: 54px; border-radius: 13px; font-size: 22px; }
        }
        @media (prefers-reduced-motion: reduce) { .ve-otp span.is-active:empty::after { animation: none; } }
    </style>
</head>
<body>
<div class="ve-stage">
    @include('partials.ivorian-motifs', ['culture' => 'national'])

    <a href="{{ route('home') }}" class="ve-brand">
        <img src="{{ $siteBrand['logo_compact_url'] ?? $siteBrand['logo_url'] }}" alt="{{ $siteBrand['site_name'] }}" style="height:46px;width:auto">
    </a>

    <div class="ve-card">
        <div class="ve-head">
            @include('partials.ivorian-motifs', ['culture' => 'newsletter'])
            <p class="ve-step">{{ __('Dernière étape') }}</p>
            <div class="ve-icon" aria-hidden="true"><x-icon name="shield-check" /></div>
            <h1 class="ve-title">{{ __('Vérifiez votre e-mail') }}</h1>
            <p class="ve-sub">{{ __('Nous avons envoyé un code à 6 chiffres à') }} <b>{{ $vEmail }}</b></p>
        </div>

        <div class="ve-body">
            @if(session('status'))
                <div class="ve-alert is-ok" role="status"><x-icon name="check-circle" /><p style="margin:0">{{ session('status') }}</p></div>
            @endif
            @if(session('mail_warning'))
                <div class="ve-alert is-warn" role="alert"><x-icon name="danger-circle" /><p style="margin:0">{{ session('mail_warning') }}</p></div>
            @endif

            {{-- Saisie du code (un seul champ « verification_code ») --}}
            <form method="POST" action="{{ route('verification.code') }}" data-ve-form>
                @csrf
                <label for="verification_code" class="ve-label">{{ __('Entrez le code à 6 chiffres') }}</label>
                <div class="ve-otp @error('verification_code') is-error @enderror" data-ve-otp>
                    <span></span><span></span><span></span><span></span><span></span><span></span>
                    <input id="verification_code" name="verification_code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="14" required
                           autocomplete="one-time-code" autofocus value="{{ old('verification_code') }}"
                           @error('verification_code') aria-invalid="true" aria-describedby="code-err" @enderror>
                </div>
                @error('verification_code')<p class="ve-err" id="code-err" role="alert">{{ $message }}</p>@enderror

                <ul class="ve-meta">
                    <li><x-icon name="clock-circle" />{{ __('Code valable 10 minutes') }}</li>
                    <li><x-icon name="shield-check" />{{ __('Ne le partagez avec personne') }}</li>
                </ul>

                <button type="submit" class="ve-btn ve-submit" data-ve-submit><x-icon name="check-circle" />{{ __('Vérifier le code') }}</button>
            </form>

            @if($vInbox)
                <a href="{{ $vInbox[1] }}" target="_blank" rel="noopener" class="ve-btn is-ghost ve-inbox"><x-icon name="link" />{{ __('Ouvrir :app', ['app' => $vInbox[0]]) }}</a>
            @endif

            <div class="ve-help">
                <p>{{ __('Vous n\'avez rien reçu ?') }}</p>
                <ul>
                    <li>{{ __('Patientez une minute : l\'e-mail peut mettre un peu de temps à arriver.') }}</li>
                    <li>{{ __('Regardez dans les courriers indésirables (spam) et l\'onglet Promotions.') }}</li>
                    <li>{{ __('Vérifiez que l\'adresse ci-dessus est bien la vôtre.') }}</li>
                </ul>
                <form method="POST" action="{{ route('verification.send') }}" data-ve-resend>
                    @csrf
                    <button type="submit" class="ve-btn is-ghost" data-ve-resend-btn><x-icon name="restart" /><span data-ve-resend-txt>{{ __('Renvoyer le code') }}</span></button>
                </form>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="ve-foot">
                @csrf
                <span>{{ __('Ce n\'est pas votre adresse ?') }}</span>
                <button type="submit" class="ve-link">{{ __('Utiliser un autre compte') }}</button>
            </form>
        </div>
    </div>

    <p class="ve-copy">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</p>
</div>

<script>
(function () {
    var form = document.querySelector('[data-ve-form]'), wrap = document.querySelector('[data-ve-otp]');
    if (!form || !wrap) return;
    var input = document.getElementById('verification_code'), boxes = wrap.querySelectorAll('span');

    function paint() {
        var v = input.value, focused = document.activeElement === input;
        boxes.forEach(function (b, i) {
            b.textContent = v[i] || '';
            b.classList.toggle('is-filled', !!v[i]);
            b.classList.toggle('is-active', focused && i === Math.min(v.length, 5));
        });
    }
    input.addEventListener('input', function () {
        var clean = input.value.replace(/\D/g, '').slice(0, 6);
        if (clean !== input.value) input.value = clean;
        wrap.classList.remove('is-error');
        paint();
        // Six chiffres saisis ou collés : envoi automatique.
        if (clean.length === 6 && !form.dataset.sent) { form.dataset.sent = '1'; form.querySelector('[data-ve-submit]').disabled = true; form.submit(); }
    });
    input.addEventListener('focus', paint);
    input.addEventListener('blur', paint);
    form.addEventListener('submit', function () { form.dataset.sent = '1'; form.querySelector('[data-ve-submit]').disabled = true; });
    paint();

    // Renvoi : patienter 30 s entre deux demandes (le compteur reprend après un rechargement).
    var btn = document.querySelector('[data-ve-resend-btn]'), label = document.querySelector('[data-ve-resend-txt]');
    var base = label.textContent, waitTxt = {{ \Illuminate\Support\Js::from(__('Renvoyer le code dans :s s')) }}, KEY = 've_resend_at';
    function left() { var t = 0; try { t = +sessionStorage.getItem(KEY) || 0; } catch (e) {} return Math.max(0, Math.ceil((t + 30000 - Date.now()) / 1000)); }
    function tick() {
        var s = left();
        btn.disabled = s > 0;
        label.textContent = s > 0 ? waitTxt.replace(':s', s) : base;
        if (s > 0) setTimeout(tick, 1000);
    }
    document.querySelector('[data-ve-resend]').addEventListener('submit', function () { try { sessionStorage.setItem(KEY, String(Date.now())); } catch (e) {} });
    @if(session('status') || session('mail_warning')) try { if (!sessionStorage.getItem(KEY)) sessionStorage.setItem(KEY, String(Date.now())); } catch (e) {} @endif
    tick();
})();
</script>
</body>
</html>
