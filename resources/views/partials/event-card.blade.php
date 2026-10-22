{{--
    Carte événement commune (accueil « Événements à venir » et /evenements), en forme de billet :
    affiche avec la date en tampon, infos, puis talon détachable (prix + actions).
    Paramètres : $event (App\Models\Event), $headingTag ('h2' | 'h3', défaut h3).

    Toute la carte mène à la fiche (lien étiré sur le titre) ; « Réserver » (billetterie externe)
    et « Enregistrer » restent de vrais liens / boutons au-dessus de ce lien.
    Styles : .ti-event-card dans partials.ui-kit.
--}}
@php
    $headingTag = $headingTag ?? 'h3';
    $start = $event->starts_at?->copy()->locale(app()->getLocale());
    $daysUntil = $event->starts_at ? (int) round(now()->startOfDay()->diffInDays($event->starts_at->copy()->startOfDay(), false)) : null;
    $countdown = match (true) {
        $daysUntil === null, $daysUntil < 0 => null,
        $daysUntil === 0 => __("Aujourd'hui"),
        $daysUntil === 1 => __('Demain'),
        default => __('Dans :n jours', ['n' => $daysUntil]),
    };
    $isSoon = $daysUntil !== null && $daysUntil >= 0 && $daysUntil <= 7;
    $isFree = $event->is_free || (float) ($event->price ?? 0) <= 0;
    $hasTime = $start && $start->format('H:i') !== '00:00';
    $place = $event->location_name && $event->city && ! str_contains(mb_strtolower($event->location_name), mb_strtolower($event->city))
        ? $event->location_name.', '.$event->city
        : ($event->location_name ?: ($event->city ?: "Côte d'Ivoire"));
@endphp
<article class="ti-event-card group">
    <div class="ti-event-card__media">
        @if(!empty($event->cover_url))
            <img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?: $event->localized('title') }}" loading="lazy" decoding="async">
        @else
            <div class="ti-event-card__placeholder" aria-hidden="true"><i class="fas fa-ticket"></i></div>
        @endif
        @if($start)
            <span class="ti-event-card__stamp" aria-hidden="true">
                <b>{{ $start->format('d') }}</b>
                <span>{{ rtrim($start->translatedFormat('M'), '.') }}</span>
            </span>
        @endif
        <span class="ti-event-card__save">
            @include('partials.place-card-fav', ['providerId' => $event->id, 'name' => $event->localized('title'), 'favType' => 'event', 'favIcon' => 'bookmark', 'favClass' => 'ti-round'])
        </span>
        @if($countdown)
            <span class="ti-event-card__countdown {{ $isSoon ? 'is-soon' : '' }}"><i class="far fa-clock" aria-hidden="true"></i>{{ $countdown }}</span>
        @endif
    </div>

    <div class="ti-event-card__body">
        @if($event->category)
            <p class="ti-event-card__category"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ $event->category->localized('name') }}</p>
        @endif
        <{{ $headingTag }} class="ti-event-card__title">
            <a href="{{ route('events.show', $event->slug) }}" class="ti-event-card__link">{{ $event->localized('title') }}</a>
        </{{ $headingTag }}>
        <ul class="ti-event-card__meta">
            @if($start)
                <li><i class="far fa-clock" aria-hidden="true"></i>{{ ucfirst($start->translatedFormat('l j F')) }}@if($hasTime) · {{ $start->format('H\hi') }}@endif</li>
            @endif
            <li><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $place }}</li>
        </ul>
    </div>

    <div class="ti-event-card__stub">
        <span class="ti-event-card__price {{ $isFree ? 'is-free' : '' }}">
            <small>{{ __('Entrée') }}</small>
            {{ $isFree ? __('Gratuit') : number_format((float) $event->price, 0, ',', ' ') }}@unless($isFree)<em> FCFA</em>@endunless
        </span>
        @if($event->ticket_url)
            <a href="{{ $event->ticket_url }}" target="_blank" rel="noopener noreferrer" class="ti-event-card__cta">
                {{ __('Réserver') }}<i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>
        @else
            <span class="ti-event-card__more" aria-hidden="true">{{ __('Voir l\'événement') }}<i class="fas fa-arrow-right"></i></span>
        @endif
    </div>
</article>
@include('partials.place-card-script')
