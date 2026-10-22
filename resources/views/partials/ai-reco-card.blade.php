{{-- Carte de recommandation. Paramètre : $r (élément retourné par HybridRecommender::recommend). --}}
@php $__i = $r['item']; @endphp
<a href="{{ $__i->url }}" class="ai-reco group">
    <div class="ai-reco__img">
        @if($__i->image)
            <img src="{{ $__i->image }}" alt="{{ $__i->title }}" loading="lazy">
        @else
            <i class="fas fa-image"></i>
        @endif
        <span class="ai-reco__type">{{ $__i->typeLabel }}</span>
    </div>
    <div class="ai-reco__body">
        <h3 class="ai-reco__title">{{ $__i->title }}</h3>
        <p class="ai-reco__meta">
            @if($__i->city){{ $__i->city }}@endif
            @if($__i->price !== null && $__i->price > 0) · {{ __('dès') }} {{ number_format($__i->price, 0, ',', ' ') }} {{ __('FCFA') }} @elseif($__i->price === 0) · {{ __('gratuit') }} @endif
        </p>
        <p class="ai-why"><i class="fas fa-wand-magic-sparkles"></i>{{ implode(' · ', $r['reasons']) }}</p>
    </div>
</a>
