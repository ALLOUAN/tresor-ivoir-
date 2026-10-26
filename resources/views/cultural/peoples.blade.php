<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Cultures Ivoiriennes') }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Montserrat', serif; }
        html:not(.dark) body { background:#D8E9E0; color: #0E3B2A; }
        /* Image de fond configurable (back-office) du bandeau de la page Cultures. */
        .cultural-hero {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        /* Image configurée : couleurs vraies, sans voile ni cadre — le titre se pose
           directement sur l'image (prévoir un visuel au centre clair). */
        .cultural-hero[style*="--cultural-hero-bg-image"] {
            background-image: var(--cultural-hero-bg-image, none);
        }
    </style>
    @include('partials.filter-sidebar')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
    @include('partials.page-background')

@include('partials.public-top-nav')

@php
    $culturalHeroImage = \Illuminate\Support\Facades\Schema::hasTable('cultural_hero_images')
        ? \App\Models\CulturalHeroImage::query()->find(1)
        : null;
@endphp

{{-- Hero --}}
<section class="hero-cultures relative py-20 overflow-hidden {{ ($culturalHeroImage && $culturalHeroImage->isVisible()) ? 'cultural-hero' : '' }}"
    @if($culturalHeroImage && $culturalHeroImage->isVisible())
        style="--cultural-hero-bg-image: url('{{ $culturalHeroImage->image_url }}');"
    @endif
>
    @unless($culturalHeroImage && $culturalHeroImage->isVisible())
        @include('partials.ivorian-motifs', ['culture' => 'cultures'])
    @endunless
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <div class="ti-hero-card inline-block rounded-2xl px-6 py-8 sm:px-12 sm:py-10">
            <p class="text-orange-400 text-sm font-medium uppercase tracking-widest mb-3">{{ __('Patrimoine Vivant') }}</p>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                {{ __('Cultures Ivoiriennes') }}
            </h1>
            <p class="text-gray-200 text-xl max-w-2xl mx-auto">
                {{ __('Partez à la rencontre des peuples qui façonnent l\'identité culturelle de la Côte d\'Ivoire.') }}
            </p>
        </div>
    </div>
</section>

@include('partials.hero-separator', ['class' => 'mb-8'])

{{-- Domaines culturels (barre latérale sur ordinateur, rangée défilante sur mobile) + grille des peuples --}}
<div class="filter-layout">
@if($domains->isNotEmpty())
<section class="filter-aside max-w-6xl mx-auto px-6 pb-8" aria-label="{{ __('Domaines culturels') }}">
    <div class="filter-row flex flex-wrap gap-2 justify-center" id="domain-filter-row" data-filter-row>
        <p class="filter-title" data-sub="{{ __('Filtrer les peuples') }}">{{ __('Domaines culturels') }}</p>
        <a href="{{ route('cultural.peoples') }}"
            class="domain-filter-pill inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition
                {{ !request('domaine') ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700' }}">
            <i class="fas fa-globe text-xs"></i> {{ __('Tous les peuples') }}
        </a>
        @foreach($domains as $domainPill)
        <a href="{{ route('cultural.peoples', ['domaine' => $domainPill->slug]) }}"
            class="domain-filter-pill inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition border
                {{ request('domaine') === $domainPill->slug
                    ? 'bg-orange-500 text-black border-orange-500'
                    : 'bg-slate-800/60 text-slate-300 hover:bg-slate-700 hover:text-white border-slate-700' }}"
            style="{{ request('domaine') === $domainPill->slug || !$domainPill->color ? '' : 'border-color:'.$domainPill->color.'40' }}">
            @if($domainPill->icon)<i class="{{ $domainPill->icon }} text-xs" style="{{ ($domainPill->color && request('domaine') !== $domainPill->slug) ? 'color:'.$domainPill->color : '' }}"></i>@endif
            {{ $domainPill->name }}
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- Grille peuples --}}
<section class="filter-main max-w-6xl mx-auto px-6 pb-20">
    @if($domain)
    <p class="ti-people-count">
        <span>{{ $peoples->count() }} {{ $peoples->count() > 1 ? __('peuples liés') : __('peuple lié') }} {{ __('au domaine') }} <strong>{{ $domain->name }}</strong></span>
        <a href="{{ route('cultural.peoples') }}"><i class="fas fa-xmark"></i> {{ __('Retirer le filtre') }}</a>
    </p>
    @endif
    @if($peoples->isEmpty())
    <div class="text-center py-20 text-slate-500">
        <i class="fas fa-users text-5xl mb-4 block text-slate-700"></i>
        @if($domain)
        <p>{{ __('Aucun peuple lié au domaine «') }} {{ $domain->name }} {{ __('» pour le moment.') }}</p>
        <a href="{{ route('cultural.peoples') }}" class="text-orange-400 hover:text-orange-300 text-sm mt-2 inline-block">{{ __('Voir tous les peuples') }}</a>
        @else
        <p>{{ __('Aucun peuple disponible pour le moment.') }}</p>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($peoples as $people)
            @include('partials.people-card', ['people' => $people, 'headingTag' => 'h2'])
        @endforeach
    </div>
    @endif
</section>
</div>

@include('partials.homepage-footer')
</body>
</html>
