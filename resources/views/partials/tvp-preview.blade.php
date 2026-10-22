{{-- Aperçu (non cliquable) des types de visite d'un site : invités et comptes non visiteurs. Paramètre : $tvpModes. --}}
<p class="tvp-step">{{ __('Types de visite proposés') }}</p>
<div class="tvp-modes mb-4">
    @foreach($tvpModes as $m)
        <div>
            <div class="tvp-tab is-preview">
                <span class="tvp-tab__icon"><i class="fas {{ $m['icon'] }}" aria-hidden="true"></i></span>
                <span class="tvp-tab__txt"><strong>{{ $m['label'] }}</strong>@if($m['hint'])<small>{{ $m['hint'] }}</small>@endif
                    </span>
<span class="tvp-tab__price">{{ $m['price'] }}@if($m['unit'])<small>{{ $m['unit'] }}</small>@endif</span>
@if(! empty($m['badges']))<span class="tvp-badges">@foreach($m['badges'] as $b)<span class="tvp-badge {{ $b['tone'] ? 'is-'.$b['tone'] : '' }}"><i class="fas {{ $b['icon'] }}" aria-hidden="true"></i>{{ $b['text'] }}</span>@endforeach</span>@endif
            </div>
            @if(($m['mode'] ?? null) === 'custom' && $m['type']->hasDetails())
                <div class="mt-1.5">@include('partials.tvp-type-details', ['t' => $m['type']])</div>
            @endif
        </div>
    @endforeach
</div>
