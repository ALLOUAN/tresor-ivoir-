{{--
    Carte d'un forfait au sein du panneau d'une catégorie.
    Attend : $plan, $i (index dans la catégorie), $count (nb de forfaits de la catégorie), $category (ProviderCategory racine courante).
--}}
@php
    $isPopular  = $plan->code === 'silver' || ($i === 1 && $count >= 2);
    $icons      = ['fa-seedling','fa-star','fa-gem'];
    $iconColors = ['text-emerald-400','text-gold-400','text-green-400'];
    $icon       = $icons[$i % 3];
    $iconColor  = $iconColors[$i % 3];
    $savings    = $plan->price_monthly > 0
        ? round(100 - ($plan->price_yearly / ($plan->price_monthly * 12) * 100))
        : 0;
    // Un forfait générique n'indique pas de lui-même la catégorie choisie par le visiteur : on la
    // transmet pour que la fiche prestataire créée à l'inscription soit rattachée à la bonne catégorie.
    $checkoutParams = $plan->provider_category_id ? [] : ['categorie' => $category->slug];
@endphp

<div class="plan-card relative flex flex-col rounded-2xl border p-6
    {{ $isPopular ? 'plan-popular border-gold-500/40 bg-gradient-to-b from-dark-700 to-dark-800' : 'border-white/8 bg-dark-800/60' }}">

    @if($isPopular)
    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gold-500 text-dark-900 text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest whitespace-nowrap">
        ⭐ Plus populaire
    </div>
    @endif

    {{-- En-tête plan --}}
    <div class="mb-6">
        <div class="w-12 h-12 rounded-xl {{ $isPopular ? 'bg-gold-500/15' : 'bg-white/5' }} flex items-center justify-center mb-4">
            <i class="fas {{ $icon }} {{ $iconColor }} text-xl"></i>
        </div>
        <h2 class="font-serif text-xl font-bold mb-1">{{ $plan->name_fr }}</h2>
        <p class="text-gray-500 text-sm leading-relaxed">{{ $plan->benefits_text ?: 'Boostez votre visibilité auprès de milliers de voyageurs.' }}</p>
    </div>

    {{-- Prix --}}
    <div class="mb-6">
        <div class="price-monthly">
            <div class="flex items-end gap-1">
                <span class="font-serif text-3xl font-bold">{{ number_format((float)$plan->price_monthly, 0, ',', ' ') }}</span>
                <span class="text-gray-500 text-sm mb-1">FCFA / mois</span>
            </div>
            <p class="text-gray-600 text-xs mt-1">Engagement mensuel, résiliable à tout moment</p>
        </div>
        <div class="price-yearly hidden">
            <div class="flex items-end gap-1">
                <span class="font-serif text-3xl font-bold">{{ number_format((float)($plan->price_yearly / 12), 0, ',', ' ') }}</span>
                <span class="text-gray-500 text-sm mb-1">FCFA / mois</span>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-gray-600 text-xs">soit {{ number_format((float)$plan->price_yearly, 0, ',', ' ') }} FCFA / an</p>
                @if($savings > 0)
                <span class="text-[10px] bg-gold-500/15 text-gold-400 px-1.5 py-0.5 rounded-full font-semibold">-{{ $savings }}%</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Features --}}
    <ul class="space-y-2.5 mb-8 flex-1">
        @php
            if (!empty($plan->features_json)) {
                // Fonctionnalités définies depuis le back-office
                $features = array_map(fn($f) => [
                    'label' => $f['label'] ?? '',
                    'ok'    => (bool) ($f['included'] ?? false),
                ], $plan->features_json);
            } else {
                // Fallback sur les champs booléens du plan
                $features = [
                    ['label' => 'Badge vérifié',           'ok' => $plan->has_verified_badge],
                    ['label' => 'Photos ('.$plan->photos_limit.')', 'ok' => $plan->photos_limit > 0],
                    ['label' => 'Vidéo de présentation',   'ok' => $plan->has_video],
                    ['label' => 'Mise en avant accueil',   'ok' => $plan->has_homepage],
                    ['label' => 'Campagne newsletter',     'ok' => $plan->has_newsletter],
                    ['label' => 'Posts réseaux sociaux',   'ok' => $plan->has_social_posts],
                    ['label' => 'Statistiques avancées',   'ok' => in_array($plan->stats_level, ['advanced', 'full'])],
                    ['label' => 'Support prioritaire',     'ok' => in_array($plan->support_level, ['chat', 'dedicated'])],
                ];
            }
        @endphp
        @foreach($features as $feat)
        <li class="flex items-center gap-2.5 text-sm {{ $feat['ok'] ? 'text-gray-200' : 'text-gray-600' }}">
            <i class="fas {{ $feat['ok'] ? 'fa-check feature-check' : 'fa-xmark feature-cross' }} text-xs w-4 text-center"></i>
            {{ $feat['label'] }}
        </li>
        @endforeach
    </ul>

    {{-- CTA --}}
    <a href="{{ route('subscriptions.checkout', array_merge(['plan' => $plan], $checkoutParams)) }}"
       class="block text-center py-3 rounded-xl font-bold text-sm transition-all duration-200
       {{ $isPopular
           ? 'text-dark-900 hover:opacity-90'
           : 'border border-gold-500/30 text-gold-300 hover:border-gold-400/60 hover:bg-gold-500/5' }}"
       @if($isPopular) style="background:#f2790f" @endif>
        @guest Choisir ce plan @else Souscrire maintenant @endguest
        <i class="fas fa-arrow-right text-xs ml-1"></i>
    </a>
</div>
