{{--
    Carte peuple commune (/cultures et section « Cultures Ivoiriennes » de l'accueil).
    Paramètres : $people (App\Models\CulturalPeople), $headingTag ('h2' | 'h3', défaut h3).
    Carte « d'identité culturelle » : photo, ou monogramme sur aplat avec motif propre à chaque peuple.
    Styles : .ti-people-card dans partials.ui-kit.
--}}
@php
    $headingTag = $headingTag ?? 'h3';
    $peopleImage = $people->cover_image ?: $people->thumbnail;
    $population = (int) $people->population_estimee;
    $populationLabel = match (true) {
        $population >= 1000000 => '≈ '.str_replace('.', ',', rtrim(rtrim(number_format($population / 1000000, 1, '.', ''), '0'), '.')).' M',
        $population > 0 => '≈ '.number_format($population, 0, ',', ' '),
        default => null,
    };
    // Sans photo : couleur et motif stables par peuple (aplats du logo).
    $palette = [['#EB6E00', 'diamond'], ['#00874F', 'cross'], ['#00874F', 'ring'], ['#B85600', 'diamond'], ['#00874F', 'ring'], ['#B85600', 'cross']];
    [$pColor, $pMotif] = $palette[crc32($people->slug ?: $people->name) % count($palette)];
    $pPatternId = 'pp-'.$people->id;
@endphp
<a href="{{ route('cultural.people', $people->slug) }}" class="ti-people-card group">
    <div class="ti-people-card__media" @unless($peopleImage) style="background: {{ $pColor }}" @endunless>
        @if($peopleImage)
            <img src="{{ $peopleImage }}" alt="" loading="lazy" decoding="async">
        @else
            <svg class="ti-people-card__pattern" width="100%" height="100%" aria-hidden="true">
                <defs>
                    <pattern id="{{ $pPatternId }}" width="28" height="28" patternUnits="userSpaceOnUse">
                        @if($pMotif === 'diamond')<path d="M14 2 26 14 14 26 2 14Z" fill="none" stroke="#fff" stroke-width="2"/>
                        @elseif($pMotif === 'cross')<path d="M0 14h28M14 0v28" fill="none" stroke="#fff" stroke-width="2"/>
                        @else<circle cx="14" cy="14" r="6" fill="none" stroke="#fff" stroke-width="2"/>@endif
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#{{ $pPatternId }})"/>
            </svg>
            <span class="ti-people-card__initial" aria-hidden="true">{{ mb_substr($people->name, 0, 1) }}</span>
        @endif

        <span class="ti-people-card__top">
            @if($people->is_featured)
                <span class="ti-people-card__pill is-featured"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ __('À la une') }}</span>
            @else
                <span></span>
            @endif
            @if($people->zone_geographique)
                <span class="ti-people-card__pill"><i class="far fa-compass" aria-hidden="true"></i>{{ $people->zone_geographique }}</span>
            @endif
        </span>
        @unless($peopleImage)
            <span class="ti-people-card__overname" aria-hidden="true">{{ $people->name }}</span>
        @endunless
    </div>

    <div class="ti-people-card__body">
        <{{ $headingTag }} class="ti-people-card__name @unless($peopleImage) sr-only @endunless">{{ $people->name }}</{{ $headingTag }}>
        @if($people->capitale_culturelle)
            <p class="ti-people-card__capital"><i class="fas fa-landmark" aria-hidden="true"></i>{{ __('Capitale culturelle :') }} <strong>{{ $people->capitale_culturelle }}</strong></p>
        @endif
        @if($populationLabel || $people->famille_linguistique)
            <dl class="ti-people-card__stats">
                @if($populationLabel)
                    <div><dt><i class="fas fa-users" aria-hidden="true"></i>{{ __('Habitants') }}</dt><dd>{{ $populationLabel }}</dd></div>
                @endif
                @if($people->famille_linguistique)
                    <div><dt><i class="far fa-comment" aria-hidden="true"></i>{{ __('Langues') }}</dt><dd>{{ $people->famille_linguistique }}</dd></div>
                @endif
            </dl>
        @endif
    </div>

    <div class="ti-people-card__footer">
        <span>{{ __('Découvrir le peuple') }}</span>
        <span class="ti-people-card__go" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
    </div>
</a>
