<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    @php $s = $plan['summary']; @endphp
    <title>{{ __('Séjour à') }} {{ implode(', ', $s['cities']) }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="robots" content="noindex">
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
    $fcfa = fn ($n) => number_format((int) $n, 0, ',', ' ').' FCFA';
    $over = $s['budget'] > 0 && ($s['remaining'] ?? 0) < 0;
@endphp

<main class="pb-16">
    <section class="py-12 sm:py-14 text-center" style="background:#faf8f3">
        <div class="ai-wrap">
            <div class="ti-hero-card inline-block rounded-2xl px-6 py-8 sm:px-12 sm:py-10">
                <p class="ai-kicker mb-3">{{ __('Mon séjour') }}</p>
                <h1 class="font-serif text-3xl md:text-5xl font-bold leading-tight mb-4">{{ $s['days'] }} {{ __('jour') }}{{ $s['days'] > 1 ? 's' : '' }} à {{ implode(' · ', $s['cities']) }}</h1>
                <p class="text-lg max-w-2xl mx-auto">{{ $story['intro'] }}</p>
            </div>
        </div>
    </section>
    @include('partials.hero-separator')

    <div class="ai-wrap pt-8 space-y-6">
        {{-- Synthèse du budget --}}
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3" aria-label="{{ __('Synthèse') }}">
            <div class="ai-card ai-stat"><b>{{ $fcfa($s['total_cost']) }}</b><small>{{ __('Coût estimé') }} · {{ $s['travelers'] }} {{ __('pers.') }}</small></div>
            <div class="ai-card ai-stat"><b>{{ $s['budget'] > 0 ? $fcfa($s['budget']) : '—' }}</b><small>{{ __('Budget prévu') }}</small></div>
            <div class="ai-card ai-stat"><b style="color:{{ $over ? '#c0362c' : '#1b6b3a' }}">{{ $s['remaining'] !== null ? $fcfa(abs($s['remaining'])) : '—' }}</b><small>{{ $over ? __('Dépassement') : __('Reste disponible') }}</small></div>
            <div class="ai-card ai-stat"><b>{{ $s['km_total'] }} {{ __('km') }}</b><small>{{ __('Trajets estimés (route)') }}</small></div>
        </section>
        <p class="ai-muted text-sm -mt-2">{{ __('Dont hébergement :') }} {{ $fcfa($s['lodging_cost']) }} · {{ __('activités et repas :') }} {{ $fcfa($s['activities_cost']) }}.</p>

        @foreach($plan['warnings'] as $w)
            <p class="ai-note ai-note--warn"><i class="fas fa-triangle-exclamation mr-2"></i>{{ $w }}</p>
        @endforeach

        {{-- Jours --}}
        @foreach($plan['days'] as $i => $d)
            <article class="ai-card ai-day" aria-labelledby="day-{{ $d['index'] }}">
                <header class="ai-day__head">
                    <span class="ai-day__no">{{ $d['index'] }}</span>
                    <div>
                        <h2 id="day-{{ $d['index'] }}" class="font-bold" style="color:#1d1a16">{{ \Illuminate\Support\Carbon::parse($d['date'])->locale(app()->getLocale())->translatedFormat('l j F') }} · {{ $d['city'] }}</h2>
                        <p class="ai-muted text-sm">{{ $d['km'] }} {{ __('km estimés') }} · {{ $fcfa($d['cost']) }} {{ __('(activités et repas)') }}</p>
                    </div>
                </header>

                @if($d['transfer'])
                    <p class="px-5 py-3 text-sm" style="background:#fdf7e7;border-bottom:1px solid #f0e2b8;color:#6b4e0e">
                        <i class="fas fa-car-side mr-2"></i>{{ __('Trajet vers') }} {{ $d['transfer']['to'] }} {{ __(': environ') }} {{ $d['transfer']['km'] }} {{ __('km (≈') }} {{ intdiv($d['transfer']['minutes'], 60) }} h {{ str_pad((string) ($d['transfer']['minutes'] % 60), 2, '0', STR_PAD_LEFT) }}).
                    </p>
                @endif

                @if(!empty($story['days'][$i]))
                    <p class="px-5 pt-4 pb-1 text-[15px] leading-relaxed" style="color:#4a443a">{{ $story['days'][$i] }}</p>
                @endif

                <div>
                    @forelse($d['stops'] as $st)
                        <div class="ai-stop">
                            <span class="ai-stop__slot">{{ $st['slot'] }}</span>
                            <div>
                                @if($st['item'])
                                    <a href="{{ $st['item']['url'] }}" class="ai-stop__title ai-link" style="color:#1d1a16">{{ $st['title'] }}</a>
                                    <p class="ai-stop__sub">
                                        {{ $st['item']['type_label'] }}@if($st['item']['city']) · {{ $st['item']['city'] }}@endif
                                        @if($st['km'] > 0) · {{ $st['km'] }} {{ __('km depuis l\'étape précédente') }} @endif
                                    </p>
                                @else
                                    <span class="ai-stop__title">{{ $st['title'] }}</span>
                                @endif
                                @if(!empty($st['reasons']))
                                    <p class="ai-stop__sub"><i class="fas fa-wand-magic-sparkles mr-1" style="color:#b8892b"></i>{{ implode(' · ', $st['reasons']) }}</p>
                                @endif
                                @if($st['item'] && !empty($st['item']['directions']))
                                    <a href="{{ $st['item']['directions'] }}" target="_blank" rel="noopener noreferrer" class="ai-link text-sm inline-block mt-1"><i class="fas fa-diamond-turn-right text-xs mr-1"></i>{{ __('Itinéraire') }}</a>
                                @endif
                            </div>
                            <div class="ai-stop__cost">
                                @if($st['cost'] > 0){{ $fcfa($st['cost']) }}@else <span class="ai-muted font-normal">{{ $st['kind'] === 'activity' ? __('Gratuit ou prix non communiqué') : '—' }}</span>@endif
                                @if(!empty($st['estimated']) && $st['cost'] > 0)<br><span class="ai-badge">{{ __('estimé') }}</span>@endif
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-4 ai-muted text-sm">{{ __('Journée libre.') }}</p>
                    @endforelse
                </div>

                @if(collect($d['stops'])->filter(fn ($s) => ! empty($s['item']['lat']))->count() >= 1)
                    @include('partials.day-route-map', ['url' => route('trip.day-route', [$trip->uuid, $d['index']]), 'day' => $d['index']])
                @endif

                @if($d['lodging'])
                    <div class="ai-lodging">
                        <div>
                            <p class="font-bold" style="color:#14532d"><i class="fas fa-bed mr-2"></i>{{ __('Nuit à') }} {{ $d['lodging']['title'] }}</p>
                            <p class="text-sm" style="color:#14532d">{{ __('Chambre «') }} {{ $d['lodging']['room'] }} » · {{ $fcfa($d['lodging']['nightly']) }} {{ __('la nuit ×') }} {{ $d['lodging']['rooms'] }} {{ __('chambre') }}{{ $d['lodging']['rooms'] > 1 ? 's' : '' }}</p>
                        </div>
                        <a href="{{ $d['lodging']['book_url'] }}" class="ai-btn"><i class="fas fa-lock text-xs"></i> {{ __('Réserver (acompte sécurisé)') }}</a>
                    </div>
                @endif
            </article>
        @endforeach

        <section class="ai-card p-5 flex flex-wrap items-center justify-between gap-4">
            <p class="ai-muted text-sm max-w-xl">
                {{ __('Programme généré automatiquement à partir des adresses publiées') }}{{ $trip->engine === 'anthropic' ? __(', rédigé avec l\'aide d\'un modèle de langage') : '' }}{{ __('. Les prix indiqués sont ceux des prestataires ; vérifiez les disponibilités au moment de réserver.') }}
            </p>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="ai-btn ai-btn--ghost" onclick="navigator.clipboard && navigator.clipboard.writeText(location.href).then(function(){ this.textContent='Lien copié'; }.bind(this))"><i class="fas fa-link text-xs"></i> {{ __('Copier le lien') }}</button>
                <button type="button" class="ai-btn ai-btn--ghost" onclick="window.print()"><i class="fas fa-print text-xs"></i> {{ __('Imprimer') }}</button>
                <a href="{{ route('trip.create') }}" class="ai-btn"><i class="fas fa-rotate text-xs"></i> {{ __('Recommencer') }}</a>
            </div>
        </section>
    </div>
</main>

@include('partials.homepage-footer')
</body>
</html>
