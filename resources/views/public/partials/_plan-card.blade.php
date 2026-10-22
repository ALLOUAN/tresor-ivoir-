{{--
    Carte d'un forfait au sein du panneau d'une catégorie.
    Attend : $plan, $i (index dans la catégorie), $count (nb de forfaits de la catégorie), $category (ProviderCategory racine courante).
    Classes conservées pour le script de la page : .price-monthly / .price-yearly.
--}}
@php
    $isPopular  = $plan->code === 'silver' || ($i === 1 && $count >= 2);
    $tierIcons  = ['leaf', 'star', 'star-shine'];
    $tierColors = ['c2', 'c1', 'c3'];
    $savings    = $plan->price_monthly > 0
        ? round(100 - ($plan->price_yearly / ($plan->price_monthly * 12) * 100))
        : 0;
    // Un forfait générique n'indique pas de lui-même la catégorie choisie par le visiteur : on la
    // transmet pour que la fiche prestataire créée à l'inscription soit rattachée à la bonne catégorie.
    $checkoutParams = $plan->provider_category_id ? [] : ['categorie' => $category->slug];
    if (! empty($plan->features_json)) {
        $features = array_map(fn ($f) => ['label' => $f['label'] ?? '', 'ok' => (bool) ($f['included'] ?? false)], $plan->features_json);
    } else {
        $features = [
            ['label' => __('Badge vérifié'), 'ok' => $plan->has_verified_badge],
            ['label' => __('Photos (').$plan->photos_limit.')', 'ok' => $plan->photos_limit > 0],
            ['label' => __('Vidéo de présentation'), 'ok' => $plan->has_video],
            ['label' => __('Mise en avant accueil'), 'ok' => $plan->has_homepage],
            ['label' => __('Campagne newsletter'), 'ok' => $plan->has_newsletter],
            ['label' => __('Posts réseaux sociaux'), 'ok' => $plan->has_social_posts],
            ['label' => __('Statistiques avancées'), 'ok' => in_array($plan->stats_level, ['advanced', 'full'])],
            ['label' => __('Support prioritaire'), 'ok' => in_array($plan->support_level, ['chat', 'dedicated'])],
        ];
    }
    $included = collect($features)->where('ok', true)->count();
@endphp

<article class="pl-card lc {{ $tierColors[$i % 3] }} {{ $isPopular ? 'is-popular' : '' }}">
    @if($isPopular)
        <p class="pl-card__ribbon"><x-icon name="star" />{{ __('Le plus choisi') }}</p>
    @endif

    <header class="pl-card__head">
        <span class="pl-card__ic"><x-icon :name="$tierIcons[$i % 3]" /></span>
        <h3>{{ $plan->localized('name') }}</h3>
        <p>{{ $plan->benefits_text ?: __('Boostez votre visibilité auprès de milliers de voyageurs.') }}</p>
    </header>

    <div class="pl-card__price">
        <div class="price-monthly">
            <p class="pl-amount"><b>{{ number_format((float) $plan->price_monthly, 0, ',', ' ') }}</b><span>{{ __('FCFA / mois') }}</span></p>
            <p class="pl-note">{{ __('Engagement mensuel, résiliable à tout moment') }}</p>
        </div>
        <div class="price-yearly hidden">
            <p class="pl-amount"><b>{{ number_format((float) ($plan->price_yearly / 12), 0, ',', ' ') }}</b><span>{{ __('FCFA / mois') }}</span></p>
            <p class="pl-note">{{ __('soit') }} {{ number_format((float) $plan->price_yearly, 0, ',', ' ') }} {{ __('FCFA / an') }}
                @if($savings > 0)<em class="pl-save">-{{ $savings }}%</em>@endif
            </p>
        </div>
    </div>

    <a href="{{ route('subscriptions.checkout', array_merge(['plan' => $plan], $checkoutParams)) }}" class="pl-cta">
        @guest {{ __('Choisir ce plan') }} @else {{ __('Souscrire maintenant') }} @endguest <span aria-hidden="true">→</span>
    </a>

    <p class="pl-inc">{{ trans_choice(':count avantage inclus|:count avantages inclus', $included) }}</p>
    <ul class="pl-feats">
        @foreach($features as $feat)
            <li class="{{ $feat['ok'] ? 'is-ok' : 'is-no' }}">
                <span class="pl-tick" aria-hidden="true">{{ $feat['ok'] ? '✓' : '–' }}</span>
                <span>{{ $feat['label'] }}<span class="sr-only">{{ $feat['ok'] ? __(' (inclus)') : __(' (non inclus)') }}</span></span>
            </li>
        @endforeach
    </ul>
</article>
