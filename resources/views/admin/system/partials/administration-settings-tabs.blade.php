@php
    $active = $active ?? 'appearance';
    $appearanceTabClass = 'inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap';
    $appearanceTabActive = 'border-violet-500 text-white';
    $appearanceTabIdle = 'border-transparent text-slate-400 hover:text-slate-200';
@endphp

<div class="mb-6 border-b border-slate-800 flex flex-wrap gap-1 -mb-px">
    <a href="{{ route('admin.administration.maintenance') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'maintenance' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-screwdriver-wrench {{ ($active ?? '') === 'maintenance' ? 'text-violet-400' : '' }}"></i> Maintenance
    </a>
    <a href="{{ route('admin.administration.appearance') }}" class="{{ $appearanceTabClass }} {{ $active === 'appearance' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-images {{ $active === 'appearance' ? 'text-violet-400' : '' }}"></i> Slides
    </a>
    <a href="{{ route('admin.administration.settings') }}" class="{{ $appearanceTabClass }} {{ $active === 'settings' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-sliders {{ $active === 'settings' ? 'text-violet-400' : '' }}"></i> Paramètres généraux
    </a>
    <a href="{{ route('admin.administration.homepage') }}" class="{{ $appearanceTabClass }} {{ $active === 'homepage' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-house {{ $active === 'homepage' ? 'text-violet-400' : '' }}"></i> Accueil
    </a>
    <a href="{{ route('admin.administration.contact-messages.index') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'contact-messages' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-inbox {{ ($active ?? '') === 'contact-messages' ? 'text-violet-400' : '' }}"></i> Messages reçus
    </a>
    <a href="{{ route('admin.administration.contacts') }}" class="{{ $appearanceTabClass }} {{ ($active ?? '') === 'contacts' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-address-book {{ ($active ?? '') === 'contacts' ? 'text-violet-400' : '' }}"></i> Coordonnées
    </a>
    <a href="{{ route('admin.administration.social') }}" class="{{ $appearanceTabClass }} {{ $active === 'social' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-share-nodes {{ $active === 'social' ? 'text-violet-400' : '' }}"></i> Réseaux sociaux
    </a>
    <a href="{{ route('admin.administration.media') }}" class="{{ $appearanceTabClass }} {{ $active === 'media' ? $appearanceTabActive : $appearanceTabIdle }}">
        <i class="fas fa-photo-film {{ $active === 'media' ? 'text-violet-400' : '' }}"></i> Médias
    </a>
</div>
