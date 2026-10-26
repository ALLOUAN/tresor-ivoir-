<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $restaurant->name }} — {{ __('Restaurants & Gastronomie') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => $restaurant->name, 'description' => $restaurant->short_description ?: $restaurant->description, 'image' => $restaurant->cover_image ?: ($restaurant->media->first()?->url ?: $restaurant->thumbnail)])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .btn-primary { background: #C0392B; box-shadow: 0 12px 28px rgba(192,57,43,0.3); color: #fff; transition: transform .2s ease, filter .2s ease; }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .hero-cover { position: relative; overflow: hidden; background: #0E3B2A; }
        .hero-cover img { transition: transform .6s ease; }
        .hero-cover:hover img { transform: scale(1.05); }
        .gallery-tile { position: relative; overflow: hidden; cursor: pointer; border-radius: .85rem; }
        .gallery-tile img { transition: transform .4s ease; }
        .gallery-tile:hover img { transform: scale(1.08); }
        .bento-card { border: 1px solid rgba(14,59,42,0.07); background: #FFFFFF; box-shadow: 0 10px 28px rgba(14,59,42,0.06); }
        .bento-icon { width: 2.2rem; height: 2.2rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; background: rgba(192, 57, 43, 0.12); color: #C0392B; }
        .section-kicker { letter-spacing: .22em; }
        /* « Notre carte » : une feuille de menu, pas une grille de tuiles. */
        .menu-sheet { background: #FFFFFF; border: 1px solid #D8E9E0; border-radius: 6px; padding: 26px 28px 22px; }
        .menu-group + .menu-group { margin-top: 26px; }
        .menu-group__title { display: flex; align-items: center; gap: 14px; margin-bottom: 10px;
            font-family: var(--font-serif, Georgia, serif); font-style: italic; font-size: 19px; font-weight: 600; color: #8a2b1f; }
        .menu-group__title::after { content: ''; flex: 1; height: 1px; background: #D8E9E0; }
        .menu-item { display: flex; align-items: flex-start; gap: 12px; padding: 9px 0; }
        .menu-item + .menu-item { border-top: 1px dashed #D8E9E0; }
        .menu-item__img { width: 44px; height: 44px; flex-shrink: 0; border-radius: 50%; object-fit: cover; }
        .menu-item__line { display: flex; align-items: baseline; gap: 8px; }
        .menu-item__name { font-size: 15px; font-weight: 600; color: #0E3B2A; text-decoration: none; }
        a.menu-item__name:hover { color: #B85600; text-decoration: underline; text-underline-offset: 3px; }
        .menu-item.is-picked { margin: 0 -12px; padding: 9px 12px; border-radius: 12px; background: #F3F8F5; border-top-color: transparent; }
        .menu-item.is-picked + .menu-item { border-top-color: transparent; }
        .menu-item.is-picked .menu-item__name { color: #B85600; }
        .menu-item__dots { flex: 1; min-width: 16px; border-bottom: 1px dotted #8AA99B; transform: translateY(-4px); }
        .menu-item__price { flex-shrink: 0; font-size: 14px; font-weight: 700; color: #0E3B2A; font-variant-numeric: tabular-nums; }
        .menu-item__desc { margin-top: 2px; font-size: 13px; line-height: 1.5; color: #557466; }
        .menu-sheet__more { display: inline-flex; align-items: center; gap: 6px; margin-top: 18px; padding-top: 14px;
            border-top: 1px solid #D8E9E0; width: 100%; font-size: 13px; font-weight: 600; color: #a5341f; }
        .menu-sheet__more:hover { color: #7c2416; }
        @media (max-width: 639px) { .menu-sheet { padding: 20px 16px 16px; } .menu-item__name { font-size: 14px; } }
        .review-card { border: 1px solid rgba(14,59,42,0.07); background: #ffffff; box-shadow: 0 8px 22px rgba(14,59,42,0.05); }
        .related-tile { border: 1px solid rgba(14,59,42,0.07); background: #ffffff; box-shadow: 0 10px 26px rgba(14,59,42,0.06); transition: transform .25s ease, box-shadow .25s ease; }
        .related-tile:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(192,57,43,0.16); }
        .contact-card { border: 1px solid rgba(14,59,42,0.08); background: #FFFFFF; box-shadow: 0 20px 50px rgba(14,59,42,0.1); }
    </style>
    @include('partials.detail-theme')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

        @php
            $photos = $restaurant->media;
            $mainPhoto = $photos->first()?->url ?: $restaurant->cover_image;
        @endphp

        @include('partials.detail-hero', ['model' => $restaurant, 'sectorName' => __('Restaurants & Gastronomie'), 'sectorIcon' => 'fa-utensils', 'backUrl' => route('restaurant.index'), 'shareUrl' => route('restaurant.show', $restaurant->slug), 'bookAnchor' => '#reserver', 'items' => ($menuByCategory ?? collect())->flatten(1), 'orderable' => $restaurant->provider_id && $restaurant->accepts_orders, 'menuUrl' => route('restaurant.menu', $restaurant->slug)])

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start mt-8">
            <div class="lg:col-span-3 space-y-12 dx-main">
                @include('partials.detail-tabs', ['offerLabel' => __('La carte')])

                {{-- ── EN-TÊTE + DESCRIPTION ──────────────────────────────── --}}
                @if($restaurant->daily_special && $restaurant->daily_special_date?->isToday())
                    <div class="rounded-2xl border border-[#EB6E00]/60 bg-[#F3F8F5] px-5 py-4 flex items-center gap-4">
                        <span class="w-11 h-11 rounded-xl bg-[#EB6E00] text-[#0E3B2A] flex items-center justify-center shrink-0"><i class="fas fa-bowl-food"></i></span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#EB6E00]">{{ __('Plat du jour') }}</p>
                            <p class="font-semibold">{{ $restaurant->daily_special }}@if($restaurant->daily_special_price_xof) · <span class="text-[#00874F]">{{ number_format((int) $restaurant->daily_special_price_xof, 0, ',', ' ') }} FCFA</span>@endif</p>
                        </div>
                    </div>
                @endif

                <section class="reveal" id="apercu">
                    @include('partials.detail-overview', ['model' => $restaurant])
                </section>

                {{-- ── ÉQUIPEMENTS ────────────────────────────────────────── --}}
                @if(!empty($restaurant->amenities))
                <section class="reveal" id="equipements">
                    <p class="section-kicker text-[#EB6E00] text-xs font-bold uppercase mb-2">{{ __('Confort') }}</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">{{ __('Équipements & services') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($restaurant->amenities as $am)
                        <div class="flex items-center gap-2.5 bento-card rounded-xl p-3">
                            <span class="hx-amen__icon"><i class="{{ $am['icon'] ?? 'fas fa-check' }}" aria-hidden="true"></i></span>
                            <span class="text-sm font-medium truncate">{{ $am['label'] ?? '' }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ── NOTRE CARTE ─────────────────────────────────────────── --}}
                @if($menuByCategory->isNotEmpty())
                <section class="reveal" id="offre">
                    <p class="section-kicker text-[#EB6E00] text-xs font-bold uppercase mb-2">{{ __('À déguster sur place') }}</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">{{ __('Notre carte') }}</h2>
                    <div class="menu-sheet">
                        @foreach($menuByCategory as $categoryName => $itemGroup)
                        <div class="menu-group">
                            <h3 class="menu-group__title">{{ $categoryName }}</h3>
                            <ul>
                                @foreach($itemGroup as $item)
                                <li class="menu-item{{ request('item') === $item->name ? ' is-picked' : '' }}">
                                    @if(!empty($item->images[0]))
                                    <img src="{{ $item->images[0] }}" alt="" class="menu-item__img" loading="lazy">
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="menu-item__line">
                                            <a href="{{ route('restaurant.show', $restaurant->slug) }}?item={{ urlencode($item->name) }}" class="menu-item__name" @if(request('item') === $item->name) aria-current="true" @endif>{{ $item->name }}</a>
                                            <span class="menu-item__dots" aria-hidden="true"></span>
                                            <span class="menu-item__price">{{ number_format((int) $item->price_xof, 0, ',', ' ') }}&nbsp;FCFA</span>
                                        </div>
                                        @if($item->description)
                                        <p class="menu-item__desc">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                        <a href="{{ route('restaurant.menu', $restaurant->slug) }}" class="menu-sheet__more">
                            {{ __('Voir la carte complète') }} <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </section>
                @endif

                {{-- ── LOCALISATION ─────────────────────────────────────────── --}}
                <section class="reveal" id="localisation">
                    <p class="section-kicker text-[#EB6E00] text-xs font-bold uppercase mb-2">{{ __('Où se trouve l\'établissement') }}</p>
                    <h2 class="font-serif text-2xl font-bold mb-4">{{ __('Localisation') }}</h2>
                    @if($restaurant->latitude && $restaurant->longitude)
                        @include('partials.route-map', ['type' => 'restaurant', 'slug' => $restaurant->slug, 'lat' => $restaurant->latitude, 'lng' => $restaurant->longitude, 'name' => $restaurant->name, 'accent' => '#EB6E00'])
                        <a href="{{ route('directions.go', ['restaurant', $restaurant->slug]) }}" data-in-page-route target="_blank" rel="noopener noreferrer"
                           class="dx-route mt-3"><i class="fas fa-diamond-turn-right" aria-hidden="true"></i> {{ __('Itinéraire') }}
                        </a>
                    @elseif($restaurant->city)
                        <div class="hx-map">
                            <iframe src="https://www.google.com/maps?q={{ urlencode($restaurant->city->name) }}&z=12&output=embed" title="{{ __('Carte : :name', ['name' => $restaurant->city->name]) }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            <div class="dx-map-card">
                                <p><span class="hx-facts__icon"><i class="fas fa-location-dot" aria-hidden="true"></i></span><span><small>{{ __('Adresse') }}</small>{{ $restaurant->adresse ?: $restaurant->city->name }}</span></p>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(trim($restaurant->name.' '.($restaurant->adresse ?: $restaurant->city->name).' Côte d\'Ivoire')) }}" target="_blank" rel="noopener noreferrer" class="dx-route"><i class="fas fa-diamond-turn-right" aria-hidden="true"></i>{{ __('Itinéraire') }}</a>
                            </div>
                        </div>
                    @else
                        <p class="text-[#557466] text-sm">{{ __('Coordonnées non renseignées pour cet établissement.') }}</p>
                    @endif
                </section>

                {{-- ── AVIS CLIENTS ──────────────────────────────────────────── --}}
                @if($reviews->isNotEmpty())
                <section class="reveal" id="avis">
                    @include('partials.detail-reviews', ['reviews' => $reviews, 'model' => $restaurant])
                </section>
                @endif
            </div>{{-- fin col principale --}}

            {{-- ═══════════════ COLONNE LATÉRALE ═══════════════ --}}
            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24 space-y-3 reveal">
                    @include('partials.booking-request-form', ['type' => 'restaurant', 'target' => $restaurant])
                    @include('partials.trust-badges', ['provider' => $restaurant->provider])
                    @include('partials.gift-card-cta', ['provider' => $restaurant->provider])
                    <div class="contact-card rounded-3xl p-5" id="contact-card">
                        <h2 class="font-serif text-xl font-bold mb-1">{{ __('Contacter cet établissement') }}</h2>
                        <p class="text-[#557466] text-xs mb-4">{{ __('Posez vos questions directement, en toute sécurité, sans quitter la plateforme.') }}</p>
                        {{-- Arrivée depuis « Contacter à ce sujet » (?item=…) : rappel de l'élément choisi. --}}
                        @if(is_string(request('item')) && request('item') !== '')
                        <div class="ti-contact-item">
                            <i class="fas fa-tag"></i>
                            <span>{{ __('Votre demande concerne') }} <strong>{{ str(request('item'))->limit(80) }}</strong></span>
                        </div>
                        @endif

                        @if($restaurant->provider)
                            @auth
                                @if(auth()->user()->role === 'visitor')
                                    <form method="POST" action="{{ route('visitor.conversations.store') }}" class="space-y-2.5">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $restaurant->provider->id }}">
                                        <textarea name="message" id="contact-message" rows="3" required maxlength="4000"
                                                  class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-[#8AA99B]"
                                                  placeholder="{{ __('Votre message...') }}"></textarea>
                                        <button type="submit" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                            <i class="fas fa-paper-plane text-sm"></i> {{ __('Envoyer le message') }}
                                        </button>
                                    </form>
                                @else
                                    <p class="dx-note"><i class="fas fa-circle-info mr-1" aria-hidden="true"></i>{{ __('La messagerie avec les établissements est réservée aux comptes visiteurs.') }}</p>
                                @endif
                            @else
                                <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-5 text-center">
                                    <p class="text-[#0E3B2A] font-bold text-sm mb-1">{{ __('Connectez-vous pour contacter cet établissement') }}</p>
                                    <div class="flex items-center justify-center gap-2 mt-3">
                                        <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                            <i class="fas fa-right-to-bracket text-xs"></i> {{ __('Se connecter') }}
                                        </a>
                                        <a href="{{ route('register', ['role' => 'visitor', 'redirect' => request()->getRequestUri()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-orange-400/50 text-[#0E3B2A] transition">
                                            {{ __('Créer un compte') }}
                                        </a>
                                    </div>
                                </div>
                            @endauth
                        @else
                            @if($restaurant->phone)
                            <a href="tel:{{ $restaurant->phone }}" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                <i class="fas fa-phone text-sm"></i> {{ $restaurant->phone }}
                            </a>
                            @else
                            <p class="text-[#557466] text-sm">{{ __('Aucun moyen de contact renseigné.') }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($related->isNotEmpty())
        <section class="mt-16 reveal">
            <p class="section-kicker text-[#EB6E00] text-xs font-bold uppercase mb-2">{{ __('À découvrir aussi') }}</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">{{ __('Établissements similaires') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($related as $r)
                    @include('partials.home-sector-card', ['it' => \App\Support\HomeSectorShowcase::card($r, 'restaurants')])
                @endforeach
            </div>
        </section>
        @endif
    </div>

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0E3B2A]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

    <div class="pb-4">
@php $cartDishes = $restaurant->provider_id && $restaurant->accepts_orders ? \App\Models\MenuItem::query()->available()->where('provider_id', $restaurant->provider_id)->with('category')->get() : collect(); @endphp
@include('partials.restaurant-cart', ['restaurant' => $restaurant, 'dishes' => $cartDishes])
@include('partials.homepage-footer')
    </div>
@include('partials.place-card-script')
@include('partials.image-protection')
<script>
function openLightbox(url) {
    document.getElementById('lightbox-img').src = url;
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Depuis la page dédiée : pré-remplit le message de contact (?item=...) ──
(function () {
    const item = new URLSearchParams(window.location.search).get('item');
    if (!item) return;
    const ta = document.getElementById('contact-message');
    if (ta) ta.value = {{ \Illuminate\Support\Js::from(__('Je suis intéressé par : ')) }} + item;
})();

// ── Révélation au défilement ─────────────────────────────────────────────
(function () {
    // Arrivée directe via ancre (#contact-card...) : le navigateur y saute instantanément,
    // souvent avant que l'IntersectionObserver n'ait eu la main — la section resterait
    // invisible (opacity:0) malgré le saut. On révèle tout d'un coup dans ce cas précis.
    if (window.location.hash) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }
    const items = document.querySelectorAll('.reveal:not(.is-visible)');
    if (!items.length) return;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    items.forEach((item) => observer.observe(item));
})();
</script>
</body>
</html>
