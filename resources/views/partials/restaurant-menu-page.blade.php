{{--
    Carte d'un restaurant, façon application de commande :
    en-tête de l'établissement, recherche, catégories qui suivent le défilement,
    plats en lignes (texte à gauche, photo à droite si elle existe), fiche du plat en panneau,
    récapitulatif du panier à droite sur grand écran.
    Paramètres : $restaurant, $groups (plats groupés par catégorie), $orderable (commande en ligne).
    Le panier reste celui de partials.restaurant-cart (boutons data-cart-*, évènement « ti-cart:change »).
--}}
@php
    $rmCover = \App\Support\HomeSectorShowcase::photos($restaurant)[0] ?? $restaurant->thumbnail;
    $rmTotal = $groups->flatten(1)->count();
    $rmWhere = collect([$restaurant->quartier, $restaurant->city?->name])->filter()->unique()->join(' · ');
    $rmShow = route('restaurant.show', $restaurant->slug);
    $rmRating = (float) ($restaurant->provider?->rating_avg ?? 0);
    $rmReviews = (int) ($restaurant->provider?->rating_count ?? 0);
    $rmToday = $restaurant->provider?->hours?->firstWhere('day_of_week', now()->dayOfWeek);
    $rmOrder = ! empty($orderable);
    $rmSpecial = $restaurant->daily_special && $restaurant->daily_special_date?->isToday();
    $rmSlug = fn ($items, $name) => $items->first()->category->slug ?? \Illuminate\Support\Str::slug($name);
    $rmFmt = fn ($v) => number_format((int) $v, 0, ',', ' ');
@endphp

<div class="rm max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <a href="{{ $rmShow }}" class="hx-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> {{ $restaurant->name }}</a>

    {{-- ═══ EN-TÊTE ═══ --}}
    <header class="rm-head">
        @if($rmCover)
            <img src="{{ $rmCover }}" alt="" class="rm-head__img" decoding="async">
        @else
            <span class="rm-head__img is-icon"><x-icon name="chef-hat" /></span>
        @endif
        <div class="rm-head__txt">
            <p class="rm-kicker">{{ __('La carte') }}</p>
            <h1 class="rm-title">{{ $restaurant->name }}</h1>
            <ul class="rm-facts">
                @if($rmRating > 0)
                    <li class="is-rate"><x-icon name="star" /><b>{{ number_format($rmRating, 1, ',', ' ') }}</b>@if($rmReviews > 0)<span>({{ trans_choice(':count avis|:count avis', $rmReviews) }})</span>@endif</li>
                @endif
                @if($rmWhere)<li><x-icon name="map-point" />{{ $rmWhere }}</li>@endif
                @if($rmToday)
                    <li>
                        <x-icon name="clock-circle" />
                        @if($rmToday->isOpen())
                            {{ __('Aujourd\'hui') }} {{ substr($rmToday->open_time, 0, 5) }} – {{ substr((string) $rmToday->close_time, 0, 5) }}
                        @else
                            {{ __('Fermé aujourd\'hui') }}
                        @endif
                    </li>
                @endif
                <li><x-icon name="chef-hat" />{{ trans_choice(':count plat|:count plats', $rmTotal) }}</li>
            </ul>
            <div class="rm-modes">
                @if($rmOrder)<span><x-icon name="bag" />{{ __('À emporter') }}</span>@endif
                @if($rmOrder && $restaurant->offers_delivery)<span><x-icon name="scooter" />{{ __('Livraison') }}</span>@endif
                @if($restaurant->provider_id)<span><x-icon name="chair" />{{ __('Sur place') }}</span>@endif
            </div>
        </div>
        <div class="rm-head__actions">
            <button type="button" class="ti-round" data-place-share="{{ url()->current() }}" data-place-title="{{ __('Notre carte') }} — {{ $restaurant->name }}" aria-label="{{ __('Partager :name', ['name' => $restaurant->name]) }}" title="{{ __('Partager') }}"><x-icon name="share" /></button>
            @if($restaurant->provider_id)
                <a href="{{ $rmShow }}#reserver" class="rm-btn is-line"><x-icon name="calendar" />{{ __('Réserver une table') }}</a>
            @endif
        </div>
    </header>

    @if($rmSpecial)
        <div class="rm-special">
            <span class="rm-special__tag">{{ __('Plat du jour') }}</span>
            <b>{{ $restaurant->daily_special }}</b>
            @if($restaurant->daily_special_price_xof)<span class="rm-special__price">{{ $rmFmt($restaurant->daily_special_price_xof) }} {{ __('FCFA') }}</span>@endif
        </div>
    @endif

    @if($groups->isNotEmpty())
    <div class="rm-layout{{ $rmOrder ? ' has-cart' : '' }}">
        {{-- ═══ CATÉGORIES (colonne sur ordinateur, bande sur mobile) ═══ --}}
        <nav class="rm-nav" aria-label="{{ __('Catégories de la carte') }}">
            <label class="rm-search">
                <x-icon name="magnifier" />
                <input type="search" data-rm-search placeholder="{{ __('Rechercher un plat') }}" aria-label="{{ __('Rechercher un plat') }}" autocomplete="off">
            </label>
            <ul class="rm-cats" data-rm-cats>
                @foreach($groups as $categoryName => $items)
                    <li><a href="#cat-{{ $rmSlug($items, $categoryName) }}" data-rm-link="{{ $rmSlug($items, $categoryName) }}">{{ $categoryName }}<span>{{ $items->count() }}</span></a></li>
                @endforeach
            </ul>
        </nav>

        {{-- ═══ PLATS ═══ --}}
        <div class="rm-main">
            @foreach($groups as $categoryName => $items)
                @php $catSlug = $rmSlug($items, $categoryName); @endphp
                <section class="rm-sec menu-category-block" id="cat-{{ $catSlug }}" data-cat="{{ $catSlug }}">
                    <h2 class="rm-sec__title">{{ $categoryName }} <small>{{ $items->count() }}</small></h2>
                    <ul class="rm-items">
                        @foreach($items as $item)
                            @php
                                $imgs = array_values(array_filter((array) ($item->images ?? []), fn ($u) => is_string($u) && $u !== ''));
                                $ask = $rmShow.'?item='.urlencode($item->name).'#contact-card';
                            @endphp
                            <li class="rm-item{{ $imgs ? ' has-img' : '' }}" data-rm-item
                                data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-cat="{{ $categoryName }}"
                                data-price="{{ $rmFmt($item->price_xof) }}" data-desc="{{ $item->description }}"
                                data-photos='@json($imgs)' data-ask="{{ $ask }}">
                                <button type="button" class="rm-item__open" data-rm-open aria-haspopup="dialog">
                                    <span class="rm-item__name">{{ $item->name }}</span>
                                    @if($item->description)<span class="rm-item__desc">{{ $item->description }}</span>@endif
                                    <span class="rm-item__price">{{ $rmFmt($item->price_xof) }} <small>{{ __('FCFA') }}</small></span>
                                </button>
                                @if($imgs)
                                    <img src="{{ $imgs[0] }}" alt="" class="rm-item__img" loading="lazy" decoding="async" data-rm-open>
                                @endif
                                @if($rmOrder)
                                    <div class="rm-qty">
                                        <button type="button" class="rm-qty__add" data-cart-add="{{ $item->id }}" aria-label="{{ __('Ajouter « :name » au panier', ['name' => $item->name]) }}"><i class="fas fa-plus" aria-hidden="true"></i></button>
                                        <div class="rm-qty__step">
                                            <button type="button" data-cart-step="{{ $item->id }}" data-step="-1" aria-label="{{ __('Un de moins') }} : {{ $item->name }}"><i class="fas fa-minus" aria-hidden="true"></i></button>
                                            <b data-cart-qty="{{ $item->id }}">0</b>
                                            <button type="button" data-cart-step="{{ $item->id }}" data-step="1" aria-label="{{ __('Un de plus') }} : {{ $item->name }}"><i class="fas fa-plus" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
            <p class="rm-none" data-rm-none hidden><x-icon name="magnifier" />{{ __('Aucun plat ne correspond à votre recherche.') }}</p>
        </div>

        {{-- ═══ PANIER (grand écran) ═══ --}}
        @if($rmOrder)
        <aside class="rm-side" aria-label="{{ __('Votre commande') }}">
            <div class="rm-cart" data-rm-cart>
                <p class="rm-cart__title">{{ __('Votre commande') }}</p>
                <div class="rm-cart__empty" data-rm-cart-empty>
                    <x-icon name="bag" />
                    <p>{{ __('Ajoutez des plats avec le bouton +, puis choisissez à emporter ou en livraison.') }}</p>
                </div>
                <div class="rm-cart__full" data-rm-cart-full hidden>
                    <p class="rm-cart__line"><span data-rm-cart-count>0</span><b><span data-rm-cart-total>0</span> {{ __('FCFA') }}</b></p>
                    <button type="button" class="rm-btn is-main" data-cart-open>{{ __('Voir le panier') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                </div>
            </div>
            <a href="{{ $rmShow }}#contact-card" class="rm-side__link"><x-icon name="chat-round-dots" />{{ __('Une question sur un plat ?') }}</a>
        </aside>
        @endif
    </div>
    @else
        <div class="mn-empty">
            <x-icon name="chef-hat" />
            <p class="m-0 text-sm">{{ __('Aucun plat renseigné pour cet établissement pour le moment.') }}</p>
        </div>
    @endif
</div>

{{-- ═══ FICHE DU PLAT (panneau) ═══ --}}
<div class="rm-sheet" data-rm-sheet hidden>
    <div class="rm-sheet__overlay" data-rm-close></div>
    <div class="rm-sheet__box" role="dialog" aria-modal="true" aria-labelledby="rm-sheet-name" tabindex="-1">
        <button type="button" class="rm-sheet__close" data-rm-close aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        <div class="rm-sheet__media" data-rm-media hidden>
            <img src="" alt="" data-rm-img>
            <button type="button" class="rm-sheet__nav is-prev" data-rm-step="-1" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
            <button type="button" class="rm-sheet__nav is-next" data-rm-step="1" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            <span class="rm-sheet__count" data-rm-count></span>
        </div>
        <div class="rm-sheet__body">
            <p class="rm-kicker" data-rm-cat></p>
            <h2 class="rm-sheet__name" id="rm-sheet-name" data-rm-name></h2>
            <p class="rm-sheet__price"><span data-rm-price></span> {{ __('FCFA') }}</p>
            <p class="rm-sheet__desc" data-rm-desc></p>
            <div class="rm-sheet__foot">
                @if($rmOrder)
                    <div class="rm-sheet__qty">
                        <button type="button" data-rm-sheet-step="-1" aria-label="{{ __('Un de moins') }}"><i class="fas fa-minus" aria-hidden="true"></i></button>
                        <b data-rm-sheet-qty>0</b>
                        <button type="button" data-rm-sheet-step="1" aria-label="{{ __('Un de plus') }}"><i class="fas fa-plus" aria-hidden="true"></i></button>
                    </div>
                @endif
                <a href="#" class="rm-btn is-line" data-rm-ask><x-icon name="chat-round-dots" />{{ __('Contacter à ce sujet') }}</a>
            </div>
        </div>
    </div>
</div>

<style>
    .rm { --rm-top: 84px; }
    .rm-kicker { margin: 0; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #b9500a; }

    /* En-tête */
    .rm-head { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 22px; align-items: center; margin: 18px 0 22px; }
    .rm-head__img { width: 112px; height: 112px; border-radius: 26px; object-fit: cover; background: #f3eee4; }
    .rm-head__img.is-icon { display: inline-flex; align-items: center; justify-content: center; color: #b9500a; font-size: 34px; background: #fdf4ec; }
    .rm-title { margin: 4px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(30px, 2.4vw + 18px, 46px); font-weight: 700; line-height: 1.08; color: #1d1a16; letter-spacing: -.01em; }
    .rm-facts { display: flex; flex-wrap: wrap; gap: 6px 18px; margin: 12px 0 0; padding: 0; list-style: none; font-size: 14px; color: #4a443a; }
    .rm-facts li { display: inline-flex; align-items: center; gap: 7px; }
    .rm-facts i { color: #b9500a; font-size: 15px; }
    .rm-facts .is-rate i { color: #c98a0b; }
    .rm-facts .is-rate b { color: #1d1a16; }
    .rm-facts .is-rate span { color: #6b6458; }
    .rm-modes { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
    .rm-modes span { display: inline-flex; align-items: center; gap: 7px; height: 30px; padding: 0 12px; border-radius: 999px; background: #eef6f0; color: #1b6b3a; font-size: 12.5px; font-weight: 700; }
    .rm-head__actions { display: flex; gap: 8px; align-items: center; align-self: start; }

    .rm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 46px; padding: 0 20px; border-radius: 999px; font-size: 14px; font-weight: 700; border: 0; cursor: pointer; text-decoration: none; transition: background .15s ease, border-color .15s ease, color .15s ease; }
    .rm-btn.is-main { width: 100%; background: #b9500a; color: #fff; }
    .rm-btn.is-main:hover { background: #9a4308; color: #fff; }
    .rm-btn.is-line { background: #fff; color: #1d1a16; border: 1.5px solid #e3dccb; }
    .rm-btn.is-line:hover { border-color: #b9500a; color: #9a4308; }
    .rm-btn i { font-size: 16px; }

    .rm-special { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; margin-bottom: 22px; padding: 14px 18px; border-radius: 18px; background: #fff8e6; border: 1px solid #f4dc9f; font-size: 15px; color: #1d1a16; }
    .rm-special__tag { font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #9a4308; }
    .rm-special__price { margin-left: auto; font-weight: 800; color: #1b6b3a; }

    /* Mise en page : catégories | plats | panier */
    .rm-layout { display: grid; grid-template-columns: 210px minmax(0, 1fr); gap: 36px; align-items: start; padding-top: 22px; border-top: 1px solid #e6dccb; }
    .rm-layout.has-cart { grid-template-columns: 200px minmax(0, 1fr) 280px; gap: 32px; }
    .rm-nav { position: sticky; top: var(--rm-top); }
    .rm-search { display: flex; align-items: center; gap: 10px; height: 44px; padding: 0 14px; border-radius: 14px; background: #fff; border: 1px solid #e3dccb; color: #8a7f6b; }
    .rm-search:focus-within { border-color: #b9500a; outline: 2px solid rgba(239,124,43,.35); }
    .rm-search input { flex: 1; min-width: 0; border: 0; outline: 0; background: transparent; font-size: 14px; color: #1d1a16; }
    .rm-cats { margin: 14px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 2px; }
    .rm-cats li[hidden] { display: none; }
    .rm-cats a { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: 12px; font-size: 14.5px; font-weight: 600; color: #4a443a; text-decoration: none; transition: background .15s ease, color .15s ease; }
    .rm-cats a span { font-size: 12px; font-weight: 600; color: #8a7f6b; }
    .rm-cats a:hover { background: #f3eee4; color: #1d1a16; }
    .rm-cats a.is-active { background: #1d1a16; color: #fff; }
    .rm-cats a.is-active span { color: rgba(255,255,255,.7); }
    .rm-cats a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 1px; }

    .rm-sec { scroll-margin-top: calc(var(--rm-top) + 12px); }
    .rm-sec[hidden] { display: none; }
    .rm-sec + .rm-sec { margin-top: 34px; }
    .rm-sec__title { margin: 0 0 12px; font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #1d1a16; }
    .rm-sec__title small { font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #8a7f6b; margin-left: 4px; }
    .rm-items { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .rm-layout.has-cart .rm-items { grid-template-columns: 1fr; }

    /* Plat : texte à gauche, photo (facultative) à droite, bouton + en bas à droite */
    .rm-item { position: relative; display: flex; gap: 14px; min-height: 124px; padding: 16px; padding-right: 64px; border-radius: 20px; background: #fff; border: 1px solid #ece5d6; transition: border-color .15s ease, box-shadow .2s ease; }
    .rm-item.has-img { padding-right: 16px; }
    .rm-item:hover { border-color: #d8cdb8; box-shadow: 0 14px 30px -24px rgba(29,26,22,.45); }
    .rm-item[hidden] { display: none; }
    .rm-item__open { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: flex-start; gap: 4px; padding: 0; border: 0; background: none; text-align: left; cursor: pointer; color: inherit; font: inherit; }
    .rm-item__open:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 6px; border-radius: 8px; }
    .rm-item__name { font-size: 16.5px; font-weight: 700; line-height: 1.3; color: #1d1a16; }
    .rm-item__desc { font-size: 13.5px; line-height: 1.5; color: #6b6458; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .rm-item__price { margin-top: auto; padding-top: 8px; font-size: 15px; font-weight: 800; color: #1d1a16; font-variant-numeric: tabular-nums; }
    .rm-item__price small { font-size: 11px; font-weight: 600; color: #8a7f6b; }
    .rm-item__img { flex: none; width: 112px; height: 112px; margin: -6px -6px -6px 0; border-radius: 14px; object-fit: cover; cursor: pointer; background: #f3eee4; }

    .rm-qty { position: absolute; right: 12px; bottom: 12px; }
    .rm-qty__add { width: 40px; height: 40px; border: 0; border-radius: 999px; background: #1d1a16; color: #fff; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: background .15s ease, transform .15s ease; }
    .rm-qty__add:hover { background: #b9500a; }
    .rm-qty__add:active { transform: scale(.92); }
    .rm-item.has-img .rm-qty__add { border: 3px solid #fff; width: 44px; height: 44px; }
    .rm-qty__step { display: none; align-items: center; gap: 2px; height: 40px; padding: 3px; border-radius: 999px; background: #1d1a16; color: #fff; }
    .rm-qty__step button { width: 34px; height: 34px; border: 0; border-radius: 999px; background: transparent; color: #fff; font-size: 12px; cursor: pointer; }
    .rm-qty__step button:hover { background: #b9500a; }
    .rm-qty__step b { min-width: 22px; text-align: center; font-size: 14px; font-variant-numeric: tabular-nums; }
    .rm-item.is-in { border-color: #1b6b3a; }
    .rm-item.is-in .rm-qty__add { display: none; }
    .rm-item.is-in .rm-qty__step { display: inline-flex; background: #1b6b3a; }
    .rm-qty button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

    .rm-none { display: flex; align-items: center; gap: 10px; padding: 26px; border-radius: 20px; background: #fff; border: 1px dashed #d8cdb8; color: #6b6458; font-size: 14px; }
    .rm-none[hidden] { display: none; }

    /* Panier (grand écran) */
    .rm-side { position: sticky; top: var(--rm-top); display: flex; flex-direction: column; gap: 12px; }
    .rm-cart { padding: 20px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
    .rm-cart__title { margin: 0 0 12px; font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #1d1a16; }
    .rm-cart__empty { text-align: center; padding: 10px 4px 4px; }
    .rm-cart__empty i { width: 54px; height: 54px; border-radius: 999px; background: #f6f3ec; color: #8a7f6b; display: inline-flex; align-items: center; justify-content: center; font-size: 19px; }
    .rm-cart__empty p { margin: 12px 0 0; font-size: 13.5px; line-height: 1.55; color: #6b6458; }
    .rm-cart__full[hidden], .rm-cart__empty[hidden] { display: none; }
    .rm-cart__line { display: flex; justify-content: space-between; align-items: baseline; margin: 0 0 14px; font-size: 14px; color: #4a443a; }
    .rm-cart__line b { font-size: 20px; color: #1d1a16; font-variant-numeric: tabular-nums; }
    .rm-side__link { display: inline-flex; align-items: center; gap: 8px; padding: 0 6px; font-size: 13.5px; font-weight: 600; color: #4a443a; text-decoration: underline; text-decoration-color: #d8cdb8; text-underline-offset: 3px; }
    .rm-side__link:hover { color: #9a4308; }
    .rm-side__link i { color: #b9500a; font-size: 16px; }
    /* Sur grand écran le récapitulatif remplace le bouton flottant. */
    @media (min-width: 1200px) { .rm-page-cart .tc-fab { display: none !important; } }

    /* Fiche du plat */
    .rm-sheet { position: fixed; inset: 0; z-index: 90; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .rm-sheet[hidden] { display: none; }
    .rm-sheet__overlay { position: absolute; inset: 0; background: rgba(20,18,14,.55); }
    .rm-sheet__box { position: relative; width: min(560px, 100%); max-height: calc(100vh - 40px); overflow-y: auto; border-radius: 26px; background: #fffdf9; animation: rm-in .22s ease; outline: 0; }
    @keyframes rm-in { from { opacity: 0; transform: translateY(14px); } }
    .rm-sheet__close { position: absolute; top: 14px; right: 14px; z-index: 2; width: 40px; height: 40px; border: 0; border-radius: 999px; background: #fff; color: #1d1a16; cursor: pointer; box-shadow: 0 1px 4px rgba(29,26,22,.2); }
    .rm-sheet__close:hover { background: #1d1a16; color: #fff; }
    .rm-sheet__media { position: relative; aspect-ratio: 4 / 3; background: #f3eee4; }
    .rm-sheet__media[hidden] { display: none; }
    .rm-sheet__media img { width: 100%; height: 100%; object-fit: cover; }
    .rm-sheet__nav { position: absolute; top: 50%; width: 38px; height: 38px; margin-top: -19px; border: 0; border-radius: 999px; background: #fff; color: #1d1a16; cursor: pointer; }
    .rm-sheet__nav[hidden], .rm-sheet__count[hidden] { display: none; }
    .rm-sheet__nav.is-prev { left: 12px; } .rm-sheet__nav.is-next { right: 12px; }
    .rm-sheet__nav:hover { background: #b9500a; color: #fff; }
    .rm-sheet__count { position: absolute; left: 12px; bottom: 12px; padding: 4px 10px; border-radius: 999px; background: rgba(29,26,22,.72); color: #fff; font-size: 12px; font-weight: 700; }
    .rm-sheet__body { padding: 24px 26px 26px; }
    .rm-sheet__media[hidden] + .rm-sheet__body { padding-top: 30px; padding-right: 64px; }
    .rm-sheet__name { margin: 6px 0 0; font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; line-height: 1.15; color: #1d1a16; }
    .rm-sheet__price { margin: 8px 0 0; font-size: 18px; font-weight: 800; color: #9a4308; }
    .rm-sheet__desc { margin: 14px 0 0; font-size: 15px; line-height: 1.65; color: #4a443a; }
    .rm-sheet__desc:empty { display: none; }
    .rm-sheet__foot { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 22px; padding-top: 18px; border-top: 1px solid #ece5d6; }
    .rm-sheet__qty { display: inline-flex; align-items: center; gap: 4px; height: 46px; padding: 4px; border-radius: 999px; background: #1d1a16; color: #fff; }
    .rm-sheet__qty button { width: 38px; height: 38px; border: 0; border-radius: 999px; background: transparent; color: #fff; cursor: pointer; }
    .rm-sheet__qty button:hover { background: #b9500a; }
    .rm-sheet__qty b { min-width: 26px; text-align: center; font-size: 16px; }
    .rm-sheet__qty.is-in { background: #1b6b3a; }
    .rm-sheet button:focus-visible, .rm-sheet a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

    @media (max-width: 1199px) {
        .rm-layout.has-cart { grid-template-columns: 200px minmax(0, 1fr); }
        .rm-side { display: none; }
    }
    @media (max-width: 899px) {
        .rm-items, .rm-layout.has-cart .rm-items { grid-template-columns: 1fr; }
    }
    @media (max-width: 767px) {
        .rm { --rm-top: 64px; }
        .rm-head { grid-template-columns: auto minmax(0, 1fr); gap: 14px; margin-top: 14px; }
        .rm-head__img { width: 72px; height: 72px; border-radius: 18px; align-self: start; }
        .rm-head__actions { grid-column: 1 / -1; }
        .rm-head__actions .rm-btn { flex: 1; }
        .rm-layout, .rm-layout.has-cart { grid-template-columns: minmax(0, 1fr); gap: 16px; padding-top: 0; border-top: 0; }
        /* Bande collante : recherche + catégories qui défilent horizontalement */
        .rm-nav { z-index: 20; margin: 0 -16px; padding: 10px 16px; background: #f6f3ed; border-bottom: 1px solid #e6dccb; }
        .rm-cats { flex-direction: row; gap: 6px; margin-top: 10px; overflow-x: auto; scrollbar-width: none; }
        .rm-cats::-webkit-scrollbar { display: none; }
        .rm-cats a { flex: none; padding: 8px 14px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; font-size: 13.5px; white-space: nowrap; }
        .rm-cats a.is-active { border-color: #1d1a16; }
        .rm-sec { scroll-margin-top: calc(var(--rm-top) + 120px); }
        .rm-sec__title { font-size: 22px; }
        .rm-item { min-height: 112px; padding: 14px; padding-right: 58px; }
        .rm-item.has-img { padding-right: 14px; }
        .rm-item__img { width: 96px; height: 96px; }
        .rm-special__price { margin-left: 0; }
        /* Fiche du plat en panneau du bas */
        .rm-sheet { align-items: flex-end; padding: 0; }
        .rm-sheet__box { width: 100%; max-height: 90vh; border-radius: 26px 26px 0 0; padding-bottom: env(safe-area-inset-bottom, 0); }
        .rm-sheet__body { padding: 22px 20px 24px; }
        .rm-sheet__foot .rm-btn { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { .rm-sheet__box { animation: none; } }

    /* Boutons aux couleurs de la marque (orange). */
    .rm-cats a.is-active { background: #b9500a; color: #fff; }
    .rm-qty__add { background: #b9500a; } .rm-qty__add:hover { background: #9a4308; }
    .rm-qty__step { background: #b9500a; } .rm-qty__step button:hover { background: #9a4308; }
    .rm-sheet__qty { background: #b9500a; } .rm-sheet__qty button:hover { background: #9a4308; }
    .rm-sheet__close:hover { background: #b9500a; color: #fff; }
    .rm-btn.is-line { border-color: #b9500a; color: #9a4308; } .rm-btn.is-line:hover { background: #fdf4ec; }
</style>

<script>
(function () {
    const page = document.querySelector('.rm');
    if (!page) return;
    document.body.classList.add('rm-page-cart');
    const items = Array.from(document.querySelectorAll('[data-rm-item]'));
    const sections = Array.from(document.querySelectorAll('.rm-sec'));
    const links = Array.from(document.querySelectorAll('[data-rm-link]'));
    const catsBox = document.querySelector('[data-rm-cats]');
    const sheet = document.querySelector('[data-rm-sheet]');
    const qtyOf = id => { const el = document.querySelector('.rm-main [data-cart-qty="' + id + '"]'); return el ? Number(el.textContent) || 0 : 0; };
    let current = null;

    function syncSheet() {
        const qtyBox = sheet.querySelector('.rm-sheet__qty');
        if (!current || !qtyBox) return;
        const q = qtyOf(current.dataset.id);
        sheet.querySelector('[data-rm-sheet-qty]').textContent = q;
        qtyBox.classList.toggle('is-in', q > 0);
    }

    // ── Panier : état « dans le panier » des plats + récapitulatif latéral ──
    document.addEventListener('ti-cart:change', function (e) {
        items.forEach(li => li.classList.toggle('is-in', qtyOf(li.dataset.id) > 0));
        const n = e.detail ? e.detail.count : 0, t = e.detail ? e.detail.total : 0;
        const empty = document.querySelector('[data-rm-cart-empty]'), full = document.querySelector('[data-rm-cart-full]');
        if (empty && full) {
            empty.hidden = n > 0; full.hidden = n === 0;
            document.querySelector('[data-rm-cart-count]').textContent = n + ' ' + (n > 1 ? {{ \Illuminate\Support\Js::from(__('plats')) }} : {{ \Illuminate\Support\Js::from(__('plat')) }});
            document.querySelector('[data-rm-cart-total]').textContent = Number(t).toLocaleString('fr-FR');
        }
        syncSheet();
    });

    // ── Catégorie active selon le défilement ──
    function setActive(slug) {
        links.forEach(a => {
            const on = a.dataset.rmLink === slug;
            a.classList.toggle('is-active', on);
            if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
            if (on && catsBox && catsBox.scrollWidth > catsBox.clientWidth) {
                catsBox.scrollTo({ left: a.offsetLeft - 16, behavior: 'smooth' });
            }
        });
    }
    if ('IntersectionObserver' in window && sections.length) {
        const visible = new Map();
        const io = new IntersectionObserver(entries => {
            entries.forEach(en => visible.set(en.target, en.isIntersecting));
            const first = sections.find(s => visible.get(s) && !s.hidden);
            if (first) setActive(first.dataset.cat);
        }, { rootMargin: '-30% 0px -60% 0px' });
        sections.forEach(s => io.observe(s));
    }
    if (sections[0]) setActive(sections[0].dataset.cat);

    // ── Recherche : filtre les plats par nom ou description (sans tenir compte des accents) ──
    const norm = s => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const search = document.querySelector('[data-rm-search]');
    const none = document.querySelector('[data-rm-none]');
    if (search) search.addEventListener('input', function () {
        const q = norm(search.value.trim());
        let shown = 0;
        sections.forEach(sec => {
            let n = 0;
            sec.querySelectorAll('[data-rm-item]').forEach(li => {
                const ok = !q || norm(li.dataset.name + ' ' + li.dataset.desc).includes(q);
                li.hidden = !ok; if (ok) n++;
            });
            sec.hidden = n === 0; shown += n;
            const link = links.find(a => a.dataset.rmLink === sec.dataset.cat);
            if (link) link.parentElement.hidden = n === 0;
        });
        none.hidden = shown > 0;
    });

    // ── Fiche du plat ──
    if (!sheet) return;
    const box = sheet.querySelector('.rm-sheet__box');
    const media = sheet.querySelector('[data-rm-media]');
    const img = sheet.querySelector('[data-rm-img]');
    const count = sheet.querySelector('[data-rm-count]');
    let photos = [], idx = 0, lastFocus = null;

    function showPhoto() {
        img.src = photos[idx] || '';
        const many = photos.length > 1;
        sheet.querySelectorAll('[data-rm-step]').forEach(b => b.hidden = !many);
        count.hidden = !many; count.textContent = (idx + 1) + ' / ' + photos.length;
    }
    function openSheet(li) {
        current = li; lastFocus = document.activeElement;
        photos = JSON.parse(li.dataset.photos || '[]'); idx = 0;
        media.hidden = photos.length === 0; img.alt = li.dataset.name;
        if (photos.length) showPhoto();
        sheet.querySelector('[data-rm-cat]').textContent = li.dataset.cat;
        sheet.querySelector('[data-rm-name]').textContent = li.dataset.name;
        sheet.querySelector('[data-rm-price]').textContent = li.dataset.price;
        sheet.querySelector('[data-rm-desc]').textContent = li.dataset.desc;
        sheet.querySelector('[data-rm-ask]').href = li.dataset.ask;
        syncSheet();
        sheet.hidden = false; document.body.style.overflow = 'hidden';
        box.focus();
    }
    function closeSheet() {
        sheet.hidden = true; document.body.style.overflow = ''; current = null;
        if (lastFocus) lastFocus.focus();
    }
    document.addEventListener('click', function (e) {
        const opener = e.target.closest('[data-rm-open]');
        if (opener) { const li = opener.closest('[data-rm-item]'); if (li) openSheet(li); return; }
        if (sheet.hidden) return;
        if (e.target.closest('[data-rm-close]')) { closeSheet(); return; }
        const step = e.target.closest('[data-rm-step]');
        if (step) { idx = (idx + Number(step.dataset.rmStep) + photos.length) % photos.length; showPhoto(); return; }
        const qs = e.target.closest('[data-rm-sheet-step]');
        if (qs && current && window.TiCart) {
            window.TiCart.set(current.dataset.id, qtyOf(current.dataset.id) + Number(qs.dataset.rmSheetStep));
        }
    });
    document.addEventListener('keydown', function (e) {
        if (sheet.hidden) return;
        if (e.key === 'Escape') closeSheet();
        if (e.key === 'ArrowLeft' && photos.length > 1) { idx = (idx - 1 + photos.length) % photos.length; showPhoto(); }
        if (e.key === 'ArrowRight' && photos.length > 1) { idx = (idx + 1) % photos.length; showPhoto(); }
    });
})();
</script>
