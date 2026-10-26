<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Proposer un film') }} — {{ __('Cinéma d\'Ivoire') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', [
        'title' => __('Proposer un film').' — '.__('Cinéma d\'Ivoire'),
        'description' => __('Réalisateurs, producteurs, distributeurs : faites découvrir votre film documentaire ou de fiction ivoirien.'),
    ])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .cs { --night: #006B3F; --gold: #EB6E00; padding-bottom: 72px; }
        .cs .ico { flex: none; }
        .cs .ico > svg { width: 100%; height: 100%; }
        .cs span > .ico, .cs a > .ico, .cs button > .ico, .cs dt > .ico, .cs div > .ico { margin: 0; border: 0; border-radius: 0; background: none; }
        .cs a:focus-visible, .cs button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
        .cs-hero { position: relative; overflow: hidden; background: var(--night); color: #fff; }
        .cs-hero .ivm-g { opacity: .07 !important; }
        .cs-hero .ivm-g > * { stroke: #F7C396 !important; }
        .cs-hero__in { position: relative; z-index: 1; max-width: 1080px; margin: 0 auto; padding: 40px 20px 46px; }
        .cs-crumbs { display: flex; gap: 6px; margin: 0 0 12px; padding: 0; list-style: none; font-size: 13px; color: #8AA99B; }
        .cs-crumbs a { color: #F3F8F5; text-decoration: none; }
        .cs-crumbs li + li::before { content: "/"; margin-right: 6px; color: #8AA99B; }
        .cs-h1 { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(30px, 4vw, 48px); line-height: 1.06; color: #fff; }
        .cs-lead { margin: 10px 0 0; max-width: 64ch; font-size: 16.5px; line-height: 1.6; color: #D8E9E0; }
        .cs-steps { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 0; padding: 0; list-style: none; counter-reset: s; }
        .cs-steps li { counter-increment: s; display: inline-flex; align-items: center; gap: 8px; height: 34px; padding: 0 14px 0 6px; border-radius: 999px; background: rgba(255,255,255,.1); font-size: 13.5px; font-weight: 600; }
        .cs-steps li::before { content: counter(s); width: 24px; height: 24px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: var(--gold); color: #0E3B2A; font-size: 12px; font-weight: 800; }

        .cs-wrap { max-width: 1080px; margin: 0 auto; padding: 28px 20px 0; display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 28px; align-items: start; }
        .cs-card { padding: 26px; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; }
        .cs-card + .cs-card { margin-top: 18px; }
        .cs-card h2 { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; font-family: 'Montserrat', serif; font-size: 21px; color: #0E3B2A; }
        .cs-card h2 .n { width: 30px; height: 30px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #F3F8F5; color: #EB6E00; font-family: 'Montserrat', sans-serif; font-size: 14px; font-weight: 800; }
        .cs-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 16px; }
        .cs-grid .is-full { grid-column: 1 / -1; }
        .cs-f label, .cs-f .lbl { display: block; margin-bottom: 6px; font-size: 13.5px; font-weight: 700; color: #0E3B2A; }
        .cs-f label i { font-style: normal; color: #EB6E00; }
        .cs-f input[type=text], .cs-f input[type=email], .cs-f input[type=url], .cs-f input[type=number], .cs-f input[type=tel], .cs-f select, .cs-f textarea { width: 100%; min-height: 46px; padding: 10px 14px; border-radius: 14px; border: 1px solid #D8E9E0; background: #fff; font-size: 15px; color: #0E3B2A; }
        .cs-f textarea { min-height: 140px; resize: vertical; line-height: 1.55; }
        .cs-f input:focus, .cs-f select:focus, .cs-f textarea:focus { outline: none; border-color: #EB6E00; box-shadow: 0 0 0 3px rgba(235,110,0,.15); }
        .cs-f small { display: block; margin-top: 5px; font-size: 12.5px; color: #557466; }
        .cs-f .err { color: #b42318; font-weight: 600; }
        .cs-kinds { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .cs-kind { position: relative; display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 16px; border: 1px solid #D8E9E0; cursor: pointer; }
        .cs-kind input { position: absolute; opacity: 0; pointer-events: none; }
        .cs-kind .ic { width: 44px; height: 44px; flex: none; display: flex; align-items: center; justify-content: center; }
        .cs-kind .ic .ico { width: 22px; height: 22px; }
        .cs-kind b { display: block; font-size: 15px; color: #0E3B2A; }
        .cs-kind span small { font-size: 12.5px; color: #557466; }
        .cs-kind:has(input:checked) { border-color: #EB6E00; box-shadow: inset 0 0 0 1px #EB6E00; background: #FFFFFF; }
        .cs-kind:has(input:focus-visible) { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .cs-check { display: flex; gap: 10px; align-items: flex-start; padding: 14px; border-radius: 14px; background: #F3F8F5; font-size: 14px; line-height: 1.5; color: #0E3B2A; cursor: pointer; }
        .cs-check input { width: 20px; height: 20px; margin-top: 1px; accent-color: #EB6E00; flex: none; }
        .cs-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 52px; padding: 0 28px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15.5px; font-weight: 800; border: 0; cursor: pointer; text-decoration: none; transition: background .15s ease; }
        .cs-btn:hover { background: #B85600; }
        .cs-btn .ico { width: 18px; height: 18px; }
        .cs-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-top: 18px; }
        .cs-actions p { margin: 0; font-size: 13px; color: #557466; }
        .cs-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }

        .cs-ok { display: grid; grid-template-columns: 52px minmax(0, 1fr); gap: 14px; padding: 20px; margin-bottom: 18px; border-radius: 20px; background: #F3F8F5; border: 1px solid #D8E9E0; color: #00874F; }
        .cs-ok .ic { width: 52px; height: 52px; border-radius: 32%; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid color-mix(in srgb, #00874F 22%, transparent); color: #00874F; }
        .cs-ok .ic .ico { width: 26px; height: 26px; }
        .cs-ok h2 { margin: 0; font-size: 18px; }
        .cs-ok p { margin: 4px 0 0; font-size: 14.5px; line-height: 1.5; }
        .cs-errors { margin-bottom: 18px; padding: 14px 18px; border-radius: 16px; background: #fdecea; border: 1px solid #f5c2bd; color: #8c1d13; font-size: 14px; }

        .cs-aside { position: sticky; top: 88px; display: grid; gap: 14px; }
        .cs-note { padding: 20px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; }
        .cs-note h3 { margin: 0 0 10px; font-size: 15px; font-weight: 800; color: #0E3B2A; }
        .cs-note ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; }
        .cs-note li { display: grid; grid-template-columns: 30px minmax(0, 1fr); gap: 10px; font-size: 13.5px; line-height: 1.5; color: #24503D; }
        .cs-note li > .ico { width: 30px; height: 30px; margin: 0; border-radius: 32%; background: var(--ti-ic-tint); border: 1px solid color-mix(in srgb, var(--ti-ic) 22%, transparent); color: var(--ti-ic); }
        .cs-note li > .ico > svg { width: 16px; height: 16px; }
        .cs-note p { margin: 0; font-size: 13.5px; line-height: 1.55; color: #24503D; }

        @media (max-width: 900px) {
            .cs-wrap { grid-template-columns: minmax(0, 1fr); }
            .cs-aside { position: static; }
        }
        @media (max-width: 640px) {
            .cs-hero__in { padding: 16px 16px 20px; }
            .cs-crumbs { font-size: 11.5px; margin-bottom: 8px; }
            .cs-h1 { font-size: 25px; }
            .cs-lead { font-size: 13.5px; line-height: 1.45; }
            .cs-steps { gap: 5px; margin-top: 10px; }
            .cs-steps li { height: 26px; font-size: 11.5px; padding: 0 10px 0 4px; gap: 6px; }
            .cs-steps li::before { width: 18px; height: 18px; font-size: 10.5px; }
            .cs-wrap { padding: 18px 16px 0; gap: 18px; }
            .cs-card { padding: 18px 16px; border-radius: 20px; }
            .cs-grid { grid-template-columns: minmax(0, 1fr); }
            .cs-kinds { grid-template-columns: minmax(0, 1fr); }
            .cs-btn { width: 100%; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.public-top-nav')

<main class="cs">
    <section class="cs-hero" aria-labelledby="cs-title">
        @include('partials.ivorian-motifs', ['culture' => 'baoule'])
        <div class="cs-hero__in">
            <ol class="cs-crumbs" aria-label="{{ __('Fil d\'Ariane') }}">
                <li><a href="{{ route('films.index') }}">{{ __('Cinéma d\'Ivoire') }}</a></li>
                <li>{{ __('Proposer un film') }}</li>
            </ol>
            <h1 class="cs-h1" id="cs-title">{{ __('Proposer un film') }}</h1>
            <p class="cs-lead">{{ __('Vous êtes réalisateur, producteur ou distributeur ? Présentez votre documentaire ou votre fiction : notre équipe vérifie la fiche et les autorisations avant toute publication.') }}</p>
            <ol class="cs-steps">
                <li>{{ __('Vous décrivez le film') }}</li>
                <li>{{ __('Nous vérifions les droits') }}</li>
                <li>{{ __('La fiche est publiée') }}</li>
            </ol>
        </div>
    </section>

    <div class="cs-wrap">
        <div>
            @if(session('submitted'))
                <div class="cs-ok" role="status">
                    <span class="ic"><x-icon name="check-circle" /></span>
                    <div>
                        <h2>{{ __('Merci, « :t » a bien été reçu.', ['t' => session('submitted')]) }}</h2>
                        <p>{{ __('Notre équipe vous recontacte par e-mail après examen de la fiche. Aucun film n\'est publié sans validation.') }}</p>
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div class="cs-errors" role="alert">{{ __('Merci de corriger les champs signalés.') }}</div>
            @endif

            <form method="POST" action="{{ route('films.submit.store') }}" enctype="multipart/form-data" class="cs-f" novalidate>
                @csrf
                <div class="cs-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <section class="cs-card" aria-labelledby="cs-s1">
                    <h2 id="cs-s1"><span class="n">1</span>{{ __('Le film') }}</h2>
                    <div class="cs-grid">
                        <div class="is-full">
                            <span class="lbl">{{ __('Catégorie') }} <i>*</i></span>
                            <div class="cs-kinds">
                                @foreach(['documentaire' => ['videocamera-record', __('Film documentaire'), __('Patrimoine, histoire, société…')], 'fiction' => ['clapperboard-play', __('Film fiction'), __('Long ou court métrage')]] as $k => [$ic, $lb, $hint])
                                    <label class="cs-kind is-{{ $k }}">
                                        <input type="radio" name="kind" value="{{ $k }}" @checked(old('kind', 'documentaire') === $k) required>
                                        <span class="ic"><x-icon name="{{ $ic }}" /></span>
                                        <span><b>{{ $lb }}</b><small>{{ $hint }}</small></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('kind')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div class="is-full">
                            <label for="f-title">{{ __('Titre') }} <i>*</i></label>
                            <input type="text" id="f-title" name="title" value="{{ old('title') }}" maxlength="180" required>
                            @error('title')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-director">{{ __('Réalisateur ou réalisatrice') }} <i>*</i></label>
                            <input type="text" id="f-director" name="director" value="{{ old('director') }}" maxlength="160" required>
                            @error('director')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-genre">{{ __('Genre') }}</label>
                            <input type="text" id="f-genre" name="genre" value="{{ old('genre') }}" maxlength="60" list="f-genres">
                            <datalist id="f-genres">@foreach(collect(\App\Models\Film::GENRES)->flatten()->unique() as $g)<option value="{{ $g }}">@endforeach</datalist>
                            @error('genre')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-year">{{ __('Année') }}</label>
                            <input type="number" id="f-year" name="year" value="{{ old('year') }}" min="1900" max="{{ now()->year + 2 }}" inputmode="numeric">
                            @error('year')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-duration">{{ __('Durée (minutes)') }}</label>
                            <input type="number" id="f-duration" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1" max="900" inputmode="numeric">
                            @error('duration_minutes')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div class="is-full">
                            <label for="f-country">{{ __('Pays de production') }}</label>
                            <input type="text" id="f-country" name="country" value="{{ old('country', 'Côte d\'Ivoire') }}" maxlength="80">
                        </div>
                        <div class="is-full">
                            <label for="f-synopsis">{{ __('Synopsis') }} <i>*</i></label>
                            <textarea id="f-synopsis" name="synopsis" maxlength="4000" required>{{ old('synopsis') }}</textarea>
                            <small>{{ __('40 caractères minimum. Évitez de dévoiler la fin.') }}</small>
                            @error('synopsis')<small class="err">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </section>

                <section class="cs-card" aria-labelledby="cs-s2">
                    <h2 id="cs-s2"><span class="n">2</span>{{ __('Médias et liens') }}</h2>
                    <div class="cs-grid">
                        <div>
                            <label for="f-poster">{{ __('Affiche (format portrait)') }}</label>
                            <input type="file" id="f-poster" name="poster" accept="image/jpeg,image/png,image/webp">
                            <small>{{ __('JPG, PNG ou WebP, 6 Mo maximum.') }}</small>
                            @error('poster')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-director-photo">{{ __('Photo du réalisateur') }}</label>
                            <input type="file" id="f-director-photo" name="director_photo" accept="image/jpeg,image/png,image/webp">
                            <small>{{ __('Portrait cadré sur le visage, format carré conseillé.') }}</small>
                            @error('director_photo')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-trailer">{{ __('Bande-annonce (YouTube ou Vimeo)') }}</label>
                            <input type="url" id="f-trailer" name="trailer_url" value="{{ old('trailer_url') }}" placeholder="https://www.youtube.com/watch?v=…" maxlength="500">
                            @error('trailer_url')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-watch">{{ __('Où regarder le film') }}</label>
                            <input type="url" id="f-watch" name="watch_url" value="{{ old('watch_url') }}" placeholder="{{ __('Plateforme, billetterie, distributeur') }}" maxlength="500">
                            @error('watch_url')<small class="err">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </section>

                <section class="cs-card" id="contact" aria-labelledby="cs-s3" style="scroll-margin-top: 90px">
                    <h2 id="cs-s3"><span class="n">3</span>{{ __('Vos coordonnées') }}</h2>
                    <div class="cs-grid">
                        <div class="is-full">
                            <label for="f-name">{{ __('Nom et structure') }} <i>*</i></label>
                            <input type="text" id="f-name" name="submitter_name" value="{{ old('submitter_name') }}" maxlength="160" autocomplete="name" required>
                            @error('submitter_name')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-email">{{ __('E-mail') }} <i>*</i></label>
                            <input type="email" id="f-email" name="submitter_email" value="{{ old('submitter_email') }}" maxlength="190" autocomplete="email" required>
                            @error('submitter_email')<small class="err">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label for="f-phone">{{ __('Téléphone') }}</label>
                            <input type="tel" id="f-phone" name="submitter_phone" value="{{ old('submitter_phone') }}" maxlength="40" autocomplete="tel">
                        </div>
                        <div class="is-full">
                            <label class="cs-check">
                                <input type="checkbox" name="rights_confirmed" value="1" @checked(old('rights_confirmed')) required>
                                <span>{{ __('Je confirme détenir les droits sur ce film, ou l\'autorisation des ayants droit, pour présenter sa fiche, son affiche et sa bande-annonce sur :s.', ['s' => $siteBrand['site_name']]) }}</span>
                            </label>
                            @error('rights_confirmed')<small class="err">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="cs-actions">
                        <button type="submit" class="cs-btn"><x-icon name="upload" />{{ __('Envoyer la proposition') }}</button>
                        <p>{{ __('Vos coordonnées ne sont pas publiées.') }}</p>
                    </div>
                </section>
            </form>
        </div>

        <aside class="cs-aside">
            <div class="cs-note">
                <h3>{{ __('Ce que vous obtenez') }}</h3>
                <ul>
                    <li><x-icon name="clapperboard-play" />{{ __('Une fiche film complète, optimisée pour Google et le partage sur les réseaux.') }}</li>
                    <li><x-icon name="play-circle" />{{ __('Votre bande-annonce intégrée et un lien direct vers votre plateforme.') }}</li>
                    <li><x-icon name="calendar" />{{ __('Vos dates de projection et de festival mises en avant.') }}</li>
                </ul>
            </div>
            <div class="cs-note">
                <h3>{{ __('Droits et retrait') }}</h3>
                <p>{{ __('Nous ne diffusons aucun film sans autorisation : seuls la fiche, l\'affiche et la bande-annonce officielles sont présentées. Pour modifier ou retirer une fiche, écrivez-nous via ce formulaire en précisant le titre.') }}</p>
            </div>
        </aside>
    </div>
</main>

@include('partials.homepage-footer')
</body>
</html>
