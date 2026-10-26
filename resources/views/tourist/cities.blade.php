<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Régions Touristiques') }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Montserrat', serif; }
        .city-card { transition: transform .2s ease, box-shadow .2s ease; }
        .city-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(14,59,42,.4); }
        html:not(.dark) body { background:#D8E9E0; color: #0E3B2A; }
        html:not(.dark) .city-card { background:#F3F8F5 !important; border-color: rgba(14,59,42,.12) !important; box-shadow: 0 8px 20px rgba(14,59,42,.05); }
        /* Image de fond configurable (back-office) du bandeau de la page Tourisme. */
        .tourist-hero {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .tourist-hero[style*="--tourist-hero-bg-image"] {
            background-image: var(--tourist-hero-bg-image, none);
        }
    </style>
    <style>
        /* Catégories : familles colorées (palette du logo). Mobile : une rangée de pastilles défilante ; ordinateur : barre latérale groupée. Aplats. */
        .cx { --c: 235,110,0; display: flex; gap: 8px; font-family: 'Montserrat', sans-serif; }
        .cx .is-nature { --c: 0,135,79; }
        .cx .is-culture { --c: 235,110,0; }
        .cx .is-taste { --c: 235,110,0; }
        .cx .is-leisure { --c: 0,135,79; }
        .cx-head, .cx-group { display: none; }
        .cx-item { flex: none; display: inline-flex; align-items: center; gap: 9px; padding: 6px 14px 6px 6px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; color: #0E3B2A; font-size: 14px; font-weight: 600; line-height: 1.25; text-decoration: none; transition: background .15s ease, border-color .15s ease, color .15s ease; }
        .cx-item:hover { border-color: rgba(var(--c), .45); color: rgb(var(--c)); }
        .cx-ico { flex: none; width: 30px; height: 30px; border-radius: 999px; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; background: rgba(var(--c), .1); color: rgb(var(--c)); font-size: 13px; }
        .cx-ico img { width: 100%; height: 100%; object-fit: cover; }
        .cx-ico .ico { width: 16px; height: 16px; }
        .cx-item.is-active { background: #EB6E00; border-color: #EB6E00; color: #fff; }
        .cx-item.is-active .cx-ico { background: rgba(255,255,255,.18); color: #fff; }
        .cx-item:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        @media (max-width: 1023px) {
            .cx { overflow-x: auto; overscroll-behavior-x: contain; scroll-snap-type: x proximity; -webkit-overflow-scrolling: touch; scrollbar-width: none; margin: 0 -1.5rem; padding: 4px 1.5rem 10px; }
            .cx::-webkit-scrollbar { display: none; }
            .cx-item { white-space: nowrap; scroll-snap-align: center; }
        }
        @media (min-width: 1024px) {
            .cities-layout { display: grid; grid-template-columns: 17.5rem minmax(0, 1fr); gap: 2rem; align-items: start; max-width: 72rem; margin: 0 auto; padding: 0 1.5rem; }
            .cities-layout:not(:has(.cities-aside)) { grid-template-columns: minmax(0, 1fr); }
            .cities-layout > section { max-width: none; margin: 0; padding-left: 0; padding-right: 0; }
            .cities-aside { position: sticky; top: 6.5rem; padding-bottom: 0 !important; }
            .cx { position: relative; flex-direction: column; gap: 2px; max-height: calc(100vh - 8.5rem); overflow-y: auto; padding: 0 10px 12px; background: #fff; border: 1px solid #D8E9E0; border-radius: 24px; scrollbar-width: thin; scrollbar-color: #D8E9E0 transparent; }
            .cx-head { display: block; position: sticky; top: 0; z-index: 2; margin: 0 -10px 6px; padding: 18px 18px 12px; background: #fff; border-bottom: 1px solid #F3F8F5; }
            .cx-head::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 4px; background-color: #EB6E00; background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='48' height='4'><rect width='16' height='4' fill='%23EB6E00'/><rect x='16' width='6' height='4' fill='%23EB6E00'/><rect x='22' width='14' height='4' fill='%2300874F'/><rect x='36' width='4' height='4' fill='%23D8E9E0'/><rect x='40' width='8' height='4' fill='%2300874F'/></svg>"); }
            .cat-title { margin: 0; color: #0E3B2A; font: 700 1.2rem/1.2 var(--font-serif, 'Montserrat', serif); }
            .cx-sub { margin: 3px 0 0; font-size: 12.5px; color: #557466; }
            .cx-group { display: flex; align-items: center; gap: 8px; margin: 12px 8px 4px; font-size: 10.5px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #557466; }
            .cx-group::before { content: ''; width: 8px; height: 8px; border-radius: 2px; transform: rotate(45deg); background: rgb(var(--g)); }
            .cx-group[data-fam="nature"] { --g: 0,135,79; }
            .cx-group[data-fam="culture"] { --g: 235,110,0; }
            .cx-group[data-fam="taste"] { --g: 235,110,0; }
            .cx-group[data-fam="leisure"] { --g: 0,135,79; }
            .cx-item { position: relative; width: 100%; padding: 6px 10px 6px 6px; border: 0; border-radius: 14px; background: transparent; font-size: 14px; font-weight: 500; color: #0E3B2A; }
            .cx-item:hover { background: rgba(var(--c), .07); color: #0E3B2A; }
            .cx-ico { width: 32px; height: 32px; border-radius: 10px; }
            .cx-item.is-active { background: rgba(var(--c), .1); color: #0E3B2A; font-weight: 700; }
            .cx-item.is-active::before { content: ''; position: absolute; left: -10px; top: 8px; bottom: 8px; width: 4px; border-radius: 0 4px 4px 0; background: rgb(var(--c)); }
            .cx-item.is-active .cx-ico { background: rgb(var(--c)); color: #fff; }
            .cx-all.is-active { background: #F3F8F5; }
            .cities-main .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
    @include('partials.page-background')

@include('partials.public-top-nav')

@php
    $touristHeroImage = \Illuminate\Support\Facades\Schema::hasTable('tourist_hero_images')
        ? \App\Models\TouristHeroImage::query()->find(1)
        : null;
@endphp

{{-- Hero --}}
<section class="hero-regions relative py-20 overflow-hidden {{ ($touristHeroImage && $touristHeroImage->isVisible()) ? 'tourist-hero' : '' }}"
    @if($touristHeroImage && $touristHeroImage->isVisible())
        style="--tourist-hero-bg-image: url('{{ $touristHeroImage->image_url }}');"
    @endif
>
    @unless($touristHeroImage && $touristHeroImage->isVisible())
        @include('partials.ivorian-motifs', ['culture' => 'regions'])
    @endunless
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <div class="ti-hero-card ti-tour-hero inline-block rounded-[28px] px-6 py-8 sm:px-12 sm:py-10">
            <p class="ti-tour-hero__kicker"><i class="far fa-compass" aria-hidden="true"></i>{{ __('Découverte') }}</p>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                {{ __('Régions Touristiques') }}
            </h1>
            <p class="text-gray-200 text-xl max-w-2xl mx-auto">
                {{ __('Explorez les villes et leurs merveilles touristiques à travers toute la Côte d\'Ivoire.') }}
            </p>
            @php $tourSites = (int) $cities->sum('sites_count'); @endphp
            <ul class="ti-tour-hero__stats">
                <li><i class="fas fa-city" aria-hidden="true"></i><strong>{{ $cities->count() }}</strong> {{ trans_choice('ville|villes', $cities->count()) }}</li>
                @if($tourSites > 0)
                    <li><i class="fas fa-map-pin" aria-hidden="true"></i><strong>{{ $tourSites }}</strong> {{ trans_choice('site touristique|sites touristiques', $tourSites) }}</li>
                @endif
                @if($categories->isNotEmpty())
                    <li><i class="fas fa-layer-group" aria-hidden="true"></i><strong>{{ $categories->count() }}</strong> {{ trans_choice('catégorie|catégories', $categories->count()) }}</li>
                @endif
            </ul>
        </div>
    </div>
</section>

@include('partials.hero-separator', ['class' => 'mb-8'])

{{-- Filtre par catégorie --}}
{{-- Catégories (barre latérale sur ordinateur, rangée défilante sur mobile) + grille des villes --}}
<div class="cities-layout">
@if($categories->isNotEmpty())
@php
    // Catégories regroupées par famille (même règle que la page thème) ; chaque famille a sa couleur du logo.
    $catFamilies = [
        'nature' => ['label' => __('Nature & paysages'), 'items' => collect()],
        'culture' => ['label' => __('Culture & patrimoine'), 'items' => collect()],
        'taste' => ['label' => __('Saveurs'), 'items' => collect()],
        'leisure' => ['label' => __('Loisirs & sorties'), 'items' => collect()],
    ];
    foreach ($categories as $cat) {
        $cs = (string) $cat->slug;
        $fam = match (true) {
            (bool) preg_match('/plage|lagune|parc|reserve|cascade|montagne|lac|cours-eau|nature|foret/', $cs) && ! str_contains($cs, 'loisir') => 'nature',
            (bool) preg_match('/gastronomie|maquis|cuisine/', $cs) => 'taste',
            (bool) preg_match('/sport|aventure|nocturne|divertissement|loisir|zoo/', $cs) => 'leisure',
            default => 'culture',
        };
        $catFamilies[$fam]['items']->push($cat);
    }
    $catFamilies = array_filter($catFamilies, fn ($f) => $f['items']->isNotEmpty());
@endphp
<section class="cities-aside max-w-6xl mx-auto px-6 pb-8" aria-label="{{ __('Catégories') }}">
    <div class="cx" data-chips-row>
        <div class="cx-head">
            <p class="cat-title">{{ __('Catégories') }}</p>
            <p class="cx-sub">{{ __('Filtrer les destinations par envie') }}</p>
        </div>
        <a href="{{ route('tourist.cities') }}" class="cx-item cx-all {{ !$category ? 'is-active' : '' }}" @if(!$category) aria-current="page" @endif>
            <span class="cx-ico"><x-icon name="widget" /></span>
            <span class="cx-name">{{ __('Toutes catégories') }}</span>
        </a>
        @foreach($catFamilies as $famKey => $fam)
            <p class="cx-group" data-fam="{{ $famKey }}">{{ $fam['label'] }}</p>
            @foreach($fam['items'] as $cat)
                @php $on = $category && $category->id === $cat->id; @endphp
                <a href="{{ route('tourist.cities', ['categorie' => $cat->slug]) }}" class="cx-item is-{{ $famKey }} {{ $on ? 'is-active' : '' }}" @if($on) aria-current="page" @endif>
                    <span class="cx-ico">
                        @if($cat->icon_image_url)
                            <img src="{{ $cat->icon_image_url }}" alt="" loading="lazy">
                        @else
                            <i class="{{ $cat->icon ?: 'fas fa-tag' }}" aria-hidden="true"></i>
                        @endif
                    </span>
                    <span class="cx-name">{{ $cat->name }}</span>
                </a>
            @endforeach
        @endforeach
    </div>
</section>
@endif

{{-- Cities grid --}}
<section class="cities-main max-w-6xl mx-auto px-6 pb-20">
    @if($cities->isNotEmpty())
    <div class="ti-tour-bar">
        <p class="ti-tour-bar__count">
            <strong>{{ $cities->count() }}</strong> {{ trans_choice('destination|destinations', $cities->count()) }}
            @if($category) <span>{{ $cities->count() > 1 ? __('villes proposant') : __('ville proposant') }} « {{ $category->name }} »</span>@endif
        </p>
        @if($category)
            <a href="{{ route('tourist.cities') }}" class="ti-tour-bar__clear"><i class="fas fa-xmark" aria-hidden="true"></i>{{ __('Effacer') }}</a>
        @endif
    </div>
    @elseif($category)
    <p class="text-center text-slate-500 text-sm mb-6">
        <a href="{{ route('tourist.cities') }}" class="text-orange-400 hover:text-orange-300 ml-1"><i class="fas fa-times-circle"></i> {{ __('Effacer') }}</a>
    </p>
    @endif
    @if($cities->isEmpty())
    <div class="text-center py-20 text-slate-500">
        <i class="fas fa-city text-5xl mb-4 block text-slate-700"></i>
        @if($category)
        <p>{{ __('Aucune ville ne propose «') }} {{ $category->name }} {{ __('» pour le moment.') }}</p>
        <a href="{{ route('tourist.cities') }}" class="text-orange-400 hover:text-orange-300 text-sm mt-2 inline-block">{{ __('Voir toutes les villes') }}</a>
        @else
        <p>{{ __('Aucune ville disponible pour le moment.') }}</p>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($cities as $city)
            @include('partials.city-card', ['city' => $city])
        @endforeach
    </div>
    @endif
</section>
</div>

@include('partials.homepage-footer')
<script>
    (function () {
        // Sur mobile, la catégorie choisie est ramenée au centre de la rangée de filtres (refait au chargement complet : les icônes changent les largeurs).
        var row = document.querySelector('[data-chips-row]');
        if (!row) return;
        function centerActive() {
            if (row.scrollWidth <= row.clientWidth) return;
            var active = row.querySelector('.is-active');
            if (!active) return;
            var x = active.getBoundingClientRect().left - row.getBoundingClientRect().left + row.scrollLeft;
            row.scrollLeft = Math.max(0, x - (row.clientWidth - active.offsetWidth) / 2);
        }
        centerActive();
        window.addEventListener('load', centerActive);
    })();
</script>
</body>
</html>
