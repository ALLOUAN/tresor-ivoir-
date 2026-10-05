<!DOCTYPE html>
<html lang="fr" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Créer un compte — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amber: { 400: '#fa9a3c', 500: '#f2790f', 600: '#9f4709' }
                    }
                }
            }
        }
    </script>
    @include('partials.theme-light-bridge')
    <style>
        html:not(.dark) .bg-slate-800\/60 { background-color:rgba(0,0,0,0.04) !important; }
        html:not(.dark) .border-slate-600 { border-color:#c2b89e !important; }
        html:not(.dark) .placeholder-slate-500::placeholder { color:#665f52 !important; }
        .step-panel { transition: opacity .3s ease, transform .3s ease; }
        .step-panel.is-hidden { opacity: 0; transform: translateX(16px); pointer-events: none; }
        .step-panel.is-active { opacity: 1; transform: translateX(0); }
        .plan-radio input[type="radio"]:checked + label {
            border-color: rgba(242,121,15,0.6);
            background: rgba(242,121,15,0.1);
        }
    </style>
</head>
<body class="min-h-screen bg-green-950 flex flex-col">
    @include('partials.page-background')

@include('partials.public-top-nav')

<div class="flex-1 flex items-center justify-center p-4 py-10">
<div class="w-full max-w-2xl">

    {{-- Retour accueil --}}
    <div class="mb-4">
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-slate-500 hover:bg-slate-700 hover:text-white">
            <i class="fas fa-arrow-left text-xs"></i>
            Retour à l'accueil
        </a>
    </div>

    {{-- Logo / Brand --}}
    <div class="text-center mb-8">
        @if(!empty($siteBrand['logo_url']))
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/5 border border-slate-600 mb-4 overflow-hidden p-1">
                <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
            </div>
        @else
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-500 mb-4">
                <i class="fas fa-gem text-white text-2xl"></i>
            </div>
        @endif
        <h1 class="text-3xl font-bold text-orange-400 tracking-wide">{{ $siteBrand['site_name'] }}</h1>
        <p class="text-slate-400 text-sm mt-1">{{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}</p>
        <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full border border-orange-500/30 bg-orange-500/10 text-orange-300 text-[11px] font-semibold uppercase tracking-wide">
            <i class="fas fa-star text-[10px]"></i> Espace pro — Inscription prestataire
        </span>
    </div>

    {{-- Card --}}
    <div class="bg-green-900 border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8">
        <h2 class="text-white text-xl font-semibold mb-1">Créer votre compte</h2>
        <p class="text-slate-500 text-sm mb-6">Publiez votre activité, choisissez votre forfait et activez votre présence en ligne rapidement.</p>

        @if(session('info'))
            <div class="mb-5 p-3 rounded-lg border border-green-700 bg-green-900/40 text-green-300 text-sm">
                {{ session('info') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-lg border border-red-700 bg-red-900/40 text-red-300 text-sm flex items-start gap-2">
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

            <input type="hidden" name="role" value="provider">
            <input type="hidden" name="category_slug" value="{{ $selectedCategorySlug ?? '' }}">

            @if(isset($selectedPlan) && $selectedPlan)
                <input type="hidden" name="plan_id" value="{{ $selectedPlan->id }}">
                <div class="rounded-xl border border-orange-500/30 bg-orange-500/10 p-3.5 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium text-orange-300/80 uppercase tracking-wide mb-1">Forfait sélectionné</p>
                        <p class="text-sm font-semibold text-white truncate">{{ $selectedPlan->name_fr }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $selectedPlan->providerCategory?->name_fr ?? 'Toutes catégories' }}
                            · {{ number_format((float) $selectedPlan->price_monthly, 0, ',', ' ') }} FCFA/mois
                        </p>
                    </div>
                    <a href="{{ route('plans.public') }}" class="shrink-0 text-xs font-medium text-orange-300 hover:text-orange-200 transition whitespace-nowrap">
                        Changer d'offre
                    </a>
                </div>
            @elseif(isset($plans) && $plans->isNotEmpty())
                @php $resolvedPlanId = (int) old('plan_id', $selectedPlanId ?? 0); @endphp
                <div id="provider-plan-picker">
                    <div class="flex items-center justify-between mb-2.5">
                        <p class="text-xs font-medium text-slate-400">Type de compte prestataire</p>
                        <a href="{{ route('plans.public') }}" class="text-[11px] text-orange-400 hover:text-orange-300 transition">Voir toutes les offres par catégorie</a>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-3">
                        @foreach($plans as $plan)
                            <div class="plan-radio">
                                <input type="radio" name="plan_id" id="plan_{{ $plan->id }}" value="{{ $plan->id }}" class="sr-only"
                                       {{ $resolvedPlanId === (int) $plan->id ? 'checked' : '' }}>
                                <label for="plan_{{ $plan->id }}"
                                       class="h-full flex flex-col items-start justify-between gap-2 p-3 rounded-xl border border-slate-600 bg-slate-800 cursor-pointer transition text-slate-300 hover:border-slate-500">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-white truncate">{{ $plan->name_fr }}</span>
                                        @if(!empty($plan->benefits_text))
                                            <span class="block text-xs text-slate-500 truncate">{{ $plan->benefits_text }}</span>
                                        @endif
                                    </span>
                                    <span class="shrink-0 text-xs font-semibold text-orange-400">
                                        {{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA/mois
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-slate-600 mt-2">Le forfait choisi sera utilisé pour finaliser l'abonnement après création du compte.</p>
                </div>
            @endif

            {{-- Prénom + Nom --}}
            <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-slate-300 text-sm font-medium mb-1.5">Prénom <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-user text-sm"></i></span>
                        <input id="first_name" name="first_name" type="text" required maxlength="80"
                               value="{{ old('first_name') }}" autocomplete="given-name" placeholder="Jean"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('first_name') border-red-500 @enderror">
                    </div>
                    @error('first_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="last_name" class="block text-slate-300 text-sm font-medium mb-1.5">Nom <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-user text-sm"></i></span>
                        <input id="last_name" name="last_name" type="text" required maxlength="80"
                               value="{{ old('last_name') }}" autocomplete="family-name" placeholder="Kouassi"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('last_name') border-red-500 @enderror">
                    </div>
                    @error('last_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- E-mail + Téléphone --}}
            <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-slate-300 text-sm font-medium mb-1.5">Adresse e-mail <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-envelope text-sm"></i></span>
                        <input id="email" name="email" type="email" required maxlength="255"
                               value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.ci"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('email') border-red-500 @enderror">
                    </div>
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="block text-slate-300 text-sm font-medium mb-1.5">Téléphone <span class="text-slate-600">(facultatif)</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-phone text-sm"></i></span>
                        <input id="phone" name="phone" type="tel" maxlength="20"
                               value="{{ old('phone') }}" autocomplete="tel" placeholder="+225 07 00 00 00 00"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('phone') border-red-500 @enderror">
                    </div>
                    @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Mot de passe --}}
            <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-slate-300 text-sm font-medium mb-1.5">Mot de passe <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-lock text-sm"></i></span>
                        <input id="password" name="password" type="password" required
                               autocomplete="new-password" placeholder="8 caractères minimum"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('password') border-red-500 @enderror">
                    </div>
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-slate-300 text-sm font-medium mb-1.5">Confirmer le mot de passe <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-lock text-sm"></i></span>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password" placeholder="Répétez le mot de passe"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>
                </div>
            </div>

            {{-- CGU --}}
            <div class="flex items-start gap-2.5">
                <input id="terms" name="terms" type="checkbox" value="1" required
                       class="mt-0.5 w-4 h-4 rounded bg-slate-700 border-slate-600 text-orange-500 focus:ring-orange-500 @error('terms') border-red-500 @enderror">
                <label for="terms" class="text-slate-400 text-xs leading-relaxed">
                    J'accepte les
                    <a href="{{ route('information.show', 'conditions-generales-utilisation') }}" target="_blank" class="text-orange-400 hover:text-orange-300 transition">conditions générales d'utilisation</a>
                    de {{ $siteBrand['site_name'] }}.
                </label>
            </div>

            {{-- Submit --}}
            <button type="button" id="go-to-payment"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition duration-150 flex items-center justify-center gap-2 text-sm mt-2">
                <i class="fas fa-user-plus text-sm"></i>
                Créer mon compte
            </button>
        </div>

        <div id="register-step-2" class="step-panel is-hidden hidden space-y-5" aria-hidden="true">
            <div class="rounded-xl border border-slate-700 bg-slate-800/50 p-5">
                <div class="flex items-center gap-3 mb-5">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-500 text-white text-sm font-bold">2</span>
                    <h3 class="text-white text-base font-semibold">Paiement de l'abonnement</h3>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="payment_method" class="block text-slate-300 text-sm font-medium mb-1.5">Moyen de paiement <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-credit-card text-sm"></i></span>
                            <select id="payment_method" name="payment_method" required
                                    class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                                <option value="">Sélectionner un moyen de paiement</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="card">Carte bancaire</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="payment_phone" class="block text-slate-300 text-sm font-medium mb-1.5">Numéro pour le paiement <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-500"><i class="fas fa-mobile-screen-button text-sm"></i></span>
                            <input id="payment_phone" name="payment_phone" type="tel" required placeholder="Numéro Mobile Money"
                                   class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                        </div>
                        <p class="text-slate-500 text-xs mt-1.5">Numéro associé à votre compte Mobile Money</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" id="back-to-account"
                        class="sm:w-auto w-full py-2.5 px-5 rounded-lg font-semibold text-sm text-slate-300 border border-slate-600 hover:border-slate-500 hover:text-white transition">
                    Retour
                </button>
                <button type="submit"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition duration-150 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-check-circle text-sm"></i>
                    Confirmer le paiement
                </button>
            </div>
        </div>
        </form>
    </div>

    {{-- Lien connexion --}}
    <p class="text-center text-slate-500 text-sm mt-6">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="text-orange-400 hover:text-orange-300 font-medium transition">Se connecter</a>
    </p>
    <p class="text-center text-slate-600 text-xs mt-3">
        &copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés
    </p>
</div>
</div>

@include('partials.homepage-footer')

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
            }, 320);
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
