<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Créer un compte — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        sans:  ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        gold: { 400: '#f5b942', 500: '#e8a020', 600: '#c4811a' },
                        dark: { 800: '#141410', 900: '#0d0d0b' },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        body {
            background:
                radial-gradient(900px 500px at 12% 8%, rgba(232,160,32,0.14), transparent 60%),
                radial-gradient(700px 420px at 88% 90%, rgba(99,102,241,0.10), transparent 62%),
                #0a0907;
        }
        .auth-shell {
            position: relative;
        }
        .auth-shell::before {
            content: "";
            position: absolute;
            inset: -14px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(232,160,32,0.18), rgba(255,255,255,0.03), rgba(232,160,32,0.08));
            filter: blur(14px);
            opacity: .45;
            pointer-events: none;
        }
        .glass-panel {
            position: relative;
            border: 1px solid rgba(255,255,255,0.12);
            background: linear-gradient(145deg, rgba(20,18,14,0.88), rgba(13,11,9,0.92));
            backdrop-filter: blur(8px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.42), inset 0 1px 0 rgba(255,255,255,0.04);
        }
        .aside-panel {
            max-width: 100%;
            justify-self: stretch;
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(232,160,32,0.12), transparent 58%),
                linear-gradient(155deg, rgba(25,22,18,0.9), rgba(12,10,8,0.9));
        }
        .aside-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(255,255,255,0.06), transparent 30%);
            opacity: .6;
        }
        .aside-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid rgba(232,160,32,0.3);
            background: rgba(232,160,32,0.09);
            color: #f3d8a0;
            border-radius: 999px;
            padding: .28rem .62rem;
            font-size: .67rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-weight: 700;
        }
        .aside-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .9rem;
            margin-bottom: .75rem;
        }
        .aside-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .aside-logo-wrap {
            position: relative;
            border-radius: .9rem;
            padding: 2px;
            background: linear-gradient(135deg, rgba(232,160,32,0.55), rgba(255,255,255,0.18), rgba(232,160,32,0.22));
            box-shadow: 0 8px 20px rgba(0,0,0,0.35);
        }
        .aside-logo-inner {
            border-radius: calc(.9rem - 2px);
            overflow: hidden;
        }
        .aside-title {
            margin: 0;
            color: #f5b942;
            font-size: 1.06rem;
            line-height: 1.2;
            font-weight: 700;
        }
        .aside-subtitle {
            margin-top: .2rem;
            font-size: .72rem;
            color: #6b7280;
        }
        .aside-content {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: .8rem;
            align-items: start;
        }
        .aside-summary {
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
            border-radius: .85rem;
            padding: .65rem .8rem;
        }
        .aside-chip-list {
            display: grid;
            gap: .45rem;
        }
        .account-type-card {
            border: 1px solid rgba(232,160,32,0.35);
            background:
                radial-gradient(120% 140% at 50% 0%, rgba(232,160,32,0.2), rgba(232,160,32,0.08) 45%, rgba(232,160,32,0.03) 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.1), 0 12px 30px rgba(0,0,0,0.32);
        }
        .account-type-kicker {
            font-size: .82rem;
            letter-spacing: .22em;
            text-transform: uppercase;
            font-weight: 900;
            color: #f8cf77;
            text-shadow: 0 0 16px rgba(232,160,32,0.35);
        }
        .account-type-value {
            margin-top: .35rem;
            font-size: clamp(1.8rem, 3vw, 2.35rem);
            line-height: 1.05;
            font-weight: 900;
            color: #fff;
            text-shadow: 0 3px 18px rgba(0,0,0,0.35), 0 0 20px rgba(232,160,32,0.18);
        }
        .account-type-note {
            margin-top: .4rem;
            font-size: .74rem;
            color: #d1a74d;
            letter-spacing: .03em;
        }
        .stat-chip {
            border: 1px solid rgba(232,160,32,0.25);
            background: linear-gradient(135deg, rgba(232,160,32,0.16), rgba(232,160,32,0.06));
            color: #f5d28e;
            border-radius: .7rem;
            font-size: .72rem;
            padding: .45rem .6rem;
            white-space: nowrap;
        }
        @media (max-width: 640px) {
            .aside-top {
                flex-direction: column;
                align-items: flex-start;
            }
            .aside-content {
                grid-template-columns: 1fr;
            }
            .aside-chip-list {
                grid-template-columns: 1fr;
            }
        }
        .field-input {
            background: rgba(9,8,7,0.92);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .field-input:focus {
            border-color: rgba(232,160,32,0.5);
            box-shadow: 0 0 0 3px rgba(232,160,32,0.12);
        }
        .plan-card input[type="radio"]:checked + label {
            border-color: rgba(232,160,32,0.62);
            background: linear-gradient(140deg, rgba(232,160,32,0.18), rgba(232,160,32,0.06));
            box-shadow: 0 10px 24px rgba(0,0,0,0.34), 0 0 0 1px rgba(232,160,32,0.2) inset;
        }
        .step-panel {
            transition: opacity .35s ease, transform .35s ease;
        }
        .step-panel.is-hidden {
            opacity: 0;
            transform: translateX(20px);
            pointer-events: none;
        }
        .step-panel.is-active {
            opacity: 1;
            transform: translateX(0);
        }
        .payment-card {
            background: #fff;
            border: .5px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.25rem;
        }
        .payment-field {
            border: .5px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            color: #111827;
        }
        .payment-field:focus {
            border-color: #7F77DD;
            box-shadow: 0 0 0 3px rgba(127,119,221,.18);
            outline: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 font-sans">

    <div class="auth-shell w-full max-w-7xl py-4">

        <div class="max-w-5xl mx-auto">
            {{-- Card --}}
            <div class="glass-panel rounded-3xl p-5 sm:p-6 lg:p-7">
                <aside class="glass-panel aside-panel rounded-3xl p-4 sm:p-5 mb-5">
                    <div class="aside-top">
                        <span class="aside-badge"><i class="fas fa-star text-[10px]"></i>Espace pro</span>
                        <p class="text-[11px] text-gray-500">Inscription prestataire</p>
                    </div>
                    <div class="aside-content">
                        <div>
                            <div class="aside-brand">
                                @if(!empty($siteBrand['logo_url']))
                                    <div class="aside-logo-wrap">
                                        <div class="aside-logo-inner inline-flex items-center justify-center w-16 h-16 bg-white/5 border border-white/10 overflow-hidden p-1">
                                            <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                                        </div>
                                    </div>
                                @else
                                    <div class="aside-logo-wrap">
                                        <div class="aside-logo-inner inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-600">
                                            <i class="fas fa-gem text-black text-xl"></i>
                                        </div>
                                    </div>
                                @endif
                                <div>
                                    <h1 class="aside-title font-serif">{{ $siteBrand['site_name'] }}</h1>
                                    <p class="aside-subtitle">{{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}</p>
                                </div>
                            </div>
                            <div class="aside-summary mt-3">
                                <p class="text-gray-200 text-xs font-semibold mb-1">Compte prestataire</p>
                                <p class="text-gray-500 text-xs leading-relaxed">Publiez votre activité, choisissez votre forfait et activez votre présence en ligne rapidement.</p>
                            </div>
                        </div>
                        <div class="aside-chip-list">
                            <p class="stat-chip"><i class="fas fa-check text-amber-300 mr-2"></i>Création rapide</p>
                            <p class="stat-chip"><i class="fas fa-check text-amber-300 mr-2"></i>Choix du forfait</p>
                            <p class="stat-chip"><i class="fas fa-check text-amber-300 mr-2"></i>Activation immédiate</p>
                        </div>
                    </div>
                </aside>

                <h2 class="text-white text-xl font-semibold mb-1">Créer votre compte</h2>
                <p class="text-gray-500 text-sm mb-6">Rejoignez la communauté Trésor Ivoire.</p>

            @if(session('info'))
                <div class="mb-5 p-3 rounded-lg border border-sky-500/30 bg-sky-500/10 text-sky-200 text-sm">{{ session('info') }}</div>
            @endif

            {{-- Erreurs globales --}}
            @if ($errors->any())
                <div class="mb-5 p-3 rounded-lg border border-red-500/30 bg-red-500/10 text-red-300 text-sm flex items-start gap-2">
                    <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                <form method="POST" action="{{ route('register.post') }}" class="space-y-4" id="register-step-form">
                @csrf
                <div id="register-step-1" class="step-panel is-active space-y-4">

                <div class="grid lg:grid-cols-2 gap-3">
                    <input type="hidden" name="role" value="provider">
                    <div class="account-type-card rounded-xl px-4 py-4 text-center">
                        <p class="account-type-kicker">Type de compte</p>
                        <p class="account-type-value">Prestataire</p>
                        <p class="account-type-note">Espace professionnel activé</p>
                    </div>

                @php
                    $resolvedPlanId = (int) old('plan_id', $selectedPlanId ?? request('plan'));
                    if ($resolvedPlanId <= 0 && isset($plans) && $plans->isNotEmpty()) {
                        $resolvedPlanId = (int) $plans->first()->id;
                    }
                @endphp
                @if(isset($plans) && $plans->isNotEmpty())
                    <div id="provider-plan-picker">
                        <p class="text-xs font-medium text-gray-400 mb-3">Type de compte prestataire</p>
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($plans as $plan)
                                <div class="plan-card">
                                    <input
                                        type="radio"
                                        name="plan_id"
                                        id="plan_{{ $plan->id }}"
                                        value="{{ $plan->id }}"
                                        class="sr-only"
                                        {{ $resolvedPlanId === (int) $plan->id ? 'checked' : '' }}
                                    >
                                    <label
                                        for="plan_{{ $plan->id }}"
                                        class="h-full flex flex-col items-start justify-between gap-2 p-3 rounded-xl border border-white/10 cursor-pointer transition-all duration-200 text-gray-300 hover:border-white/20"
                                    >
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-white truncate">{{ $plan->name_fr }}</span>
                                            @if(!empty($plan->benefits_text))
                                                <span class="block text-xs text-gray-500 truncate">{{ $plan->benefits_text }}</span>
                                            @endif
                                        </span>
                                        <span class="shrink-0 text-xs font-semibold text-amber-300">
                                            {{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA/mois
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-600 mt-2">Le forfait choisi sera utilisé pour finaliser l’abonnement après création du compte.</p>
                    </div>
                @endif
                </div>

                {{-- Prénom + Nom --}}
                <div class="grid lg:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs font-medium text-gray-400 mb-1.5">Prénom <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                            <input id="first_name" name="first_name" type="text" required maxlength="80"
                                   value="{{ old('first_name') }}" autocomplete="given-name" placeholder="Jean"
                                   class="field-input w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none @error('first_name') border-red-500/60 @enderror">
                        </div>
                        @error('first_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-medium text-gray-400 mb-1.5">Nom <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                            <input id="last_name" name="last_name" type="text" required maxlength="80"
                                   value="{{ old('last_name') }}" autocomplete="family-name" placeholder="Kouassi"
                                   class="field-input w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none @error('last_name') border-red-500/60 @enderror">
                        </div>
                        @error('last_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- E-mail --}}
                <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-400 mb-1.5">Adresse e-mail <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-at absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="email" name="email" type="email" required maxlength="255"
                               value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.ci"
                               class="field-input w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none @error('email') border-red-500/60 @enderror">
                    </div>
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Téléphone (facultatif visiteur, recommandé prestataire) --}}
                <div>
                    <label for="phone" class="block text-xs font-medium text-gray-400 mb-1.5">Téléphone <span class="text-gray-600">(facultatif)</span></label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="phone" name="phone" type="tel" maxlength="20"
                               value="{{ old('phone') }}" autocomplete="tel" placeholder="+225 07 00 00 00 00"
                               class="field-input w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none @error('phone') border-red-500/60 @enderror">
                    </div>
                    @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                </div>

                {{-- Mot de passe --}}
                <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-400 mb-1.5">Mot de passe <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="password" name="password" type="password" required
                               autocomplete="new-password" placeholder="8 caractères minimum"
                               class="field-input w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none @error('password') border-red-500/60 @enderror">
                    </div>
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-gray-400 mb-1.5">Confirmer le mot de passe <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-lock-keyhole absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password" placeholder="Répétez le mot de passe"
                               class="field-input w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none">
                    </div>
                </div>
                </div>

                {{-- Submit --}}
                    <button type="button" id="go-to-payment"
                            class="w-full py-3 rounded-xl font-bold text-sm text-black transition-all duration-200 flex items-center justify-center gap-2 mt-2"
                            style="background: linear-gradient(135deg,#f5b942,#e8a020); box-shadow: 0 4px 20px rgba(232,160,32,0.3)">
                        <i class="fas fa-user-plus text-xs"></i>
                        Créer mon compte
                    </button>
                </div>

                <div id="register-step-2" class="step-panel is-hidden hidden space-y-4" aria-hidden="true">
                    <div class="payment-card">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-white text-sm font-bold" style="background:#7F77DD;">4</span>
                            <h3 class="text-lg font-semibold" style="color:#7F77DD;">Paiement de l'abonnement</h3>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="payment_method" class="block text-sm font-semibold text-gray-900 mb-2">Moyen de paiement <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <select id="payment_method" name="payment_method" required class="payment-field w-full pl-10 pr-3 py-3 text-sm">
                                        <option value="">Sélectionner un moyen de paiement</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="card">Carte bancaire</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="payment_phone" class="block text-sm font-semibold text-gray-900 mb-2">Numéro pour le paiement <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-mobile-screen-button absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input id="payment_phone" name="payment_phone" type="tel" required placeholder="Numéro Mobile Money" class="payment-field w-full pl-10 pr-3 py-3 text-sm">
                                </div>
                                <p class="text-sm text-gray-500 mt-2">Numéro associé à votre compte Mobile Money</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" id="back-to-account"
                                class="sm:w-auto w-full py-3 px-5 rounded-xl font-semibold text-sm text-gray-200 border border-white/20 hover:border-white/35 transition">
                            Retour
                        </button>
                        <button type="submit"
                                class="w-full py-3 rounded-xl font-bold text-sm text-black transition-all duration-200 flex items-center justify-center gap-2"
                                style="background: linear-gradient(135deg,#f5b942,#e8a020); box-shadow: 0 4px 20px rgba(232,160,32,0.3)">
                            <i class="fas fa-check-circle text-xs"></i>
                            Confirmer le paiement
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </div>

        {{-- Lien connexion --}}
        <p class="text-center text-sm text-gray-600 mt-6">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="text-yellow-400 hover:text-yellow-300 font-medium transition">Se connecter</a>
        </p>
        <p class="text-center text-gray-700 text-xs mt-3">
            &copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}
        </p>
    </div>

<script>
    (function () {
        const form = document.getElementById('register-step-form');
        const step1 = document.getElementById('register-step-1');
        const step2 = document.getElementById('register-step-2');
        const goToPaymentBtn = document.getElementById('go-to-payment');
        const backBtn = document.getElementById('back-to-account');

        if (!form || !step1 || !step2 || !goToPaymentBtn || !backBtn) {
            return;
        }

        const fieldsStep1 = Array.from(step1.querySelectorAll('input, select, textarea'))
            .filter((el) => el.type !== 'hidden' && !el.disabled);
        const fieldsStep2 = Array.from(step2.querySelectorAll('input, select, textarea'));

        function hidePanel(panel) {
            panel.classList.remove('is-active');
            panel.classList.add('is-hidden');
            window.setTimeout(function () {
                if (panel.classList.contains('is-hidden')) {
                    panel.classList.add('hidden');
                }
            }, 360);
        }

        function showPanel(panel) {
            panel.classList.remove('hidden');
            requestAnimationFrame(function () {
                panel.classList.remove('is-hidden');
                panel.classList.add('is-active');
            });
        }

        function toggleStep(activeStep) {
            const isStep2 = activeStep === 2;

            if (isStep2) {
                hidePanel(step1);
                showPanel(step2);
            } else {
                hidePanel(step2);
                showPanel(step1);
            }

            step1.setAttribute('aria-hidden', isStep2 ? 'true' : 'false');
            step2.setAttribute('aria-hidden', isStep2 ? 'false' : 'true');
        }

        goToPaymentBtn.addEventListener('click', function () {
            const invalidField = fieldsStep1.find((field) => !field.checkValidity());
            if (invalidField) {
                invalidField.reportValidity();
                invalidField.focus();
                return;
            }
            toggleStep(2);
        });

        backBtn.addEventListener('click', function () {
            toggleStep(1);
        });

        if (fieldsStep2.some((field) => field.value)) {
            toggleStep(2);
        }
    })();
</script>
</body>
</html>
