@php
    $active = $active ?? 'appearance';
    $appearanceTabClass = 'inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap';
    $appearanceTabActive = 'border-green-500 text-white';
    $appearanceTabIdle = 'border-transparent text-slate-400 hover:text-slate-200';
@endphp

<div class="mb-6 border-b border-slate-800 flex flex-wrap gap-1 -mb-px">
    <a href="{{ route('admin.administration.maintenance') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'maintenance' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-screwdriver-wrench {{ ($active ?? '') === 'maintenance' ? 'text-green-400' : '' }}"></i> Maintenance
    </a>
    <a href="{{ route('admin.administration.appearance') }}" class="{{ $appearanceTabClass }} {{ $active === 'appearance' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-images {{ $active === 'appearance' ? 'text-green-400' : '' }}"></i> Slides
    </a>
    <a href="{{ route('admin.administration.flash-info') }}" class="{{ $appearanceTabClass }} {{ $active === 'flash-info' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-bullhorn {{ $active === 'flash-info' ? 'text-green-400' : '' }}"></i> Flash info
    </a>
    <a href="{{ route('admin.administration.settings') }}" class="{{ $appearanceTabClass }} {{ $active === 'settings' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-sliders {{ $active === 'settings' ? 'text-green-400' : '' }}"></i> Paramètres généraux
    </a>
    <a href="{{ route('admin.administration.homepage') }}" class="{{ $appearanceTabClass }} {{ $active === 'homepage' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-house {{ $active === 'homepage' ? 'text-green-400' : '' }}"></i> Accueil
    </a>
    <a href="{{ route('admin.administration.contact-messages.index') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'contact-messages' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-inbox {{ ($active ?? '') === 'contact-messages' ? 'text-green-400' : '' }}"></i> Messages reçus
    </a>
    <a href="{{ route('admin.administration.contacts') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'contacts' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-address-book {{ ($active ?? '') === 'contacts' ? 'text-green-400' : '' }}"></i> Coordonnées
    </a>
    <a href="{{ route('admin.administration.social') }}" class="{{ $appearanceTabClass }} {{ $active === 'social' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-share-nodes {{ $active === 'social' ? 'text-green-400' : '' }}"></i> Réseaux sociaux
    </a>
    <a href="{{ route('admin.administration.media') }}" class="{{ $appearanceTabClass }} {{ $active === 'media' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-photo-film {{ $active === 'media' ? 'text-green-400' : '' }}"></i> Médias
    </a>
    <a href="{{ route('admin.administration.footer') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'footer' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-image {{ ($active ?? '') === 'footer' ? 'text-green-400' : '' }}"></i> Footer
    </a>
    <a href="{{ route('admin.administration.regions-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'regions-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-mountain-sun {{ ($active ?? '') === 'regions-image' ? 'text-green-400' : '' }}"></i> Régions (fond)
    </a>
    <a href="{{ route('admin.administration.annuaire-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'annuaire-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-address-book {{ ($active ?? '') === 'annuaire-image' ? 'text-green-400' : '' }}"></i> Annuaire (fond)
    </a>
    <a href="{{ route('admin.administration.cultures-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'cultures-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-masks-theater {{ ($active ?? '') === 'cultures-image' ? 'text-green-400' : '' }}"></i> Cultures (fond)
    </a>
    <a href="{{ route('admin.administration.header-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'header-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-window-maximize {{ ($active ?? '') === 'header-image' ? 'text-green-400' : '' }}"></i> En-tête (fond)
    </a>
    <a href="{{ route('admin.administration.evenements-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'evenements-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-calendar-days {{ ($active ?? '') === 'evenements-image' ? 'text-green-400' : '' }}"></i> Événements (fond)
    </a>
    <a href="{{ route('admin.administration.partenaires-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'partenaires-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-handshake {{ ($active ?? '') === 'partenaires-image' ? 'text-green-400' : '' }}"></i> Partenaires (fond)
    </a>
    <a href="{{ route('admin.administration.articles-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'articles-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-newspaper {{ ($active ?? '') === 'articles-image' ? 'text-green-400' : '' }}"></i> Articles (fond)
    </a>
    <a href="{{ route('admin.administration.tourist-hero-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'tourist-hero-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-umbrella-beach {{ ($active ?? '') === 'tourist-hero-image' ? 'text-green-400' : '' }}"></i> Page Tourisme (fond)
    </a>
    <a href="{{ route('admin.administration.cultural-hero-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'cultural-hero-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-masks-theater {{ ($active ?? '') === 'cultural-hero-image' ? 'text-green-400' : '' }}"></i> Page Cultures (fond)
    </a>
    <a href="{{ route('admin.administration.articles-hero-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'articles-hero-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-newspaper {{ ($active ?? '') === 'articles-hero-image' ? 'text-green-400' : '' }}"></i> Page Articles (fond)
    </a>
    <a href="{{ route('admin.administration.providers-hero-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'providers-hero-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-address-book {{ ($active ?? '') === 'providers-hero-image' ? 'text-green-400' : '' }}"></i> Page Annuaire (fond)
    </a>
    <a href="{{ route('admin.administration.events-hero-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'events-hero-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-calendar-days {{ ($active ?? '') === 'events-hero-image' ? 'text-green-400' : '' }}"></i> Page Événements (fond)
    </a>
    <a href="{{ route('admin.administration.gallery-hero-image') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'gallery-hero-image' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-camera-retro {{ ($active ?? '') === 'gallery-hero-image' ? 'text-green-400' : '' }}"></i> Page Galerie (fond)
    </a>
</div>
