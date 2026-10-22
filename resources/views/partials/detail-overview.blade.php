{{--
    Aperçu d'une fiche établissement : chapeau, description, informations pratiques.
    Paramètre : $model.
--}}
@if($model->short_description)
    <p class="hx-lead">{{ $model->localized('short_description') }}</p>
@endif
@if($model->description)
    <p class="hx-text">{{ $model->localized('description') }}</p>
@endif

<ul class="hx-facts dx-facts">
    <li><span class="hx-facts__icon"><i class="fas fa-city" aria-hidden="true"></i></span><span class="min-w-0"><small>{{ __('Ville') }}</small><span class="block truncate">{{ $model->city?->name ?: 'N/A' }}</span></span></li>
    @if($model->city?->region_administrative)
        <li><span class="hx-facts__icon"><i class="fas fa-map" aria-hidden="true"></i></span><span class="min-w-0"><small>{{ __('Région') }}</small><span class="block truncate">{{ $model->city->region_administrative }}</span></span></li>
    @endif
    @if($model->phone)
        <li><span class="hx-facts__icon"><i class="fas fa-phone" aria-hidden="true"></i></span><span class="min-w-0"><small>{{ __('Contact') }}</small><a href="tel:{{ $model->phone }}" class="block truncate">{{ $model->phone }}</a></span></li>
    @endif
    @if($model->adresse)
        <li class="is-wide"><span class="hx-facts__icon"><i class="fas fa-location-dot" aria-hidden="true"></i></span><span class="min-w-0"><small>{{ __('Adresse') }}</small><span class="block">{{ $model->adresse }}</span></span></li>
    @endif
</ul>
