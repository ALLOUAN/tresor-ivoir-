<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ __('Créer un compte') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('partials.theme-light-bridge')
    <style>
        /* Inscription client : même famille que la connexion (cadre blanc, panneau vert à motifs),
           étape 2 sur 2, libellés au-dessus, erreurs sous chaque champ, mot de passe visible et jauge. Aplats. */
        .rv-stage { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; flex: 1; padding: 44px 16px 56px; background: #f6f3ed; }
        .rv-stage > .ivm .ivm-g { opacity: .22 !important; }
        .rv-shell { position: relative; z-index: 1; width: 100%; max-width: 1040px; display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); overflow: hidden; border-radius: 28px; background: #fff; border: 1px solid #e8e1d3; font-family: 'Inter', sans-serif; color: #1d1a16; }

        .rv-aside { position: relative; overflow: hidden; display: flex; flex-direction: column; padding: 40px 38px; background: #123a25; color: #fff; }
        .rv-aside > :not(.ivm) { position: relative; z-index: 1; }
        .rv-aside .ivm-g { opacity: .06 !important; }
        .rv-aside .ivm-g > * { stroke: #f6c38f !important; }
        .rv-step { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #f6c38f; }
        .rv-step i { display: inline-flex; gap: 4px; }
        .rv-step i b { width: 22px; height: 4px; border-radius: 4px; background: #f6c38f; }
        .rv-aside h1 { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(30px, 3.2vw, 40px); font-weight: 700; line-height: 1.08; color: #fff; }
        .rv-aside h1 em { font-style: normal; color: #f6c38f; }
        .rv-lead { margin: 14px 0 0; max-width: 34ch; font-size: 15px; line-height: 1.6; color: #cfe6d7; }
        .rv-perks { display: grid; gap: 14px; margin: 28px 0 0; padding: 0; list-style: none; }
        .rv-perks li { display: flex; align-items: flex-start; gap: 12px; font-size: 14.5px; line-height: 1.45; color: #fff; }
        .rv-perks li > .ico { flex: none; width: 34px; height: 34px; margin: 0; padding: 8px; border: 0; border-radius: 11px; background: rgba(255,255,255,.1); color: #f6c38f; }
        .rv-perks small { display: block; margin-top: 2px; font-size: 13px; color: #b9d6c4; }
        .rv-pro { margin-top: auto; padding-top: 28px; font-size: 13.5px; color: #cfe6d7; }
        .rv-pro a { color: #fff; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }

        .rv-main { padding: 34px 40px 32px; }
        .rv-back { display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; color: #6b6458; text-decoration: none; }
        .rv-back:hover { color: #9a4308; }
        .rv-back .ico { width: 16px; height: 16px; }
        .rv-title { margin: 18px 0 0; font-family: 'Playfair Display', serif; font-size: 30px; font-weight: 700; line-height: 1.15; color: #1d1a16; }
        .rv-sub { margin: 6px 0 22px; font-size: 14.5px; color: #6b6458; }
        .rv-alert { display: flex; gap: 10px; margin: 0 0 18px; padding: 12px 14px; border-radius: 14px; background: #fdeceb; border: 1px solid #f5c2bd; color: #8a241c; font-size: 14px; }
        .rv-alert .ico { flex: none; width: 18px; height: 18px; margin-top: 1px; }

        .rv-form { display: flex; flex-direction: column; gap: 16px; }
        .rv-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .rv-label { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin: 0 0 6px; font-size: 13.5px; font-weight: 700; color: #1d1a16; }
        .rv-label em { font-style: normal; font-weight: 500; font-size: 12.5px; color: #8a7f6b; }
        .rv-field { position: relative; }
        .rv-field > .ico { position: absolute; left: 14px; top: 50%; width: 18px; height: 18px; transform: translateY(-50%); color: #8f8778; pointer-events: none; }
        html .rv-input { width: 100%; height: 50px; padding: 0 14px 0 44px; border-radius: 14px; border: 1.5px solid #e1d7c4 !important; background: #fbf9f5 !important; color: #1d1a16 !important; font: inherit; font-size: 15px; outline: none; transition: border-color .15s ease, background .15s ease, box-shadow .15s ease; }
        html .rv-input.no-ico { padding-left: 14px; }
        html .rv-input::placeholder { color: #9a8f7c; }
        html .rv-input:hover { border-color: #cfc2a8 !important; }
        html .rv-input:focus { border-color: #b9500a !important; background: #fff !important; box-shadow: 0 0 0 4px rgba(185,80,10,.12) !important; }
        html .rv-input[aria-invalid="true"] { border-color: #c0362c !important; background: #fff !important; }
        .rv-input.has-eye { padding-right: 50px; }
        .rv-eye { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border: 0; border-radius: 10px; background: transparent; color: #6b6458; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .rv-eye:hover { background: #f3eee4; color: #1d1a16; }
        .rv-eye .ico { width: 18px; height: 18px; }
        .rv-err { margin: 6px 0 0; font-size: 12.5px; font-weight: 600; color: #b42318; }
        .rv-hint { margin: 6px 0 0; font-size: 12.5px; color: #8a7f6b; }

        .rv-meter { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-top: 8px; }
        .rv-meter i { height: 4px; border-radius: 4px; background: #ece5d6; transition: background .2s ease; }
        .rv-meter[data-level="1"] i:nth-child(-n+1) { background: #c0362c; }
        .rv-meter[data-level="2"] i:nth-child(-n+2) { background: #d4951c; }
        .rv-meter[data-level="3"] i:nth-child(-n+3) { background: #4a8a2a; }
        .rv-meter[data-level="4"] i { background: #1f7a43; }
        .rv-meter-txt { display: flex; justify-content: space-between; gap: 8px; margin: 6px 0 0; font-size: 12.5px; color: #8a7f6b; }
        .rv-meter-txt b { font-weight: 700; color: #4a443a; }
        .rv-match { margin: 6px 0 0; font-size: 12.5px; font-weight: 600; }
        .rv-match.is-ok { color: #1f7a43; }
        .rv-match.is-ko { color: #b42318; }

        .rv-terms { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 14px; background: #fbf9f5; border: 1px solid #ece5d6; font-size: 13.5px; line-height: 1.5; color: #4a443a; }
        .rv-terms input { flex: none; width: 18px; height: 18px; margin-top: 2px; accent-color: #b9500a; }
        .rv-terms a { color: #9a4308; font-weight: 700; }
        .rv-submit { display: flex; align-items: center; justify-content: center; gap: 9px; height: 52px; border: 0; border-radius: 999px; background: #b9500a; color: #fff; font: inherit; font-size: 15.5px; font-weight: 800; cursor: pointer; transition: background .15s ease; }
        .rv-submit:hover { background: #9a4308; }
        .rv-submit .ico { width: 18px; height: 18px; }
        .rv-submit[disabled] { opacity: .7; cursor: progress; }
        .rv-secure { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 0; font-size: 12.5px; color: #6b6458; }
        .rv-secure .ico { width: 15px; height: 15px; color: #1f8079; }
        .rv-login { margin-top: 22px; padding-top: 18px; border-top: 1px solid #f0eadf; text-align: center; font-size: 14px; color: #6b6458; }
        .rv-login a { color: #9a4308; font-weight: 700; text-decoration: none; }
        .rv-login a:hover, .rv-terms a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .rv-main a:focus-visible, .rv-main button:focus-visible, .rv-terms input:focus-visible, .rv-aside a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        @media (max-width: 1023px) {
            .rv-shell { grid-template-columns: minmax(0, 1fr); max-width: 560px; }
            .rv-aside { padding: 26px 26px 28px; }
            .rv-perks { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 18px; }
            .rv-perks li { flex-direction: column; gap: 8px; font-size: 13px; }
            .rv-perks small, .rv-pro { display: none; }
        }
        @media (max-width: 640px) {
            .rv-stage { padding: 16px 12px 32px; }
            .rv-shell { border-radius: 22px; }
            .rv-aside { padding: 20px 18px 22px; }
            .rv-aside h1 { font-size: 26px; margin-top: 12px; }
            .rv-lead { font-size: 14px; margin-top: 8px; }
            .rv-perks { gap: 6px; }
            .rv-perks li { font-size: 12px; }
            .rv-perks li > .ico { width: 30px; height: 30px; padding: 7px; }
            .rv-main { padding: 20px 18px 22px; }
            .rv-title { font-size: 24px; margin-top: 14px; }
            .rv-sub { margin-bottom: 18px; }
            .rv-row { grid-template-columns: minmax(0, 1fr); gap: 16px; }
        }
        @media (prefers-reduced-motion: reduce) { .rv-meter i, .rv-input { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-803c04c8.css') }}?v={{ @filemtime(public_path('css/tw-803c04c8.css')) }}">
</head>
<body class="min-h-screen flex flex-col" style="background:#f6f3ed">

@include('partials.public-top-nav')

<div class="rv-stage">
    @include('partials.ivorian-motifs', ['culture' => 'national'])
    <div class="rv-shell">

        {{-- Panneau : ce que permet le compte client --}}
        <aside class="rv-aside">
            @include('partials.ivorian-motifs', ['culture' => 'cultures', 'band' => false])
            <p class="rv-step"><i aria-hidden="true"><b></b><b></b></i>{{ __('Étape 2 sur 2 · Vos informations') }}</p>
            <h1>{{ __('Votre voyage en') }} <em>{{ __('Côte d\'Ivoire') }}</em> {{ __('commence ici.') }}</h1>
            <p class="rv-lead">{{ __('Un compte client gratuit pour réserver, acheter et tout retrouver au même endroit.') }}</p>
            <ul class="rv-perks">
                <li><x-icon name="bed" /><span>{{ __('Réservez en ligne') }}<small>{{ __('Hôtels, résidences, visites et expériences.') }}</small></span></li>
                <li><x-icon name="clipboard-list" /><span>{{ __('Suivez vos commandes') }}<small>{{ __('Réservations, achats et historique réunis.') }}</small></span></li>
                <li><x-icon name="heart" /><span>{{ __('Gardez vos coups de cœur') }}<small>{{ __('Vos adresses favorites, sur tous vos appareils.') }}</small></span></li>
            </ul>
            <p class="rv-pro">{{ __('Vous êtes un professionnel du tourisme ?') }} <a href="{{ route('plans.public') }}">{{ __('Créer un compte prestataire') }}</a></p>
        </aside>

        {{-- Formulaire --}}
        <main class="rv-main">
            <a href="{{ route('home') }}" class="rv-back"><x-icon name="alt-arrow-left" />{{ __('Retour à l\'accueil') }}</a>
            <h2 class="rv-title">{{ __('Créer mon compte') }}</h2>
            <p class="rv-sub">{{ __('Inscription gratuite, aucun paiement demandé.') }}</p>

            @if ($errors->any())
                <div class="rv-alert" role="alert">
                    <x-icon name="danger-circle" />
                    <p style="margin:0">{{ trans_choice('Corrigez le champ signalé ci-dessous.|Corrigez les :count champs signalés ci-dessous.', $errors->count(), ['count' => $errors->count()]) }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="rv-form" data-rv-form novalidate>
                @csrf
                <input type="hidden" name="role" value="visitor">

                <div class="rv-row">
                    <div>
                        <label for="first_name" class="rv-label">{{ __('Prénom') }}</label>
                        <div class="rv-field">
                            <x-icon name="user-rounded" />
                            <input id="first_name" name="first_name" type="text" required maxlength="80" value="{{ old('first_name') }}" autocomplete="given-name" placeholder="{{ __('Jean') }}" class="rv-input" @error('first_name') aria-invalid="true" aria-describedby="first_name-err" @enderror>
                        </div>
                        @error('first_name')<p class="rv-err" id="first_name-err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="rv-label">{{ __('Nom') }}</label>
                        <input id="last_name" name="last_name" type="text" required maxlength="80" value="{{ old('last_name') }}" autocomplete="family-name" placeholder="{{ __('Kouassi') }}" class="rv-input no-ico" @error('last_name') aria-invalid="true" aria-describedby="last_name-err" @enderror>
                        @error('last_name')<p class="rv-err" id="last_name-err">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="rv-label">{{ __('Adresse e-mail') }}</label>
                    <div class="rv-field">
                        <x-icon name="chat-round-dots" />
                        <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email') }}" autocomplete="email" inputmode="email" placeholder="vous@exemple.ci" class="rv-input" aria-describedby="email-hint @error('email') email-err @enderror" @error('email') aria-invalid="true" @enderror>
                    </div>
                    @error('email')<p class="rv-err" id="email-err">{{ $message }}</p>@enderror
                    <p class="rv-hint" id="email-hint">{{ __('Un code de vérification vous sera envoyé à cette adresse.') }}</p>
                </div>

                <div>
                    <label for="phone" class="rv-label">{{ __('Téléphone') }} <em>{{ __('(facultatif)') }}</em></label>
                    <div class="rv-field">
                        <x-icon name="global" />
                        <input id="phone" name="phone" type="tel" maxlength="20" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="+225 07 00 00 00 00" class="rv-input" aria-describedby="phone-hint @error('phone') phone-err @enderror" @error('phone') aria-invalid="true" @enderror>
                    </div>
                    @error('phone')<p class="rv-err" id="phone-err">{{ $message }}</p>@enderror
                    <p class="rv-hint" id="phone-hint">{{ __('Pratique pour être joint par l\'établissement le jour J.') }}</p>
                </div>

                <div>
                    <label for="password" class="rv-label">{{ __('Mot de passe') }}</label>
                    <div class="rv-field">
                        <x-icon name="shield-check" />
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="{{ __('8 caractères minimum') }}" class="rv-input has-eye" aria-describedby="pw-meter-txt @error('password') password-err @enderror" @error('password') aria-invalid="true" @enderror>
                        <button type="button" class="rv-eye" data-rv-eye="password" aria-label="{{ __('Afficher le mot de passe') }}" aria-pressed="false"><x-icon name="eye" /></button>
                    </div>
                    @error('password')<p class="rv-err" id="password-err">{{ $message }}</p>@enderror
                    <div class="rv-meter" data-rv-meter data-level="0" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                    <p class="rv-meter-txt" id="pw-meter-txt"><span>{{ __('Astuce : mélangez lettres, chiffres et symboles.') }}</span><b data-rv-meter-label></b></p>
                </div>

                <div>
                    <label for="password_confirmation" class="rv-label">{{ __('Confirmer le mot de passe') }}</label>
                    <div class="rv-field">
                        <x-icon name="shield-check" />
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="{{ __('Saisissez-le à nouveau') }}" class="rv-input has-eye" aria-describedby="pw-match">
                        <button type="button" class="rv-eye" data-rv-eye="password_confirmation" aria-label="{{ __('Afficher le mot de passe') }}" aria-pressed="false"><x-icon name="eye" /></button>
                    </div>
                    <p class="rv-match" id="pw-match" data-rv-match aria-live="polite"></p>
                </div>

                {{-- Champ piège anti-robots : invisible et ignoré par les lecteurs d'écran, doit rester vide --}}
<div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden"><label for="website">Site web</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off" value=""></div>
                <label class="rv-terms" for="newsletter_opt_in">
                    <input id="newsletter_opt_in" name="newsletter_opt_in" type="checkbox" value="1" @checked(old('newsletter_opt_in'))>
                    <span>{{ __('Je souhaite recevoir la newsletter : adresses, événements et bons plans en Côte d\'Ivoire. Désinscription en un clic.') }}</span>
                </label>

                <div>
                    <label class="rv-terms" for="terms">
                        <input id="terms" name="terms" type="checkbox" value="1" required @checked(old('terms')) @error('terms') aria-invalid="true" @enderror>
                        <span>{{ __('J\'accepte les') }} <a href="{{ route('information.show', 'conditions-generales-utilisation') }}" target="_blank" rel="noopener">{{ __('conditions générales d\'utilisation') }}</a> {{ __('de') }} {{ $siteBrand['site_name'] }}.</span>
                    </label>
                    @error('terms')<p class="rv-err">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="rv-submit" data-rv-submit><x-icon name="user-rounded" />{{ __('Créer mon compte') }}</button>
                <p class="rv-secure"><x-icon name="shield-check" />{{ __('Vos données restent confidentielles.') }}</p>
            </form>

            <p class="rv-login">{{ __('Déjà inscrit ?') }} <a href="{{ route('login') }}">{{ __('Se connecter') }}</a></p>
        </main>
    </div>
</div>

@include('partials.homepage-footer')

<script>
(function () {
    var form = document.querySelector('[data-rv-form]');
    if (!form) return;
    var pw = document.getElementById('password'), pw2 = document.getElementById('password_confirmation');
    var meter = document.querySelector('[data-rv-meter]'), meterLabel = document.querySelector('[data-rv-meter-label]'), match = document.querySelector('[data-rv-match]');
    var labels = {{ \Illuminate\Support\Js::from(['', __('Faible'), __('Moyen'), __('Bon'), __('Excellent')]) }};
    var txt = {{ \Illuminate\Support\Js::from(['ok' => __('Les mots de passe correspondent.'), 'ko' => __('Les mots de passe ne correspondent pas.'), 'show' => __('Afficher le mot de passe'), 'hide' => __('Masquer le mot de passe')]) }};

    // Jauge indicative : la seule règle exigée reste « 8 caractères minimum ».
    function level(v) {
        if (!v) return 0;
        if (v.length < 8) return 1;
        var s = 1 + (/[a-z]/.test(v) && /[A-Z]/.test(v) ? 1 : 0) + (/\d/.test(v) ? 1 : 0) + (/[^A-Za-z0-9]/.test(v) ? 1 : 0) + (v.length >= 12 ? 1 : 0);
        return Math.min(4, Math.max(2, s));
    }
    function checkMatch() {
        if (!pw2.value) { match.textContent = ''; match.className = 'rv-match'; return; }
        var ok = pw.value === pw2.value;
        match.textContent = ok ? txt.ok : txt.ko;
        match.className = 'rv-match ' + (ok ? 'is-ok' : 'is-ko');
    }
    pw.addEventListener('input', function () { var l = level(pw.value); meter.dataset.level = l; meterLabel.textContent = labels[l]; checkMatch(); });
    pw2.addEventListener('input', checkMatch);

    document.querySelectorAll('[data-rv-eye]').forEach(function (b) {
        b.addEventListener('click', function () {
            var input = document.getElementById(b.dataset.rvEye), show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            b.setAttribute('aria-pressed', show ? 'true' : 'false');
            b.setAttribute('aria-label', show ? txt.hide : txt.show);
        });
    });

    // Validation native du navigateur au moment de l'envoi (novalidate évite les bulles au premier focus).
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
        if (pw.value !== pw2.value) { e.preventDefault(); checkMatch(); pw2.focus(); return; }
        var btn = form.querySelector('[data-rv-submit]'); btn.disabled = true;
    });

    // Avec une erreur serveur, le premier champ en erreur reçoit le focus.
    var bad = form.querySelector('[aria-invalid="true"]'); if (bad) bad.focus();
})();
</script>
</body>
</html>
