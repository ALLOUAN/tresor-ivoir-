{{--
    Section « offre » d'une fiche (activités, circuits, offres de transport).
    Paramètres : $groups (éléments groupés par catégorie), $kicker, $title, $pageUrl (page dédiée),
    $itemIcon, $meta (callable(item) → liste de [icône, texte]), $askUrl (fiche, pour « Contacter à ce sujet »).
--}}
<div class="hx-sechead">
    <div>
        <p class="hx-kicker">{{ $kicker }}</p>
        <h2 class="hx-h2">{{ $title }}</h2>
    </div>
    <a href="{{ $pageUrl }}" class="hx-link">{{ __('Tout voir en détail') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
</div>
<div class="space-y-7">
    @foreach($groups as $categoryName => $items)
        <div>
            <p class="dx-cat"><span class="dx-cat__icon"><i class="{{ $items->first()->category->icon ?? 'fas '.$itemIcon }}" aria-hidden="true"></i></span>{{ $categoryName }}<span class="dx-cat__count">{{ $items->count() }}</span></p>
            <div class="dx-offers">
                @foreach($items as $item)
                    @php $dxImg = $item->images[0] ?? null; $dxFacts = $meta($item); @endphp
                    <article class="dx-offer">
                        <div class="dx-offer__media" @if($dxImg) role="button" tabindex="0" aria-label="{{ __('Agrandir la photo de :name', ['name' => $item->name]) }}" onclick="if (window.openLightbox) openLightbox(@js($dxImg))" onkeydown="if (event.key === 'Enter' && window.openLightbox) openLightbox(@js($dxImg))" @endif>
                            @if($dxImg)
                                <img src="{{ $dxImg }}" alt="" loading="lazy" decoding="async">
                            @else
                                <i class="fas {{ $itemIcon }}" aria-hidden="true"></i>
                            @endif
                        </div>
                        <div class="dx-offer__body">
                            @php $dxUrl = isset($itemUrl) ? $itemUrl($item) : null; @endphp
                            <h3 class="dx-offer__name">@if($dxUrl)<a href="{{ $dxUrl }}" class="hover:underline underline-offset-2">{{ $item->name }}</a>@else{{ $item->name }}@endif</h3>
                            @if($dxFacts)
                                <ul class="dx-specs">
                                    @foreach($dxFacts as [$fIcon, $fText])
                                        <li><i class="fas {{ $fIcon }}" aria-hidden="true"></i>{{ $fText }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="dx-offer__side">
                            <p class="dx-offer__price"><strong>{{ number_format((int) $item->price_xof, 0, ',', ' ') }}</strong> <small>{{ __('FCFA') }}</small></p>
                            @if($dxUrl)
                                <a href="{{ $dxUrl }}" class="dx-offer__ask" aria-label="{{ __('Voir le détail') }} : {{ $item->name }}" title="{{ __('Voir le détail') }}"><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                            @else
                            <a href="{{ $askUrl }}?item={{ urlencode($item->name) }}#contact-card" class="dx-offer__ask" aria-label="{{ __('Contacter à ce sujet') }} : {{ $item->name }}" title="{{ __('Contacter à ce sujet') }}"><i class="fas fa-comment-dots" aria-hidden="true"></i></a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
