{{--
    Corps des pages catalogue d'un établissement (carte du restaurant, activités, circuits, offres).
    Paramètres :
      $model        établissement (restaurant, site, agence, lieu de loisirs, entreprise de transport)
      $groups       éléments groupés par nom de catégorie
      $sectorName, $sectorIcon, $title, $intro
      $showRoute    nom de la route de la fiche (ex. 'restaurant.show')
      $bookAnchor   ancre du module de réservation sur la fiche (null = pas de bouton)
      $bookLabel    libellé du bouton de réservation
      $allLabel     libellé du filtre « tous »
      $itemIcon     icône par défaut (sans photo / sans icône de catégorie)
      $unitLabel    pluriel « :count plat|:count plats »
      $emptyText    message si aucun élément
      $meta         callable(item) → liste de [icône, texte] (durée, participants…)
--}}
@php
    $ctCover = \App\Support\HomeSectorShowcase::photos($model)[0] ?? $model->thumbnail;
    $ctTotal = $groups->flatten(1)->count();
    $ctWhere = collect([$model->city?->name, $model->city?->region_administrative])->filter()->unique()->join(' · ');
    $ctShow = route($showRoute, $model->slug);
    $ctMeta = $meta ?? fn ($i) => [];
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    <a href="{{ $ctShow }}" class="hx-back">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ $model->name }}
    </a>

    <header class="hx-head">
        <div class="min-w-0">
            <div class="hx-pills">
                <span class="hx-pill"><i class="fas {{ $sectorIcon }}" aria-hidden="true"></i>{{ $sectorName }}</span>
                @if($model->provider?->is_verified)
                    <span class="hx-pill is-ok"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ __('Vérifié') }}</span>
                @endif
            </div>
            <h1 class="hx-title">{{ $title }}</h1>
            <p class="hx-meta">
                <span><i class="fas fa-store" aria-hidden="true"></i>{{ $model->name }}</span>
                @if($ctWhere)<span><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $ctWhere }}</span>@endif
            </p>
            <p class="hx-text mt-3">{{ $intro }}</p>
        </div>
        <div class="hx-actions">
            <button type="button" class="ti-round" data-place-share="{{ url()->current() }}" data-place-title="{{ $title }} — {{ $model->name }}" aria-label="{{ __('Partager :name', ['name' => $model->name]) }}" title="{{ __('Partager') }}"><i class="fas fa-share-nodes" aria-hidden="true"></i></button>
            @if($bookAnchor)
                <a href="{{ $ctShow }}{{ $bookAnchor }}" class="ti-cta">{{ $bookLabel }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            @endif
        </div>
    </header>

    @if($ctCover)
    <div class="mn-banner mb-8">
        <img src="{{ $ctCover }}" alt="{{ $model->name }}" decoding="async">
        <div class="mn-banner__stats">
            <span><i class="fas {{ $itemIcon }}" aria-hidden="true"></i>{{ trans_choice($unitLabel, $ctTotal) }}</span>
            @if($groups->count() > 1)
                <span><i class="fas fa-layer-group" aria-hidden="true"></i>{{ trans_choice(':count catégorie|:count catégories', $groups->count()) }}</span>
            @endif
        </div>
    </div>
    @endif

    @if($groups->isNotEmpty())
        @if($groups->count() > 1)
        {{-- ═══ FILTRE PAR CATÉGORIE ═══ --}}
        <div id="filter-list" class="mn-filters mb-8" role="toolbar" aria-label="{{ __('Filtrer par catégorie') }}">
            <button type="button" data-filter="all" class="filter-btn is-active">
                <i class="fas fa-border-all" aria-hidden="true"></i>{{ $allLabel }}<span class="filter-count">{{ $ctTotal }}</span>
            </button>
            @foreach($groups as $categoryName => $items)
                @php $catSlug = $items->first()->category->slug ?? \Illuminate\Support\Str::slug($categoryName); @endphp
                <button type="button" data-filter="{{ $catSlug }}" class="filter-btn">
                    <i class="{{ $items->first()->category->icon ?? 'fas '.$itemIcon }}" aria-hidden="true"></i>{{ $categoryName }}<span class="filter-count">{{ $items->count() }}</span>
                </button>
            @endforeach
        </div>
        @endif

        {{-- ═══ ÉLÉMENTS ═══ --}}
        <div class="space-y-12">
            @foreach($groups as $categoryName => $items)
                @php $catSlug = $items->first()->category->slug ?? \Illuminate\Support\Str::slug($categoryName); @endphp
                <section class="menu-category-block" data-cat="{{ $catSlug }}">
                    <div class="mn-cat">
                        <span class="mn-cat__icon"><i class="{{ $items->first()->category->icon ?? 'fas '.$itemIcon }}" aria-hidden="true"></i></span>
                        <div>
                            <h2>{{ $categoryName }}</h2>
                            <small>{{ trans_choice($unitLabel, $items->count()) }}</small>
                        </div>
                    </div>
                    <div class="mn-grid">
                        @foreach($items as $item)
                            @php
                                // Toutes les photos de l'élément (et plus seulement la première).
                                $ctImgs = array_values(array_filter((array) ($item->images ?? []), fn ($u) => is_string($u) && $u !== ''));
                                $ctImg = $ctImgs[0] ?? null;
                                $ctFacts = $ctMeta($item);
                            @endphp
                            <article class="mn-dish">
                                <div class="mn-dish__media" data-photos='@json($ctImgs)' data-index="0"
                                     @if($ctImg) role="button" tabindex="0" aria-label="{{ __('Voir les photos de :name', ['name' => $item->name]) }}" @endif>
                                    @if($ctImg)
                                        <img src="{{ $ctImg }}" alt="{{ $item->name }}" loading="lazy" decoding="async">
                                        <span class="mn-dish__zoom" aria-hidden="true"><i class="fas fa-expand"></i></span>
                                        @if(count($ctImgs) > 1)
                                            <button type="button" class="mn-dish__nav is-prev" data-dish-step="-1" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                                            <button type="button" class="mn-dish__nav is-next" data-dish-step="1" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                                            <span class="mn-dish__count"><i class="fas fa-images" aria-hidden="true"></i><span data-dish-count>1</span>/{{ count($ctImgs) }}</span>
                                        @endif
                                    @else
                                        <div class="mn-dish__empty"><i class="fas {{ $itemIcon }}" aria-hidden="true"></i></div>
                                    @endif
                                    <span class="mn-dish__tag">{{ $categoryName }}</span>
                                    <span class="mn-dish__price">{{ number_format((int) $item->price_xof, 0, ',', ' ') }}<small>{{ __('FCFA') }}</small></span>
                                </div>
                                <div class="mn-dish__body">
                                    <h3 class="mn-dish__name">{{ $item->name }}</h3>
                                    @if($ctFacts)
                                        <ul class="mn-dish__facts">
                                            @foreach($ctFacts as [$fIcon, $fText])
                                                <li><i class="fas {{ $fIcon }}" aria-hidden="true"></i>{{ $fText }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if($item->description)
                                        <p class="mn-dish__desc">{{ $item->description }}</p>
                                    @endif
                                    <div class="mn-dish__foot">
                                        @if(! empty($orderable))
                                            <button type="button" class="tc-add" data-cart-add="{{ $item->id }}" aria-label="{{ __('Ajouter « :name » au panier', ['name' => $item->name]) }}">
                                                <b data-cart-add-label>{{ __('Ajouter') }}</b><span><i class="fas fa-plus" aria-hidden="true"></i></span>
                                            </button>
                                            <a href="{{ $ctShow }}?item={{ urlencode($item->name) }}#contact-card" class="mn-dish__ask is-ghost" title="{{ __('Contacter à ce sujet') }}" aria-label="{{ __('Contacter à ce sujet') }} : {{ $item->name }}"><span><i class="fas fa-comment-dots" aria-hidden="true"></i></span></a>
                                        @elseif(isset($itemUrl) && ($ctUrl = $itemUrl($item)))
                                            <a href="{{ $ctUrl }}" class="mn-dish__ask">
                                                {{ $itemCta ?? __('Voir le circuit') }}<span><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                                            </a>
                                        @else
                                        <a href="{{ $ctShow }}?item={{ urlencode($item->name) }}#contact-card" class="mn-dish__ask">
                                            {{ __('Contacter à ce sujet') }}<span><i class="fas fa-comment-dots" aria-hidden="true"></i></span>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="mn-empty">
            <i class="fas {{ $itemIcon }}" aria-hidden="true"></i>
            <p class="m-0 text-sm">{{ $emptyText }}</p>
        </div>
    @endif
</div>

<div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()" aria-label="{{ __('Fermer') }}">
        <i class="fas fa-xmark"></i>
    </button>
    <button type="button" id="lightbox-prev" class="mn-lb__nav is-prev hidden" onclick="event.stopPropagation(); lightboxStep(-1)" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    <button type="button" id="lightbox-next" class="mn-lb__nav is-next hidden" onclick="event.stopPropagation(); lightboxStep(1)" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
    <span id="lightbox-count" class="mn-lb__count hidden"></span>
</div>

<script>
let lbPhotos = [];
let lbIndex = 0;
function renderLightbox() {
    document.getElementById('lightbox-img').src = lbPhotos[lbIndex];
    const many = lbPhotos.length > 1;
    ['lightbox-prev', 'lightbox-next', 'lightbox-count'].forEach(id => document.getElementById(id).classList.toggle('hidden', !many));
    document.getElementById('lightbox-count').textContent = (lbIndex + 1) + ' / ' + lbPhotos.length;
}
// openLightbox(url) ou openLightbox([urls], index) : la visionneuse parcourt toutes les photos.
function openLightbox(urls, index) {
    lbPhotos = Array.isArray(urls) ? urls : [urls];
    lbIndex = index || 0;
    renderLightbox();
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function lightboxStep(dir) {
    if (lbPhotos.length < 2) return;
    lbIndex = (lbIndex + dir + lbPhotos.length) % lbPhotos.length;
    renderLightbox();
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (document.getElementById('lightbox').classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lightboxStep(-1);
    if (e.key === 'ArrowRight') lightboxStep(1);
});

// Cartes : flèches pour parcourir les photos d'un élément, clic sur la photo pour l'agrandir.
document.addEventListener('click', e => {
    const media = e.target.closest('.mn-dish__media[data-photos]');
    if (!media) return;
    const photos = JSON.parse(media.dataset.photos || '[]');
    if (!photos.length) return;
    let i = Number(media.dataset.index || 0);
    const step = e.target.closest('[data-dish-step]');
    if (step) {
        i = (i + Number(step.dataset.dishStep) + photos.length) % photos.length;
        media.dataset.index = i;
        const img = media.querySelector('img');
        img.removeAttribute('srcset'); img.removeAttribute('sizes');
        img.src = photos[i];
        const n = media.querySelector('[data-dish-count]');
        if (n) n.textContent = i + 1;
        return;
    }
    openLightbox(photos, i);
});
document.addEventListener('keydown', e => {
    const media = e.target.closest && e.target.closest('.mn-dish__media[data-photos]');
    if (media && e.key === 'Enter') media.click();
});

(function () {
    const buttons = document.querySelectorAll('.filter-btn');
    const blocks = document.querySelectorAll('.menu-category-block');
    if (!buttons.length) return;
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const filter = btn.getAttribute('data-filter');
            buttons.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            blocks.forEach(function (block) {
                block.classList.toggle('is-hidden', !(filter === 'all' || block.getAttribute('data-cat') === filter));
            });
        });
    });
})();
</script>
