{{--
    Bouton « favori » des cartes. Paramètres : $providerId (id du contenu, nullable), $name,
    $favType ('provider' | 'article' | 'event', défaut provider), $favIcon ('heart' | 'bookmark', défaut heart),
    $favClass (classe CSS du bouton, défaut ti-place-card__fav).
    Visiteur : bascule instantanée ; invité : renvoi vers la connexion ; autres rôles : rien.
--}}
@php
    $favType = $favType ?? 'provider';
    $favIcon = $favIcon ?? 'heart';
    $favClass = $favClass ?? 'ti-place-card__fav';
    $favModel = match ($favType) { 'article' => \App\Models\Article::class, 'event' => \App\Models\Event::class, default => \App\Models\Provider::class };
@endphp
@if($providerId)
    @php $favRole = auth()->user()?->role; @endphp
    @if($favRole === 'visitor')
        @php $isFav = in_array((int) $providerId, \App\Support\HomeSectorShowcase::favoriteIds($favModel), true); @endphp
        <button type="button" class="{{ $favClass }} @if($isFav) is-on @endif" data-place-fav="{{ $providerId }}" data-fav-type="{{ $favType }}"
                aria-pressed="{{ $isFav ? 'true' : 'false' }}" aria-label="{{ __('Favori : :name', ['name' => $name]) }}" title="{{ $favIcon === 'bookmark' ? __('Enregistrer') : __('Ajouter aux favoris') }}">
            <i class="fas fa-{{ $favIcon }}" aria-hidden="true"></i>
        </button>
    @elseif(! $favRole)
        <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="{{ $favClass }}"
           aria-label="{{ __('Connectez-vous pour ajouter :name à vos favoris', ['name' => $name]) }}" title="{{ $favIcon === 'bookmark' ? __('Enregistrer') : __('Ajouter aux favoris') }}">
            <i class="far fa-{{ $favIcon }}" aria-hidden="true"></i>
        </a>
    @endif
@endif
