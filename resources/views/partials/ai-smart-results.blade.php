{{--
    Résultats de la recherche intelligente, ajoutés à la page de recherche (sans remplacer la recherche classique).
    Paramètres : $q (requête), $keywordTotal (nombre de résultats de la recherche classique).
    Silencieux si l'IA est coupée ou en cas d'erreur.
--}}
@php
    $__smart = \App\Services\Ai\Recommendations::enabled()
        ? rescue(fn () => app(\App\Services\Ai\SmartSearch::class)->search($q, 8), null, false)
        : null;
    if ($__smart !== null) {
        rescue(fn () => \Illuminate\Support\Facades\DB::table('ai_search_logs')->insert([
            'query' => mb_substr($q, 0, 200), 'keyword_results' => min(65000, (int) ($keywordTotal ?? 0)),
            'smart_results' => count($__smart['results']), 'did_you_mean' => $__smart['did_you_mean'] ? mb_substr($__smart['did_you_mean'], 0, 200) : null,
            'created_at' => now(),
        ]), null, false);
    }
@endphp
@if($__smart && ($__smart['results'] || $__smart['did_you_mean']))
@include('partials.ai-styles')
<section class="mb-12" aria-label="{{ __('Résultats intelligents') }}">
    <div class="flex flex-wrap items-center gap-3 mb-4" style="border-bottom:1px solid #e8e1d3;padding-bottom:12px">
        <i class="fas fa-wand-magic-sparkles" style="color:#b8892b"></i>
        <h2 class="font-serif text-lg font-bold" style="color:#1d1a16">{{ __('Résultats intelligents') }}</h2>
        <span class="ai-muted text-sm">
            @php $__i = $__smart['interpreted']; @endphp
            @if($__i['city']) à {{ $__i['city']['name'] }}@endif
            @if($__i['type']) · {{ __('type reconnu') }} @endif
            @if($__i['price'] === 'eco') · {{ __('petits prix') }} @elseif($__i['price'] === 'luxe') · {{ __('haut de gamme') }} @endif
            @if($__i['translated']) · {{ __('requête comprise en anglais') }} @endif
        </span>
    </div>
    @if($__smart['did_you_mean'])
        <p class="ai-note ai-note--warn mb-4">
            {{ __('Vouliez-vous dire') }} <a class="ai-link" href="{{ route('search', ['q' => $__smart['did_you_mean']]) }}">« {{ $__smart['did_you_mean'] }} »</a> {{ __('? Les résultats ci-dessous tiennent déjà compte de cette correction.') }}
        </p>
    @endif
    @if($__smart['results'])
        <div class="ai-grid">
            @foreach($__smart['results'] as $__s)
                @include('partials.ai-reco-card', ['r' => ['item' => $__s['item'], 'reasons' => $__s['why'] ?: ['Proche de votre recherche']]])
            @endforeach
        </div>
        @include('partials.ai-reco-map', ['items' => array_map(fn ($s) => $s['item'], $__smart['results'])])
    @endif
</section>
@endif
