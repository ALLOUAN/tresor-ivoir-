{{-- Fiche détaillée d'un type de visite (inclus, non inclus, rendez-vous, accessibilité, à prévoir). Paramètre : $t (TouristVisitType). --}}
@php
    $incl = \App\Models\TouristVisitType::lines($t->included);
    $excl = \App\Models\TouristVisitType::lines($t->not_included);
    $bring = \App\Models\TouristVisitType::lines($t->to_bring);
@endphp
@if($t->hasDetails())
    <details class="tvp-details">
        <summary><i class="fas fa-circle-info" aria-hidden="true"></i>{{ __('Ce qui est inclus et infos pratiques') }}<i class="fas fa-chevron-down tvp-details__chev" aria-hidden="true"></i></summary>
        <div class="tvp-details__body">
            @if($t->languagesList())
                <p><strong>{{ __('Langues') }} :</strong> {{ implode(', ', $t->languagesList()) }}</p>
            @endif
            @if($incl)
                <ul class="tvp-list">@foreach($incl as $l)<li><i class="fas fa-check is-ok" aria-hidden="true"></i>{{ $l }}</li>@endforeach</ul>
            @endif
            @if($excl)
                <ul class="tvp-list">@foreach($excl as $l)<li><i class="fas fa-xmark is-no" aria-hidden="true"></i>{{ $l }}</li>@endforeach</ul>
            @endif
            @if($t->meeting_point)
                <p><i class="fas fa-location-dot" aria-hidden="true"></i> <strong>{{ __('Rendez-vous') }} :</strong> {{ $t->meeting_point }}</p>
            @endif
            @if($t->accessibility)
                <p><i class="fas fa-wheelchair" aria-hidden="true"></i> {{ $t->accessibility }}</p>
            @endif
            @if($bring)
                <p class="mb-1"><strong>{{ __('À prévoir') }} :</strong></p>
                <ul class="tvp-list">@foreach($bring as $l)<li><i class="fas fa-circle-info is-info" aria-hidden="true"></i>{{ $l }}</li>@endforeach</ul>
            @endif
        </div>
    </details>
@endif
