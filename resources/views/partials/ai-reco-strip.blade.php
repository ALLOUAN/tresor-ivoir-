{{--
    Bandeau « Recommandé pour vous » (accueil, annuaire).
    Paramètres : $limit (défaut 4), $types (restreint aux types de fiches), $heading.
    Silencieux si l'IA est coupée ou en cas d'erreur : n'empêche jamais l'affichage de la page.
--}}
@php
    $__limit = $limit ?? 4;
    $__data = \App\Services\Ai\Recommendations::enabled()
        ? rescue(fn () => app(\App\Services\Ai\Recommendations::class)->forRequest(request(), $__limit, $types ?? []), null, false)
        : null;
@endphp
@if($__data && ! empty($__data['recommendations']))
@include('partials.ai-styles')
@php
    // Image de fond choisie dans l'administration (Apparence → Recommandations).
    $__bg = \Illuminate\Support\Facades\Schema::hasTable('reco_section_images') ? \App\Models\RecoSectionImage::query()->find(1) : null;
    $__bgUrl = $__bg && $__bg->isVisible() ? $__bg->image_url : null;
@endphp
@if($__bgUrl)
<style>
    .ai-reco-bg { position: relative; isolation: isolate; background-size: cover; background-position: center; background-repeat: no-repeat; background-image: var(--reco-bg); }
    /* Voile clair et uni : garde le titre et la carte lisibles sur n'importe quelle photo. */
    .ai-reco-bg::before { content: ''; position: absolute; inset: 0; z-index: -1; background: rgba(243,248,245,.7); }
</style>
@endif
<section class="ai-reco-strip py-10 sm:py-12 {{ $__bgUrl ? 'ai-reco-bg' : '' }}" @if($__bgUrl) style="--reco-bg: url('{{ $__bgUrl }}');" @endif aria-label="{{ __('Recommandations personnalisées') }}">
    <div class="ai-strip">
        <div class="ai-strip__head">
            <div class="sec-head">
                <p class="sec-kicker">{{ $__data['cold'] ? __('Sélection du moment') : __('Pour vous') }}</p>
                <h2 class="sec-title">{!! \Illuminate\Support\Str::of(e($heading ?? ($__data['cold'] ? __('À découvrir en Côte d\'Ivoire') : __('Recommandé pour vous'))))->replaceMatches('/(\S+)$/u', '<em>$1</em>') !!}</h2>
            </div>
            <a href="{{ route('ai.for-you') }}" class="ai-link text-sm">
                {{ $__data['cold'] ? __('Personnaliser mes suggestions') : __('Voir toutes mes suggestions') }} <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        @include('partials.ai-reco-map', ['collapsed' => true, 'items' => array_map(fn ($r) => $r['item'], $__data['recommendations'])])
    </div>
</section>
@endif
