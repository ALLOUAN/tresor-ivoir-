{{--
    Carte œuvre commune (accueil « Art & Créations » et /art-creations), façon galerie :
    l'œuvre encadrée sur un mur, pastilles de confiance, artiste, caractéristiques, prix.
    Paramètres : $artwork (App\Models\Artwork), $headingTag ('h2' | 'h3', défaut h3).

    Toute la carte mène à la fiche de l'œuvre (lien étiré sur le titre) ; le nom de l'artiste
    garde son propre lien vers sa fiche, au-dessus du lien étiré.
    Styles : .ti-art-card dans partials.ui-kit.
--}}
@php
    $headingTag = $headingTag ?? 'h3';
    $isSold = $artwork->status === \App\Models\Artwork::STATUS_SOLD;
    $isUnique = ! $isSold && (int) $artwork->stock_quantity === 1;
    $artistName = $artwork->provider?->name;
    $artistInitials = $artistName
        ? collect(preg_split('/\s+/u', $artistName))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('')
        : '';
@endphp
<article class="ti-art-card group {{ $isSold ? 'is-sold' : '' }}">
    <div class="ti-art-card__wall">
        <span class="ti-art-card__frame">
            @if(!empty($artwork->images[0]))
                <img src="{{ $artwork->images[0] }}" alt="{{ $artwork->title }}" loading="lazy" decoding="async">
            @else
                <span class="ti-art-card__placeholder" aria-hidden="true"><i class="fas fa-palette"></i></span>
            @endif
        </span>
        <span class="ti-art-card__pills">
            @if($isSold)
                <span class="ti-art-card__pill is-sold">{{ __('Vendue') }}</span>
            @elseif($isUnique)
                <span class="ti-art-card__pill"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ __('Pièce unique') }}</span>
            @endif
            @unless($isSold)
                <span class="ti-art-card__pill is-cert"><i class="fas fa-award" aria-hidden="true"></i>{{ __('Certificat d\'authenticité') }}</span>
            @endunless
        </span>
    </div>

    <div class="ti-art-card__body">
        @if($artwork->category?->name_fr)
            <p class="ti-art-card__category"><i class="fas fa-palette" aria-hidden="true"></i>{{ $artwork->category->name_fr }}</p>
        @endif
        <{{ $headingTag }} class="ti-art-card__title">
            <a href="{{ route('art.show', $artwork->slug) }}" class="ti-art-card__link">{{ $artwork->title }}</a>
        </{{ $headingTag }}>
        <p class="ti-art-card__artist">
            @if($artwork->provider)
                <span class="ti-art-card__avatar" aria-hidden="true">{{ $artistInitials }}</span>
                <a href="{{ route('providers.show', $artwork->provider->slug) }}">{{ $artistName }}</a>
            @else
                {{ __('Artiste indépendant') }}
            @endif
        </p>
        @php $specs = collect([$artwork->medium, $artwork->dimensions ? str_replace(' x ', ' × ', $artwork->dimensions) : null, $artwork->year_created])->filter(); @endphp
        @if($specs->isNotEmpty())
            <ul class="ti-art-card__specs">
                @foreach($specs as $spec)<li>{{ $spec }}</li>@endforeach
            </ul>
        @endif
    </div>

    <div class="ti-art-card__footer">
        <span class="ti-art-card__price">{{ number_format((int) $artwork->price_xof, 0, ',', ' ') }} <small>{{ __('FCFA') }}</small></span>
        <span class="ti-art-card__more" aria-hidden="true">{{ $isSold ? __('Voir l\'œuvre') : __('Acquérir') }}<i class="fas fa-arrow-right"></i></span>
    </div>
</article>
