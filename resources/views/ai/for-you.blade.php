<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ __('Pour vous') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Des adresses, des sites et des événements choisis selon vos goûts, votre budget et votre façon de voyager.">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('partials.ai-styles')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="min-h-screen">
@include('partials.page-background')
@include('partials.public-top-nav')

@php
    $pickedInterests = $profile['interests'] ?? [];
    $hasProfile = $pickedInterests || ($profile['budget'] ?? null) || ($profile['travel_type'] ?? null);
@endphp

<main class="pb-16">
    <section class="py-12 sm:py-14 text-center" style="background:#faf8f3">
        <div class="ai-wrap">
            <div class="ti-hero-card inline-block rounded-2xl px-6 py-8 sm:px-12 sm:py-10">
                <p class="ai-kicker mb-3">{{ __('Personnalisé') }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold leading-tight mb-4">{{ __('Pour vous') }}</h1>
                <p class="text-xl max-w-2xl mx-auto">{{ __('Des adresses, des sites et des événements choisis selon vos goûts, votre budget et votre façon de voyager.') }}</p>
            </div>
        </div>
    </section>
    @include('partials.hero-separator')

    <div class="ai-wrap pt-8 space-y-8">
        @if(session('status'))
            <p class="ai-note ai-note--ok" role="status"><i class="fas fa-circle-check mr-2"></i>{{ session('status') }}</p>
        @endif
        @if($errors->any())
            <p class="ai-note ai-note--err" role="alert">{{ $errors->first() }}</p>
        @endif

        {{-- Questionnaire de démarrage (3 questions) --}}
        <details class="ai-card" {{ $hasProfile ? '' : 'open' }}>
            <summary class="cursor-pointer select-none px-5 py-4 flex items-center justify-between gap-3 list-none">
                <span class="font-bold" style="color:#1d1a16"><i class="fas fa-sliders mr-2" style="color:#b9500a"></i>
                    {{ $hasProfile ? __('Mes préférences') : __('Dites-nous ce que vous aimez (3 questions)') }}</span>
                <span class="ai-link text-sm">{{ $hasProfile ? __('Modifier') : __('Répondre') }}</span>
            </summary>
            <form method="POST" action="{{ route('ai.profile.save') }}" class="px-5 pb-5 space-y-6" style="border-top:1px solid #f0e9da">
                @csrf
                <div class="pt-5">
                    <span class="ai-label">{{ __('1. Qu\'aimez-vous voyager ?') }} <span class="ai-muted font-normal">{{ __('(plusieurs choix possibles)') }}</span></span>
                    <div class="ai-chips">
                        @foreach($interests as $key => $def)
                            <label class="ai-chip">
                                <input type="checkbox" name="interests[]" value="{{ $key }}" {{ in_array($key, $pickedInterests, true) ? 'checked' : '' }}>
                                <span><i class="fas {{ $def['icon'] }}"></i>{{ $def['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <span class="ai-label">{{ __('2. Votre budget') }}</span>
                    <div class="ai-chips">
                        @foreach($budgets as $key => $label)
                            <label class="ai-chip">
                                <input type="radio" name="budget" value="{{ $key }}" {{ ($profile['budget'] ?? null) === $key ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <span class="ai-label">{{ __('3. Vous voyagez') }}</span>
                    <div class="ai-chips">
                        @foreach($travelTypes as $key => $def)
                            <label class="ai-chip">
                                <input type="radio" name="travel_type" value="{{ $key }}" {{ ($profile['travel_type'] ?? null) === $key ? 'checked' : '' }}>
                                <span>{{ $def['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="max-w-xs">
                    <label for="home_city_id" class="ai-label">{{ __('Votre ville de séjour') }} <span class="ai-muted font-normal">{{ __('(facultatif)') }}</span></label>
                    <select id="home_city_id" name="home_city_id" class="ai-input">
                        <option value="">{{ __('— Aucune —') }}</option>
                        @foreach($cities as $c)
                            <option value="{{ $c['id'] }}" {{ ($profile['home_city_id'] ?? null) == $c['id'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                    <p class="ai-hint">{{ __('Pour mettre en avant ce qui se trouve à proximité.') }}</p>
                </div>
                <button type="submit" class="ai-btn"><i class="fas fa-check text-xs"></i> {{ __('Enregistrer mes préférences') }}</button>
            </form>
        </details>

        {{-- Résultats --}}
        <section aria-labelledby="reco-title">
            <div class="ai-strip__head">
                <div>
                    <p class="ai-kicker">{{ $cold ? __('Sélection du moment') : __('Vos suggestions') }}</p>
                    <h2 id="reco-title" class="ai-h2">{{ $cold ? __('Les adresses les plus appréciées') : __('Choisi pour vous') }}</h2>
                </div>
                <a href="{{ route('trip.create') }}" class="ai-btn ai-btn--ghost"><i class="fas fa-route text-xs"></i> {{ __('Planifier mon séjour') }}</a>
            </div>
            @if($cold)
                <p class="ai-note ai-note--warn mb-4">{{ __('Nous ne vous connaissons pas encore : voici une sélection générale. Répondez aux 3 questions ci-dessus, ou consultez quelques fiches, et vos suggestions s\'affineront.') }}</p>
            @elseif($alpha > 0)
                <p class="ai-note ai-note--ok mb-4">{{ __('Vos suggestions combinent vos préférences et les choix de visiteurs aux goûts proches des vôtres.') }}</p>
            @endif

            @if($recommendations)
                <div class="ai-grid">
                    @foreach($recommendations as $r)
                        @include('partials.ai-reco-card', ['r' => $r])
                    @endforeach
                </div>
                @include('partials.ai-reco-map', ['items' => array_map(fn ($r) => $r['item'], $recommendations)])
            @else
                <p class="ai-card p-6 ai-muted">{{ __('Aucune suggestion pour le moment. Revenez après avoir consulté quelques fiches.') }}</p>
            @endif
        </section>

        {{-- Transparence et données --}}
        <section class="ai-card p-5 sm:p-6 grid gap-6 md:grid-cols-[1fr_auto] items-start">
            <div>
                <h2 class="font-bold mb-2" style="color:#1d1a16"><i class="fas fa-shield-halved mr-2" style="color:#1b6b3a"></i>{{ __('Comment ça marche, et vos données') }}</h2>
                <p class="ai-muted text-sm leading-relaxed">
                    {{ __('Chaque suggestion est classée selon sa proximité avec vos goûts, sa qualité (notes, mise en avant, nouveauté), votre budget et, quand c\'est possible, les choix de visiteurs semblables à vous. Nous gardons une place pour les adresses encore peu connues. Nous enregistrons uniquement les fiches que vous consultez et vos préférences, sans lien avec votre identité si vous n\'êtes pas connecté ; le signal « Ne pas suivre » de votre navigateur est respecté.') }}
                </p>
            </div>
            <form method="POST" action="{{ route('ai.data.clear') }}" onsubmit="return confirm('Effacer vos préférences et votre historique de consultation ?')">
                @csrf
                <button type="submit" class="ai-btn ai-btn--ghost"><i class="fas fa-trash-can text-xs"></i> {{ __('Effacer mes données') }}</button>
            </form>
        </section>
    </div>
</main>

@include('partials.homepage-footer')
</body>
</html>
