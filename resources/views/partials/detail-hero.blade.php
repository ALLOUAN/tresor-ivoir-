{{--
    En-tête des fiches établissement (hors hébergements) : retour, secteur, nom, lieu, note,
    partage / favori / réserver, puis galerie en mosaïque (photo principale + 4 vignettes).
    Paramètres : $model (établissement), $sectorName, $sectorIcon, $backUrl, $shareUrl,
    $bookAnchor (ancre du module de réservation, ex. « #reserver »),
    $orderable / $menuUrl (optionnels) : bouton panier et lien « carte complète » de l'élément choisi,
    $items (optionnel) : plats, activités, circuits ou offres dont les photos complètent la galerie
    (celles de l'élément demandé via ?item=… passent en premier, comme les chambres d'un hôtel).
    La loupe s'appuie sur la fonction openLightbox() de la page.
--}}
@php
    $dxItems = collect($items ?? []);
    $dxItemPhotos = fn ($it) => array_values(array_filter((array) ($it->images ?? []), fn ($u) => is_string($u) && $u !== ''));
    $dxSelected = is_string(request('item')) ? $dxItems->first(fn ($it) => $it->name === request('item')) : null;
    $dxOwn = \App\Support\HomeSectorShowcase::photos($model);
    // Élément choisi (?item=…) : uniquement SES photos (jamais celles d'un autre plat sous son nom) ;
    // sans photo, celles de l'établissement. Sans élément choisi : établissement puis tous les éléments.
    $dxPhotos = collect(match (true) {
        $dxSelected && $dxItemPhotos($dxSelected) => $dxItemPhotos($dxSelected),
        (bool) $dxSelected => $dxOwn,
        default => collect($dxOwn)->merge($dxItems->flatMap($dxItemPhotos))->all(),
    })->unique()->values()->all();
    $dxLabel = $dxSelected && $dxItemPhotos($dxSelected) ? $dxSelected->name : null;
    $dxRating = (float) ($model->provider?->rating_avg ?? 0);
    $dxReviews = (int) ($model->provider?->rating_count ?? 0);
    $dxWhere = collect([$model->quartier ?? null, $model->city?->name, $model->city?->region_administrative])->filter()->unique()->join(' · ');
@endphp

<a href="{{ $backUrl }}" class="hx-back">
    <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ $sectorName }}
</a>

<header class="hx-head reveal is-visible">
    <div class="min-w-0">
        <div class="hx-pills">
            <span class="hx-pill"><i class="fas {{ $sectorIcon }}" aria-hidden="true"></i>{{ $sectorName }}</span>
            @if($model->provider?->is_verified)
                <span class="hx-pill is-ok"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ __('Vérifié') }}</span>
            @endif
        </div>
        {{-- Élément choisi (?item=…) : son nom en titre, l'établissement en sous-titre. --}}
        <h1 class="hx-title">{{ $dxSelected?->name ?? $model->name }}</h1>
        <p class="hx-meta">
            @if($dxSelected)
                <span><i class="fas fa-store" aria-hidden="true"></i>{{ $model->name }}</span>
                @if((int) ($dxSelected->price_xof ?? 0) > 0)
                    <span class="hx-rate"><i class="fas fa-tag" aria-hidden="true"></i>{{ number_format((int) $dxSelected->price_xof, 0, ',', ' ') }} {{ __('FCFA') }}</span>
                @endif
            @endif
            @if($dxWhere)<span><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $dxWhere }}</span>@endif
            @if($dxRating > 0)
                <span class="hx-rate"><i class="fas fa-star" aria-hidden="true"></i>{{ number_format($dxRating, 1, ',', ' ') }}@if($dxReviews > 0)<small>({{ trans_choice(':count avis|:count avis', $dxReviews) }})</small>@endif</span>
            @else
                <span class="hx-rate is-new"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ __('Nouveau') }}</span>
            @endif
        </p>
    </div>
    <div class="hx-actions">
        <button type="button" class="ti-round" data-place-share="{{ $shareUrl }}" data-place-title="{{ $model->name }}" aria-label="{{ __('Partager :name', ['name' => $model->name]) }}" title="{{ __('Partager') }}"><i class="fas fa-share-nodes" aria-hidden="true"></i></button>
        @include('partials.place-card-fav', ['providerId' => $model->provider_id, 'name' => $model->name, 'favClass' => 'ti-round'])
        <a href="{{ $bookAnchor }}" class="ti-cta" data-dx-anchor>{{ __('Réserver') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</header>

{{-- Élément choisi (?item=…) : sa description et ses actions, juste sous le titre. --}}
@if($dxSelected)
@php
    $dxSiblings = $dxItems->filter(fn ($it) => $it->id !== $dxSelected->id && $dxSelected->category_id && $it->category_id === $dxSelected->category_id)->take(4);
    if ($dxSiblings->isEmpty()) {
        $dxSiblings = $dxItems->filter(fn ($it) => $it->id !== $dxSelected->id)->take(4);
    }
@endphp
<div class="hx-pick reveal is-visible" data-pick="{{ $dxSelected->id }}">
    <div class="hx-pick__main">
        <div class="hx-pick__body">
            <p class="hx-pick__kicker">{{ $dxSelected->category?->name ?? __('Sélection') }} · {{ $model->name }}</p>
            @if($dxSelected->description)
                <p class="hx-pick__desc">{{ $dxSelected->description }}</p>
            @endif
            <div class="hx-pick__links">
                @if(! empty($menuUrl))
                    <a href="{{ $menuUrl }}" class="hx-pick__link"><x-icon name="book" />{{ __('Voir la carte complète') }}</a>
                @endif
                <a href="{{ $shareUrl }}" class="hx-pick__link"><x-icon name="shop" />{{ $model->name }}</a>
            </div>
        </div>
        <div class="hx-pick__buy">
            @if((int) ($dxSelected->price_xof ?? 0) > 0)
                <p class="hx-pick__price">{{ number_format((int) $dxSelected->price_xof, 0, ',', ' ') }} <small>{{ __('FCFA') }}</small></p>
            @endif
            @if(! empty($orderable))
                <button type="button" class="hx-pick__add" data-cart-add="{{ $dxSelected->id }}" aria-label="{{ __('Ajouter « :name » au panier', ['name' => $dxSelected->name]) }}">
                    <i class="fas fa-plus" aria-hidden="true"></i><b data-cart-add-label>{{ __('Ajouter') }}</b>
                </button>
                <div class="hx-pick__step">
                    <button type="button" data-cart-step="{{ $dxSelected->id }}" data-step="-1" aria-label="{{ __('Un de moins') }}"><i class="fas fa-minus" aria-hidden="true"></i></button>
                    <b data-cart-qty="{{ $dxSelected->id }}">0</b>
                    <button type="button" data-cart-step="{{ $dxSelected->id }}" data-step="1" aria-label="{{ __('Un de plus') }}"><i class="fas fa-plus" aria-hidden="true"></i></button>
                </div>
                <button type="button" class="hx-pick__cart" data-cart-open>{{ __('Voir le panier') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></button>
            @endif
        </div>
    </div>
    @if($dxSiblings->isNotEmpty())
        <div class="hx-pick__more">
            <p class="hx-pick__more-title">{{ __('À goûter aussi') }}</p>
            <ul>
                @foreach($dxSiblings as $sib)
                    <li><a href="{{ $shareUrl }}?item={{ urlencode($sib->name) }}">
                        <span>{{ $sib->name }}</span>
                        @if((int) ($sib->price_xof ?? 0) > 0)<small>{{ number_format((int) $sib->price_xof, 0, ',', ' ') }} {{ __('FCFA') }}</small>@endif
                    </a></li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@if(! empty($orderable))
<script>
document.addEventListener('ti-cart:change', function () {
    document.querySelectorAll('[data-pick]').forEach(function (box) {
        const q = box.querySelector('[data-cart-qty]');
        box.classList.toggle('is-in', !!q && Number(q.textContent) > 0);
    });
});
</script>
@endif
@endif

{{-- Galerie « fiche produit » : bande de vignettes (verticale sur ordinateur, horizontale sur mobile) + grande image.
     Sans aucune photo, pas de grand cadre vide. --}}
@if($dxPhotos)
<div class="hx-gal {{ count($dxPhotos) <= 1 ? 'is-single' : '' }} {{ empty($dxPhotos) ? 'is-empty' : '' }} reveal is-visible" id="dx-gallery" data-photos='@json($dxPhotos)' tabindex="-1">
    @if(count($dxPhotos) > 1)
        <div class="hx-gal__thumbs" aria-label="{{ __('Photos de :name', ['name' => $model->name]) }}">
            @foreach($dxPhotos as $i => $url)
                <button type="button" class="hx-gal__thumb {{ $i === 0 ? 'is-active' : '' }}" data-dx-show="{{ $i }}"
                        aria-label="{{ __('Voir la photo :n', ['n' => $i + 1]) }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}">
                    <img src="{{ $url }}" alt="" loading="lazy" decoding="async">
                </button>
            @endforeach
        </div>
    @endif

    <div class="hero-cover hx-gal__main">
        @if($dxLabel)
            <span class="hx-gallery__room"><i class="fas {{ $sectorIcon }}" aria-hidden="true"></i>{{ __('Photos : :room', ['room' => $dxLabel]) }}</span>
        @endif
        @if($dxPhotos)
            <img id="dx-main-img" src="{{ $dxPhotos[0] }}" alt="{{ $model->name }}" class="w-full h-full object-cover cursor-zoom-in" decoding="async"
                 onclick="if (window.openLightbox) openLightbox(this.currentSrc || this.src)">
            <div class="hx-gal__tools">
                @include('partials.place-card-fav', ['providerId' => $model->provider_id, 'name' => $model->name, 'favClass' => 'hx-gal__tool'])
                <button type="button" class="hx-gal__tool" onclick="const i = document.getElementById('dx-main-img'); if (window.openLightbox) openLightbox(i.currentSrc || i.src)" aria-label="{{ __('Agrandir la photo') }}" title="{{ __('Agrandir la photo') }}"><i class="fas fa-expand" aria-hidden="true"></i></button>
            </div>
            @if(count($dxPhotos) > 1)
                <button type="button" class="hx-arrow is-prev" data-dx-slide="-1" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                <button type="button" class="hx-arrow is-next" data-dx-slide="1" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                <span class="hx-gallery__count"><i class="fas fa-images" aria-hidden="true"></i><span id="dx-count">1</span>&nbsp;/&nbsp;{{ count($dxPhotos) }}</span>
            @endif
        @else
            <div class="hx-gallery__empty"><i class="fas {{ $sectorIcon }}" aria-hidden="true"></i></div>
        @endif
    </div>
</div>
@endif

<script>
(function () {
    const box = document.getElementById('dx-gallery');
    const img = document.getElementById('dx-main-img');
    // Bouton « Réserver » : masqué si la fiche n'a pas de module de réservation.
    // Le module est plus bas dans la page : vérification une fois le document chargé.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-dx-anchor]').forEach(function (a) {
            const id = (a.getAttribute('href') || '').slice(1);
            if (!id || !document.getElementById(id)) a.remove();
        });
    });
    if (!box || !img) return;
    const photos = JSON.parse(box.dataset.photos || '[]');
    const count = document.getElementById('dx-count');
    const thumbs = Array.from(box.querySelectorAll('.hx-gal__thumb'));
    let current = 0;
    function show(i) {
        if (!photos.length) return;
        current = (i + photos.length) % photos.length;
        // Un srcset généré pour la première photo prendrait le pas sur le nouveau src.
        img.removeAttribute('srcset');
        img.removeAttribute('sizes');
        img.src = photos[current];
        if (count) count.textContent = current + 1;
        thumbs.forEach(function (t) {
            const on = Number(t.dataset.dxShow) === current;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-current', on ? 'true' : 'false');
            if (on) t.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        });
    }
    box.addEventListener('click', function (e) {
        const slide = e.target.closest('[data-dx-slide]');
        if (slide) { show(current + Number(slide.dataset.dxSlide)); return; }
        const tile = e.target.closest('[data-dx-show]');
        if (tile) show(Number(tile.dataset.dxShow));
    });
    box.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') { e.preventDefault(); show(current - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); show(current + 1); }
    });
})();
</script>
