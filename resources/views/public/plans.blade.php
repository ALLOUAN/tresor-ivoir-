<!DOCTYPE html>
<html lang="fr" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Nos offres — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Choisissez le plan qui correspond à votre activité et boostez votre visibilité sur {{ $siteBrand['site_name'] }}.">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { 300:'#fdbe7b', 400:'#fa9a3c', 500:'#f2790f', 600:'#d4630a' },
                        dark: { 600:'#e9e5d9', 700:'#e9e5d9', 800:'#e9e5d9', 900:'#e9e5d9' },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background:#e9e5d9; }
        ::-webkit-scrollbar-thumb { background: #f2790f; border-radius: 3px; }
        .plan-card { transition: transform .3s ease, box-shadow .3s ease; }
        .plan-card:hover { transform: translateY(-6px); }
        .plan-popular { box-shadow: 0 0 0 2px #f2790f, 0 20px 60px rgba(242, 121, 15,0.15); }
        .toggle-btn { transition: all .25s ease; }
        .toggle-btn.active { background: #f2790f; color: #ffffff; }
        .price-monthly, .price-yearly { transition: opacity .2s ease; }
        .feature-check { color: #f2790f; }
        .feature-cross { color: #4b5563; }
        /* Image de fond configurable (back-office) de la section des plans. */
        .plans-bg {
            background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55));
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .plans-bg[style*="--plans-bg-image"] {
            background-image: var(--plans-bg-image, none);
        }
        .category-card { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; cursor: pointer; }
        .category-card:hover { transform: translateY(-4px); }
        .category-card.is-selected { border-color: #f2790f; box-shadow: 0 0 0 2px rgba(242,121,15,0.35), 0 16px 40px rgba(242,121,15,0.12); }
        .cat-panel { animation: cat-panel-in .3s ease; }
        @keyframes cat-panel-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">
    @include('partials.page-background')

@include('partials.public-top-nav')

@if(session('info'))
<div class="px-4 py-2 text-center text-sm bg-green-900/90 text-green-100 border-b border-green-700/50">{{ session('info') }}</div>
@endif
@if(session('error'))
<div class="px-4 py-2 text-center text-sm bg-red-900/90 text-red-100 border-b border-red-700/50">{{ session('error') }}</div>
@endif

{{-- HERO --}}
<section class="pt-16 pb-16 text-center relative overflow-hidden">
    <div class="relative max-w-2xl mx-auto px-4">
        <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-3">Visibilité & croissance</p>
        <h1 class="font-serif text-4xl sm:text-5xl font-bold mb-4 leading-tight">Choisissez votre offre</h1>
        <p class="text-[#1c1915] text-xl font-elegant font-light leading-relaxed">
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

{{-- CATÉGORIES --}}
<section class="px-4 pb-4">
    <div class="max-w-5xl mx-auto">
        <p class="text-center text-gray-500 text-sm mb-6">
            <i class="fas fa-hand-pointer text-gold-400 mr-1.5"></i>
            Commencez par choisir votre secteur d'activité
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" id="category-cards">
            @foreach($rootCategories as $cat)
            <button type="button"
                    class="category-card text-left rounded-2xl border border-white/8 bg-dark-800/60 p-4 {{ $selectedCategorySlug === $cat->slug ? 'is-selected' : '' }}"
                    data-category-slug="{{ $cat->slug }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3"
                     style="background: {{ $cat->color_hex ? $cat->color_hex.'26' : 'rgba(242,121,15,0.15)' }};">
                    <i class="fas {{ $cat->icon ?: 'fa-tag' }} text-lg" style="color: {{ $cat->color_hex ?: '#f2790f' }};"></i>
                </div>
                <p class="font-semibold text-sm text-white">{{ $cat->name_fr }}</p>
                <p class="text-gray-600 text-xs mt-1">
                    {{ ($plansByCategory[$cat->slug] ?? collect())->count() }} forfait(s) disponible(s)
                </p>
            </button>
            @endforeach
        </div>
    </div>
</section>

{{-- PLANS --}}
@php
    $plansImage = \Illuminate\Support\Facades\Schema::hasTable('plans_section_images')
        ? \App\Models\PlansSectionImage::query()->find(1)
        : null;
@endphp
<section class="pb-20 px-4 {{ ($plansImage && $plansImage->isVisible()) ? 'plans-bg' : '' }}"
    @if($plansImage && $plansImage->isVisible())
        style="--plans-bg-image: url('{{ $plansImage->image_url }}');"
    @endif
    id="plans-section"
>
    <p id="no-category-hint" class="max-w-lg mx-auto text-center text-gray-600 text-sm py-10 {{ $selectedCategorySlug ? 'hidden' : '' }}">
        Sélectionnez une catégorie ci-dessus pour découvrir les forfaits qui lui sont dédiés.
    </p>

    @foreach($rootCategories as $cat)
    @php $catPlans = $plansByCategory[$cat->slug] ?? collect(); @endphp
    <div class="cat-panel max-w-5xl mx-auto {{ $selectedCategorySlug === $cat->slug ? '' : 'hidden' }}" data-category-panel="{{ $cat->slug }}">
        <h2 class="text-center font-serif text-2xl font-bold mb-8">{{ $cat->name_fr }}</h2>
        @if($catPlans->isEmpty())
            <p class="text-center text-gray-600 text-sm py-10">Aucun forfait actif pour cette catégorie pour le moment.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($catPlans as $i => $plan)
                    @include('public.partials._plan-card', ['plan' => $plan, 'i' => $i, 'count' => $catPlans->count(), 'category' => $cat])
                @endforeach
            </div>
        @endif
    </div>
    @endforeach

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

    // Sélection de catégorie : révèle son panneau de forfaits, sans rechargement de page.
    (function () {
        const cards = document.querySelectorAll('.category-card');
        const panels = document.querySelectorAll('.cat-panel');
        const hint = document.getElementById('no-category-hint');

        function selectCategory(slug, scroll) {
            cards.forEach(c => c.classList.toggle('is-selected', c.dataset.categorySlug === slug));
            panels.forEach(p => p.classList.toggle('hidden', p.dataset.categoryPanel !== slug));
            if (hint) hint.classList.add('hidden');

            const url = new URL(window.location.href);
            url.searchParams.set('categorie', slug);
            window.history.replaceState({}, '', url);

            if (scroll) {
                document.getElementById('plans-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        cards.forEach(card => {
            card.addEventListener('click', () => selectCategory(card.dataset.categorySlug, true));
        });
    })();
</script>

@include('partials.homepage-footer')
</body>
</html>
