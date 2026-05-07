<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Nos offres — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Choisissez le plan qui correspond à votre activité et boostez votre visibilité sur {{ $siteBrand['site_name'] }}.">
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif:   ['Playfair Display', 'Georgia', 'serif'],
                        elegant: ['Cormorant Garamond', 'Georgia', 'serif'],
                        sans:    ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        gold: { 300:'#fcd68a', 400:'#f5b942', 500:'#e8a020', 600:'#c4811a' },
                        dark: { 600:'#252520', 700:'#1c1c16', 800:'#141410', 900:'#0d0d0b' },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0d0d0b; }
        ::-webkit-scrollbar-thumb { background: #e8a020; border-radius: 3px; }
        .plan-card { transition: transform .3s ease, box-shadow .3s ease; }
        .plan-card:hover { transform: translateY(-6px); }
        .plan-popular { box-shadow: 0 0 0 2px #e8a020, 0 20px 60px rgba(232,160,32,0.15); }
        .toggle-btn { transition: all .25s ease; }
        .toggle-btn.active { background: #e8a020; color: #0d0d0b; }
        .price-monthly, .price-yearly { transition: opacity .2s ease; }
        .feature-check { color: #e8a020; }
        .feature-cross { color: #4b5563; }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">

@if(session('info'))
<div class="fixed top-16 left-0 right-0 z-40 px-4 py-2 text-center text-sm bg-sky-900/90 text-sky-100 border-b border-sky-700/50">{{ session('info') }}</div>
@endif
@if(session('error'))
<div class="fixed top-16 left-0 right-0 z-40 px-4 py-2 text-center text-sm bg-red-900/90 text-red-100 border-b border-red-700/50">{{ session('error') }}</div>
@endif

{{-- NAV MINIMALISTE --}}
<nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/5" style="background:rgba(13,13,11,0.95);backdrop-filter:blur(12px)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            @if(!empty($siteBrand['logo_url']))
                <img src="{{ $siteBrand['logo_url'] }}" alt="" class="h-8 w-auto">
            @else
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center">
                    <i class="fas fa-gem text-black text-xs"></i>
                </div>
            @endif
            <span class="font-serif font-bold text-gold-400 text-lg">{{ $siteBrand['site_name'] }}</span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white transition px-3 py-1.5">Connexion</a>
            <a href="{{ route('register') }}" class="text-sm font-semibold text-dark-900 px-4 py-2 rounded-lg" style="background:#e8a020">Créer un compte</a>
        </div>
    </div>
</nav>

{{-- HERO --}}
<section class="pt-32 pb-16 text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(45deg,#e8a020 0,#e8a020 1px,transparent 0,transparent 50%);background-size:20px 20px"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(232,160,32,0.07),transparent 70%)"></div>
    <div class="relative max-w-2xl mx-auto px-4">
        <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">Visibilité & croissance</p>
        <h1 class="font-serif text-4xl sm:text-5xl font-bold mb-4 leading-tight">Choisissez votre offre</h1>
        <p class="text-gray-400 text-lg font-elegant font-light leading-relaxed">
            Référencez votre activité sur le premier magazine culturel et touristique de Côte d'Ivoire et touchez des milliers de visiteurs qualifiés.
        </p>

        {{-- Toggle mensuel / annuel --}}
        @if($showMonthly || $showYearly)
        <div class="inline-flex items-center gap-1 mt-8 p-1 rounded-xl border border-white/10 bg-dark-800">
            @if($showMonthly)
            <button id="btn-monthly" onclick="setBilling('monthly')"
                    class="toggle-btn {{ !$showYearly || true ? 'active' : '' }} px-5 py-2 rounded-lg text-sm font-semibold">
                Mensuel
            </button>
            @endif
            @if($showYearly)
            <button id="btn-yearly" onclick="setBilling('yearly')"
                    class="toggle-btn {{ !$showMonthly ? 'active' : '' }} px-5 py-2 rounded-lg text-sm font-semibold text-gray-400 hover:text-white">
                Annuel
                @if($yearlySavingsLabel)
                <span class="ml-1.5 text-[10px] bg-gold-500/20 text-gold-400 px-1.5 py-0.5 rounded-full font-medium">{{ $yearlySavingsLabel }}</span>
                @endif
            </button>
            @endif
        </div>
        @endif
    </div>
</section>

{{-- PLANS --}}
<section class="pb-20 px-4">
    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

        @foreach($plans as $i => $plan)
        @php
            $isPopular  = $plan->code === 'silver' || ($i === 1 && $plans->count() >= 2);
            $icons      = ['fa-seedling','fa-star','fa-gem'];
            $iconColors = ['text-emerald-400','text-gold-400','text-violet-400'];
            $badgeBg    = ['bg-emerald-500/10 border-emerald-500/20 text-emerald-300','bg-gold-500/10 border-gold-500/20 text-gold-300','bg-violet-500/10 border-violet-500/20 text-violet-300'];
            $icon       = $icons[$i % 3];
            $iconColor  = $iconColors[$i % 3];
            $badge      = $badgeBg[$i % 3];
            $savings    = $plan->price_monthly > 0
                ? round(100 - ($plan->price_yearly / ($plan->price_monthly * 12) * 100))
                : 0;
        @endphp

        <div class="plan-card relative flex flex-col rounded-2xl border p-6
            {{ $isPopular ? 'plan-popular border-gold-500/40 bg-gradient-to-b from-dark-700 to-dark-800' : 'border-white/8 bg-dark-800/60' }}">

            @if($isPopular)
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gold-500 text-dark-900 text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest whitespace-nowrap">
                ⭐ Plus populaire
            </div>
            @endif

            {{-- En-tête plan --}}
            <div class="mb-6">
                <div class="w-12 h-12 rounded-xl {{ $isPopular ? 'bg-gold-500/15' : 'bg-white/5' }} flex items-center justify-center mb-4">
                    <i class="fas {{ $icon }} {{ $iconColor }} text-xl"></i>
                </div>
                <h2 class="font-serif text-xl font-bold mb-1">{{ $plan->name_fr }}</h2>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $plan->benefits_text ?: 'Boostez votre visibilité auprès de milliers de voyageurs.' }}</p>
            </div>

            {{-- Prix --}}
            <div class="mb-6">
                <div class="price-monthly">
                    <div class="flex items-end gap-1">
                        <span class="font-serif text-3xl font-bold">{{ number_format((float)$plan->price_monthly, 0, ',', ' ') }}</span>
                        <span class="text-gray-500 text-sm mb-1">FCFA / mois</span>
                    </div>
                    <p class="text-gray-600 text-xs mt-1">Engagement mensuel, résiliable à tout moment</p>
                </div>
                <div class="price-yearly hidden">
                    <div class="flex items-end gap-1">
                        <span class="font-serif text-3xl font-bold">{{ number_format((float)($plan->price_yearly / 12), 0, ',', ' ') }}</span>
                        <span class="text-gray-500 text-sm mb-1">FCFA / mois</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-gray-600 text-xs">soit {{ number_format((float)$plan->price_yearly, 0, ',', ' ') }} FCFA / an</p>
                        @if($savings > 0)
                        <span class="text-[10px] bg-gold-500/15 text-gold-400 px-1.5 py-0.5 rounded-full font-semibold">-{{ $savings }}%</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Features --}}
            <ul class="space-y-2.5 mb-8 flex-1">
                @php
                    if (!empty($plan->features_json)) {
                        // Fonctionnalités définies depuis le back-office
                        $features = array_map(fn($f) => [
                            'label' => $f['label'] ?? '',
                            'ok'    => (bool) ($f['included'] ?? false),
                        ], $plan->features_json);
                    } else {
                        // Fallback sur les champs booléens du plan
                        $features = [
                            ['label' => 'Badge vérifié',           'ok' => $plan->has_verified_badge],
                            ['label' => 'Photos ('.$plan->photos_limit.')', 'ok' => $plan->photos_limit > 0],
                            ['label' => 'Vidéo de présentation',   'ok' => $plan->has_video],
                            ['label' => 'Mise en avant accueil',   'ok' => $plan->has_homepage],
                            ['label' => 'Campagne newsletter',     'ok' => $plan->has_newsletter],
                            ['label' => 'Posts réseaux sociaux',   'ok' => $plan->has_social_posts],
                            ['label' => 'Statistiques avancées',   'ok' => in_array($plan->stats_level, ['advanced', 'full'])],
                            ['label' => 'Support prioritaire',     'ok' => in_array($plan->support_level, ['chat', 'dedicated'])],
                        ];
                    }
                @endphp
                @foreach($features as $feat)
                <li class="flex items-center gap-2.5 text-sm {{ $feat['ok'] ? 'text-gray-200' : 'text-gray-600' }}">
                    <i class="fas {{ $feat['ok'] ? 'fa-check feature-check' : 'fa-xmark feature-cross' }} text-xs w-4 text-center"></i>
                    {{ $feat['label'] }}
                </li>
                @endforeach
            </ul>

            {{-- CTA --}}
            <a href="{{ route('subscriptions.checkout', $plan) }}"
               class="block text-center py-3 rounded-xl font-bold text-sm transition-all duration-200
               {{ $isPopular
                   ? 'text-dark-900 hover:opacity-90'
                   : 'border border-gold-500/30 text-gold-300 hover:border-gold-400/60 hover:bg-gold-500/5' }}"
               @if($isPopular) style="background:linear-gradient(135deg,#f5b942,#e8a020)" @endif>
                @guest Choisir ce plan @else Souscrire maintenant @endguest
                <i class="fas fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>
        @endforeach

    </div>

    {{-- Garanties --}}
    <div class="max-w-3xl mx-auto mt-14 grid grid-cols-1 sm:grid-cols-3 gap-5 text-center">
        @foreach([
            ['fa-shield-check','Paiement sécurisé','CinetPay, Mobile Money, carte bancaire'],
            ['fa-rotate-left','Résiliation libre','Sans engagement pour les offres mensuelles'],
            ['fa-headset','Support dédié','Une équipe disponible pour vous accompagner'],
        ] as $g)
        <div class="p-5 rounded-xl border border-white/5 bg-dark-800/40">
            <i class="fas {{ $g[0] }} text-gold-400 text-2xl mb-3"></i>
            <p class="font-semibold text-sm mb-1">{{ $g[1] }}</p>
            <p class="text-gray-600 text-xs">{{ $g[2] }}</p>
        </div>
        @endforeach
    </div>

    {{-- FAQ rapide --}}
    <div class="max-w-2xl mx-auto mt-14 text-center">
        <p class="text-gray-500 text-sm">
            Une question ?
            <a href="{{ route('home') }}#contact" class="text-gold-400 hover:text-gold-300 font-medium transition">Contactez-nous</a>
            — ou consultez
            <a href="{{ route('home') }}" class="text-gold-400 hover:text-gold-300 font-medium transition">la page d'accueil</a>.
        </p>
    </div>
</section>

{{-- Footer --}}
<footer class="border-t border-white/5 py-6 text-center text-sm text-gray-700">
    &copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés
</footer>

<script>
    const SHOW_MONTHLY = @json((bool) $showMonthly);
    const SHOW_YEARLY  = @json((bool) $showYearly);

    // Cycle par défaut : mensuel si disponible, sinon annuel
    let billing = SHOW_MONTHLY ? 'monthly' : 'yearly';

    function setBilling(type) {
        billing = type;
        const btnM = document.getElementById('btn-monthly');
        const btnY = document.getElementById('btn-yearly');

        if (btnM) {
            btnM.classList.toggle('active',      type === 'monthly');
            btnM.classList.toggle('text-gray-400', type !== 'monthly');
        }
        if (btnY) {
            btnY.classList.toggle('active',      type === 'yearly');
            btnY.classList.toggle('text-gray-400', type !== 'yearly');
        }

        document.querySelectorAll('.price-monthly').forEach(el => el.classList.toggle('hidden', type !== 'monthly'));
        document.querySelectorAll('.price-yearly').forEach(el  => el.classList.toggle('hidden', type !== 'yearly'));
    }

    // Initialisation : afficher le bon prix au chargement
    document.addEventListener('DOMContentLoaded', function () {
        if (!SHOW_MONTHLY && SHOW_YEARLY) {
            // Seulement annuel : masquer les prix mensuels dès le départ
            document.querySelectorAll('.price-monthly').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.price-yearly').forEach(el  => el.classList.remove('hidden'));
        } else {
            // Mensuel par défaut (ou les deux disponibles)
            document.querySelectorAll('.price-monthly').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.price-yearly').forEach(el  => el.classList.add('hidden'));
        }
    });
</script>

@include('partials.homepage-footer')
</body>
</html>
