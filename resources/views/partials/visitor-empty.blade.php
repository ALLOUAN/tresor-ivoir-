{{--
    État vide de l'espace client : icône, titre, phrase d'aide, action utile.
    Paramètres : $icon (Solar), $title, $text (facultatif), $cta (facultatif : ['url' => …, 'label' => …, 'icon' => …]).
--}}
<div class="va-empty">
    <span class="va-empty__ico"><x-icon :name="$icon ?? 'suitcase'" /></span>
    <p class="va-empty__title">{{ $title }}</p>
    @if(! empty($text))<p class="va-empty__text">{{ $text }}</p>@endif
    @if(! empty($cta))
        <a href="{{ $cta['url'] }}" class="va-empty__cta"><x-icon :name="$cta['icon'] ?? 'alt-arrow-right'" />{{ $cta['label'] }}</a>
    @endif
</div>
