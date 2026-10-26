<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ __('Connexion') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        @include('partials.theme-light-bridge')
    <style>
        /* Image de fond configurable (back-office) de la page de connexion. */
        .login-bg {
            background-image: linear-gradient(rgba(0,107,63, 0.8), rgba(0,107,63, 0.8)), var(--login-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-shell { background: #ffffff; border: 1px solid #D8E9E0; box-shadow: 0 20px 50px rgb(14 59 42 / .10); }

        /* Panneau de marque : vert forêt, texte clair (couleurs en CSS : le pont de thème
           clair réécrit les utilitaires text-white). */
        .login-aside { background: #00874F; color: #ffffff; position: relative; }
        .login-aside::after { /* filet or discret en haut du panneau */
            content: ''; position: absolute; left: 40px; top: 0; width: 56px; height: 3px; background: #EB6E00;
        }
        .login-aside__kicker { font-size: 12px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #F7C396; }
        .login-aside__title { margin-top: 14px; font-family: var(--font-serif); font-size: 40px; line-height: 1.1; font-weight: 700; color: #ffffff; }
        .login-aside__lead { margin-top: 14px; max-width: 26rem; font-size: 15px; line-height: 1.65; color: rgba(255,255,255,.82); }
        .login-aside__list { margin-top: 30px; display: grid; gap: 14px; }
        .login-aside__list li { display: flex; align-items: flex-start; gap: 12px; font-size: 14px; color: rgba(255,255,255,.9); }
        .login-aside__list i {
            flex-shrink: 0; width: 30px; height: 30px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.1); color: #F7C396; font-size: 12px;
        }
        .login-aside__list span { padding-top: 5px; }
        .login-aside__note { margin-top: 32px; font-size: 12.5px; color: rgba(255,255,255,.7); }
        .login-aside__note i { margin-right: 6px; color: #F7C396; }

        /* Formulaire */
        .login-back { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; color: #557466; }
        .login-back:hover { color: #EB6E00; }
        .login-title { margin-top: 22px; font-family: var(--font-serif); font-size: 30px; font-weight: 700; color: #0E3B2A; }
        .login-subtitle { margin-top: 4px; margin-bottom: 26px; font-size: 14px; color: #557466; }
        .login-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #0E3B2A; }
        html:not(.dark) .login-input {
            width: 100%; height: 46px;
            padding: 0 14px 0 42px;
            background: #ffffff !important;
            border: 1px solid #8AA99B !important;
            border-radius: 10px;
            font-size: 15px; color: #0E3B2A !important;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        html:not(.dark) .login-input:hover { border-color: #EB6E00 !important; }
        html:not(.dark) .login-input:focus { outline: none; border-color: #EB6E00 !important; box-shadow: 0 0 0 3px rgb(235 110 0 / .2) !important; }
        html:not(.dark) .login-input.is-invalid { border-color: #c0362c !important; }
        .login-input-icon { position: absolute; left: 14px; top: 0; bottom: 0; display: flex; align-items: center; color: #557466; font-size: 13px; pointer-events: none; }
        .login-eye {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            width: 36px; height: 36px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #557466; font-size: 14px;
        }
        .login-eye:hover { background: #F3F8F5; color: #0E3B2A; }
        .login-check { width: 17px; height: 17px; accent-color: #EB6E00; }
        .login-link { color: #EB6E00; }
        .login-link:hover { color: #B85600; text-decoration: underline; text-underline-offset: 3px; }
        .login-submit {
            width: 100%; height: 48px; border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            font-size: 15px; font-weight: 600;
        }
        .login-alert { margin-bottom: 18px; padding: 12px 14px; border-radius: 10px; border: 1px solid; border-left-width: 4px; display: flex; gap: 10px; font-size: 14px; }
        .login-alert--error { background: #fdeceb; border-color: #f5c2bd; border-left-color: #c0362c; color: #8a241c; }
        .login-alert--success { background: #F3F8F5; border-color: #D8E9E0; border-left-color: #00874F; color: #00874F; }
        .login-signup {
            margin-top: 28px; padding-top: 22px; border-top: 1px solid #D8E9E0;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px;
            font-size: 14px; color: #557466;
        }

        /* Avec l'image de fond d'admin (voile sombre), le cadre ressort sur la photo. */
        .login-bg .login-shell { box-shadow: 0 24px 60px rgb(14 59 42 / .35); }

        /* Motifs ivoiriens : trame autour de la carte (fond crème), filigrane écru dans le panneau vert */
        .login-stage > .ivm .ivm-g { opacity: .24 !important; }
        .login-shell { position: relative; z-index: 1; }
        .login-aside { overflow: hidden; }
        .login-aside > :not(.ivm) { position: relative; z-index: 1; }
        .login-aside .ivm-g { opacity: .05 !important; }
        .login-aside .ivm-g > * { stroke: #F7C396 !important; }
        .login-aside .ivm-g > circle[fill] { fill: #F7C396 !important; }
        @media (max-width: 1023px) {
            .login-shell { max-width: 30rem; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-803c04c8.css') }}?v={{ @filemtime(public_path('css/tw-803c04c8.css')) }}">
</head>
@php
    $loginBackgroundImage = \Illuminate\Support\Facades\Schema::hasTable('login_background_images')
        ? \App\Models\LoginBackgroundImage::query()->find(1)
        : null;
    $hasCustomLoginBg = $loginBackgroundImage && $loginBackgroundImage->isVisible();
@endphp
<body class="min-h-screen bg-green-950 flex flex-col">
    @unless($hasCustomLoginBg)
        @include('partials.page-background')
    @endunless

@include('partials.public-top-nav')

    <div class="login-stage flex-1 flex items-center justify-center px-4 py-10 sm:py-14 relative overflow-hidden {{ $hasCustomLoginBg ? 'login-bg' : '' }}"
        @if($hasCustomLoginBg)
            style="--login-bg-image: url('{{ $loginBackgroundImage->image_url }}');"
        @endif
    >
    @unless($hasCustomLoginBg)
        @include('partials.ivorian-motifs', ['culture' => 'national'])
    @endunless
    <div class="login-shell w-full max-w-5xl grid lg:grid-cols-[1fr_1.05fr] overflow-hidden rounded-2xl">

        {{-- Panneau de marque (grand écran) : ce que permet un compte --}}
        <aside class="login-aside hidden lg:flex flex-col justify-between p-10 xl:p-12">
            @include('partials.ivorian-motifs', ['culture' => 'cultures', 'band' => false])
            <div>
                <p class="login-aside__kicker">{{ __('Espace membres') }}</p>
                <h1 class="login-aside__title">{{ __('Bon retour') }}<br>{{ __('parmi nous.') }}</h1>
                <p class="login-aside__lead">{{ __('Retrouvez vos réservations, vos échanges et vos adresses favorites en Côte d\'Ivoire.') }}</p>

                <ul class="login-aside__list">
                    <li><i class="fas fa-bed"></i><span>{{ __('Réservez un hébergement et suivez vos acomptes') }}</span></li>
                    <li><i class="fas fa-comments"></i><span>{{ __('Échangez en toute sécurité avec les prestataires') }}</span></li>
                    <li><i class="fas fa-heart"></i><span>{{ __('Gardez vos lieux et événements favoris') }}</span></li>
                    <li><i class="fas fa-store"></i><span>{{ __('Prestataire ? Gérez votre fiche et vos demandes') }}</span></li>
                </ul>
            </div>
            <p class="login-aside__note"><i class="fas fa-shield-halved"></i> {{ __('Connexion sécurisée — vos données restent privées.') }}</p>
        </aside>

        {{-- Formulaire --}}
        <div class="login-panel p-7 sm:p-10 xl:p-12">
            <a href="{{ route('home') }}" class="login-back">
                <i class="fas fa-arrow-left text-[11px]"></i> {{ __('Retour à l\'accueil') }}
            </a>

            <h2 class="login-title">{{ __('Connexion') }}</h2>
            <p class="login-subtitle">{{ __('Accédez à votre espace') }} {{ $siteBrand['site_name'] }}.</p>

            @if ($errors->any())
                <div class="login-alert login-alert--error" role="alert">
                    <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="login-alert login-alert--success" role="status">
                    <i class="fas fa-circle-check mt-0.5 shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="login-label">{{ __('Adresse e-mail') }}</label>
                    <div class="relative">
                        <span class="login-input-icon"><i class="fas fa-envelope"></i></span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="vous@exemple.ci"
                            class="login-input @error('email') is-invalid @enderror"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="login-label !mb-0">{{ __('Mot de passe') }}</label>
                        <a href="{{ route('password.request') }}" class="login-link text-xs">{{ __('Mot de passe oublié ?') }}</a>
                    </div>
                    <div class="relative">
                        <span class="login-input-icon"><i class="fas fa-lock"></i></span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="login-input pr-12"
                        >
                        <button type="button" class="login-eye" data-toggle-password="password" aria-label="{{ __('Afficher le mot de passe') }}" aria-pressed="false">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <label for="remember" class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input id="remember" name="remember" type="checkbox" class="login-check">
                    <span class="text-sm" style="color:#24503D">{{ __('Se souvenir de moi') }}</span>
                </label>

                {{-- Submit --}}
                <button type="submit" class="btn-primary login-submit">
                    {{ __('Se connecter') }} <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            {{-- Pas encore de compte --}}
            <div class="login-signup">
                <span>{{ __('Pas encore de compte ?') }}</span>
                <button type="button" data-open-register-modal class="login-link font-semibold">
                    {{ __('Créer un compte') }} <i class="fas fa-arrow-right text-[10px] ml-0.5"></i>
                </button>
            </div>
        </div>
    </div>
    </div>

    <script>
        // Afficher / masquer le mot de passe (aucun effet sur l'envoi du formulaire).
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-password'));
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? {{ \Illuminate\Support\Js::from(__('Masquer le mot de passe')) }} : {{ \Illuminate\Support\Js::from(__('Afficher le mot de passe')) }});
                btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });
    </script>

@include('partials.homepage-footer')
@include('partials.register-choice-modal')

</body>
</html>
