<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Commande sur mesure — :a', ['a' => $profile->display_name]) }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="robots" content="noindex">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        /* Commande sur mesure : formulaire en étapes numérotées + récapitulatif artiste. Charte du site, aplats. */
        .cm { max-width: 1180px; margin: 0 auto; padding: 22px 20px 72px; }
        .cm-back { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; color: #B85600; font-size: 13px; font-weight: 700; text-decoration: none; }
        .cm-back:hover { border-color: #EB6E00; }
        .cm-head { margin: 22px 0 26px; max-width: 760px; }
        .cm-kicker { margin: 0; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #EB6E00; }
        .cm-kicker .ico { font-size: 16px; }
        .cm-title { margin: 10px 0 0; font-family: 'Montserrat', serif; font-size: clamp(32px, 2.4vw + 18px, 52px); font-weight: 700; line-height: 1.05; letter-spacing: -.015em; color: #0E3B2A; }
        .cm-lead { margin: 12px 0 0; font-size: 16px; line-height: 1.65; color: #24503D; }

        .cm-grid { display: grid; grid-template-columns: minmax(0, 1fr) 370px; gap: 28px; align-items: start; }
        .cm-form { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
        .cm-block { padding: 24px; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; }
        .cm-block__head { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
        .cm-num { flex: none; width: 34px; height: 34px; border-radius: 999px; background: #00874F; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; }
        .cm-block__head h2 { margin: 0; font-family: 'Montserrat', serif; font-size: 21px; font-weight: 700; color: #0E3B2A; }
        .cm-block__head p { margin: 2px 0 0; font-size: 13px; color: #557466; }
        .cm-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .cm-field { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
        .cm-field.is-wide { grid-column: 1 / -1; }
        .cm-field > span:first-child { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; font-size: 13.5px; font-weight: 700; color: #0E3B2A; }
        .cm-field > span:first-child em { font-style: normal; font-size: 12px; font-weight: 600; color: #557466; }
        .cm-input { width: 100%; min-height: 50px; padding: 12px 15px; border-radius: 14px; border: 1.5px solid #D8E9E0; background: #FFFFFF; color: #0E3B2A; font: inherit; font-size: 15px; transition: border-color .15s ease, background .15s ease; }
        textarea.cm-input { min-height: 150px; resize: vertical; line-height: 1.6; }
        .cm-input::placeholder { color: #8AA99B; }
        .cm-input:hover { border-color: #8AA99B; }
        .cm-input:focus { outline: none; border-color: #EB6E00; background: #fff; box-shadow: 0 0 0 4px rgba(235,110,0,.12); }
        .cm-input.is-bad { border-color: #EB6E00; background: #FFFFFF; }
        .cm-err { margin: 0; font-size: 12.5px; font-weight: 600; color: #EB6E00; }
        .cm-hint { margin: 0; font-size: 12.5px; color: #557466; }
        .cm-count.is-low { color: #EB6E00; }
        .cm-count.is-ok { color: #00874F; }
        .cm-sugg { display: flex; flex-wrap: wrap; gap: 6px; }
        .cm-sugg button { height: 32px; padding: 0 12px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; color: #24503D; font-size: 12.5px; font-weight: 600; cursor: pointer; }
        .cm-sugg button:hover { border-color: #EB6E00; color: #B85600; }
        .cm-sugg button.is-on { background: #F3F8F5; border-color: #EB6E00; color: #B85600; }
        .cm-money { position: relative; }
        .cm-money .cm-input { padding-right: 70px; }
        .cm-money b { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 13px; font-weight: 800; color: #557466; pointer-events: none; }

        .cm-drop { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 26px 18px; border-radius: 18px; border: 1.5px dashed #8AA99B; background: #FFFFFF; text-align: center; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
        .cm-drop:hover, .cm-drop.is-over { border-color: #EB6E00; background: #F3F8F5; }
        .cm-drop input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .cm-drop .ico { font-size: 30px; color: #EB6E00; }
        .cm-drop b { font-size: 14.5px; color: #0E3B2A; }
        .cm-drop small { font-size: 12.5px; color: #557466; }
        .cm-drop:focus-within { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .cm-thumbs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
        .cm-thumbs:empty { display: none; }
        .cm-thumbs figure { margin: 0; aspect-ratio: 1; border-radius: 14px; overflow: hidden; background: #F3F8F5; }
        .cm-thumbs img { width: 100%; height: 100%; object-fit: cover; }

        .cm-submit { display: flex; flex-direction: column; gap: 12px; padding: 22px 24px; border-radius: 24px; background: #F3F8F5; border: 1px solid #D8E9E0; }
        .cm-submit p { margin: 0; display: flex; gap: 10px; font-size: 13.5px; line-height: 1.55; color: #24503D; }
        .cm-submit p .ico { flex: none; font-size: 19px; color: #00874F; }
        .cm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; height: 54px; border-radius: 999px; border: 0; background: #EB6E00; color: #fff; font-size: 15.5px; font-weight: 800; text-decoration: none; cursor: pointer; transition: background .15s ease; }
        .cm-btn:hover { background: #B85600; color: #fff; }
        .cm-btn .ico { font-size: 19px; }
        .cm-btn:focus-visible, .cm-sugg button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .cm-btn[disabled] { opacity: .7; cursor: progress; }
        .cm-alert { display: flex; gap: 10px; padding: 14px 16px; border-radius: 16px; background: #FFFFFF; border: 1px solid #F7C396; color: #B85600; font-size: 14px; }
        .cm-alert .ico { flex: none; font-size: 19px; }
        .cm-note { padding: 28px; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; text-align: center; }
        .cm-note .ico { font-size: 40px; color: #EB6E00; }
        .cm-note h2 { margin: 10px 0 0; font-family: 'Montserrat', serif; font-size: 24px; color: #0E3B2A; }
        .cm-note p { margin: 8px auto 18px; max-width: 420px; font-size: 14.5px; line-height: 1.6; color: #24503D; }

        /* Récapitulatif artiste */
        .cm-side { position: sticky; top: 140px; display: flex; flex-direction: column; gap: 14px; }
        .cm-artist { border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; overflow: hidden; }
        .cm-artist__top { display: flex; align-items: center; gap: 14px; padding: 20px 20px 16px; text-decoration: none; color: inherit; }
        .cm-avatar { flex: none; width: 60px; height: 60px; border-radius: 999px; overflow: hidden; background: #00874F; color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Montserrat', serif; font-size: 22px; font-weight: 700; border: 3px solid #F3F8F5; }
        .cm-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .cm-artist__top b { display: block; font-family: 'Montserrat', serif; font-size: 19px; line-height: 1.2; color: #0E3B2A; }
        .cm-artist__top small { display: block; margin-top: 2px; font-size: 13px; color: #557466; }
        .cm-price { padding: 14px 20px; border-top: 1px solid #F3F8F5; border-bottom: 1px solid #F3F8F5; background: #FFFFFF; }
        .cm-price small { font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #557466; }
        .cm-price strong { display: block; margin-top: 2px; font-family: 'Montserrat', serif; font-size: 28px; color: #0E3B2A; }
        .cm-facts { margin: 0; padding: 16px 20px 20px; list-style: none; display: flex; flex-direction: column; gap: 11px; font-size: 14px; color: #24503D; }
        .cm-facts li { display: flex; align-items: flex-start; gap: 10px; line-height: 1.45; }
        .cm-facts .ico { flex: none; font-size: 18px; color: #EB6E00; margin-top: 1px; }
        .cm-steps { padding: 20px; border-radius: 24px; background: #00874F; color: #fff; }
        .cm-steps h3 { margin: 0 0 14px; font-size: 13px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #D8E9E0; }
        .cm-steps ol { margin: 0; padding: 0; list-style: none; position: relative; display: flex; flex-direction: column; gap: 14px; }
        .cm-steps ol::before { content: ''; position: absolute; left: 13px; top: 8px; bottom: 8px; width: 2px; background: rgba(255,255,255,.18); }
        .cm-steps li { position: relative; display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: 12px; align-items: start; font-size: 14px; line-height: 1.45; }
        .cm-steps li > b { position: relative; z-index: 1; width: 28px; height: 28px; border-radius: 999px; background: #fff; color: #00874F; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800; }
        .cm-steps li strong { display: block; font-size: 14.5px; }
        .cm-steps li span span { display: block; margin-top: 2px; font-size: 12.5px; color: #D8E9E0; }

        @media (max-width: 1023px) {
            .cm-grid { grid-template-columns: 1fr; }
            .cm-side { position: static; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .cm { padding: 16px 16px 56px; }
            .cm-head { margin: 16px 0 20px; }
            .cm-lead { font-size: 15px; }
            .cm-block { padding: 18px; border-radius: 20px; }
            .cm-fields { grid-template-columns: 1fr; gap: 14px; }
            .cm-thumbs { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
            .cm-side { grid-template-columns: 1fr; }
            .cm-submit { padding: 18px; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $initials = collect(preg_split('/\s+/u', trim((string) $profile->display_name)))
        ->reject(fn ($w) => in_array(mb_strtolower($w), ['atelier', 'studio', 'galerie', 'de', 'du', 'la', 'le', 'les'], true))
        ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: mb_strtoupper(mb_substr((string) $profile->display_name, 0, 1));
    $fail = fn (string $f) => $errors->first($f);
    $globalError = $errors->art->first() ?: ($errors->any() && ! collect(['subject', 'brief', 'format', 'medium', 'palette', 'budget_xof', 'wanted_by', 'references', 'references.0', 'references.1', 'references.2', 'references.3'])->contains(fn ($f) => $errors->has($f)) ? $errors->first() : null);
    $refError = $errors->first('references') ?: collect(range(0, 3))->map(fn ($i) => $errors->first('references.'.$i))->filter()->first();
@endphp
<main class="cm">
    <a href="{{ route('artist.show', $profile->slug) }}" class="cm-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ $profile->display_name }}</a>

    <header class="cm-head">
        <p class="cm-kicker"><x-icon name="palette" />{{ __('Commande sur mesure') }}</p>
        <h1 class="cm-title">{{ __('Votre œuvre sur mesure') }}</h1>
        <p class="cm-lead">{{ __('Décrivez votre idée à :a. Vous recevez un devis sans engagement ; l\'acompte n\'est demandé qu\'après votre accord.', ['a' => $profile->display_name]) }}</p>
    </header>

    <div class="cm-grid">
        <div class="cm-form">
            @auth
                @if(auth()->user()->role !== 'visitor')
                    <div class="cm-note">
                        <x-icon name="user-id" />
                        <h2>{{ __('Réservé aux comptes visiteurs') }}</h2>
                        <p>{{ __('Les commandes se font avec un compte visiteur.') }}</p>
                        <a href="{{ route('artist.show', $profile->slug) }}" class="cm-btn" style="max-width:320px;margin:0 auto">{{ __('Retour à l\'artiste') }}</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('artist.commission.store', $profile->slug) }}" enctype="multipart/form-data" class="cm-form" data-cm-form novalidate>
                        @csrf
                        @if($globalError)<p class="cm-alert" role="alert"><x-icon name="danger-circle" />{{ $globalError }}</p>@endif

                        {{-- 1. Le projet --}}
                        <section class="cm-block">
                            <div class="cm-block__head"><span class="cm-num">1</span><div><h2>{{ __('Votre projet') }}</h2><p>{{ __('Le sujet et ce que vous imaginez') }}</p></div></div>
                            <div class="cm-fields">
                                <label class="cm-field is-wide">
                                    <span>{{ __('Sujet') }}</span>
                                    <input name="subject" required maxlength="200" value="{{ old('subject') }}" placeholder="{{ __('ex : Portrait de famille, paysage de Grand-Bassam') }}" class="cm-input {{ $fail('subject') ? 'is-bad' : '' }}" @if($fail('subject')) aria-invalid="true" @endif>
                                    @if($fail('subject'))<p class="cm-err">{{ $fail('subject') }}</p>@endif
                                </label>
                                <label class="cm-field is-wide">
                                    <span>{{ __('Décrivez votre projet') }}<em class="cm-count" data-cm-count>0 / 3000</em></span>
                                    <textarea name="brief" required minlength="20" maxlength="3000" rows="6" class="cm-input {{ $fail('brief') ? 'is-bad' : '' }}" data-cm-brief placeholder="{{ __('Ce que vous imaginez, le lieu où l\'œuvre sera accrochée, l\'ambiance souhaitée…') }}" @if($fail('brief')) aria-invalid="true" @endif>{{ old('brief') }}</textarea>
                                    @if($fail('brief'))<p class="cm-err">{{ $fail('brief') }}</p>@else<p class="cm-hint">{{ __('20 caractères minimum. Plus c\'est précis, plus le devis est juste.') }}</p>@endif
                                </label>
                            </div>
                        </section>

                        {{-- 2. Les détails --}}
                        <section class="cm-block">
                            <div class="cm-block__head"><span class="cm-num">2</span><div><h2>{{ __('Les détails') }}</h2><p>{{ __('Facultatif : touchez une suggestion ou écrivez la vôtre') }}</p></div></div>
                            <div class="cm-fields">
                                <label class="cm-field">
                                    <span>{{ __('Format') }}</span>
                                    <input name="format" maxlength="80" value="{{ old('format') }}" placeholder="80 × 60 cm" class="cm-input" data-cm-target="format">
                                    <span class="cm-sugg" data-cm-sugg="format">@foreach(['40 × 50 cm', '60 × 80 cm', '100 × 100 cm'] as $s)<button type="button">{{ $s }}</button>@endforeach</span>
                                    @if($fail('format'))<p class="cm-err">{{ $fail('format') }}</p>@endif
                                </label>
                                <label class="cm-field">
                                    <span>{{ __('Technique') }}</span>
                                    <input name="medium" maxlength="80" value="{{ old('medium') }}" placeholder="{{ __('Acrylique sur toile') }}" class="cm-input" data-cm-target="medium">
                                    <span class="cm-sugg" data-cm-sugg="medium">@foreach([__('Acrylique sur toile'), __('Huile sur toile'), __('Technique mixte')] as $s)<button type="button">{{ $s }}</button>@endforeach</span>
                                    @if($fail('medium'))<p class="cm-err">{{ $fail('medium') }}</p>@endif
                                </label>
                                <label class="cm-field is-wide">
                                    <span>{{ __('Couleurs') }}</span>
                                    <input name="palette" maxlength="120" value="{{ old('palette') }}" placeholder="{{ __('Ocres, bleu nuit') }}" class="cm-input">
                                    @if($fail('palette'))<p class="cm-err">{{ $fail('palette') }}</p>@endif
                                </label>
                            </div>
                        </section>

                        {{-- 3. Budget et délai --}}
                        <section class="cm-block">
                            <div class="cm-block__head"><span class="cm-num">3</span><div><h2>{{ __('Budget et délai') }}</h2><p>{{ __('Pour que l\'artiste vous propose un devis adapté') }}</p></div></div>
                            <div class="cm-fields">
                                <label class="cm-field">
                                    <span>{{ __('Budget') }}@if($profile->commission_from_xof)<em>{{ __('dès :a', ['a' => number_format($profile->commission_from_xof, 0, ',', ' ')]) }} FCFA</em>@endif</span>
                                    <span class="cm-money"><input type="number" name="budget_xof" min="0" step="1000" inputmode="numeric" value="{{ old('budget_xof') }}" placeholder="{{ $profile->commission_from_xof ? number_format($profile->commission_from_xof, 0, '', '') : '150000' }}" class="cm-input {{ $fail('budget_xof') ? 'is-bad' : '' }}"><b>FCFA</b></span>
                                    @if($fail('budget_xof'))<p class="cm-err">{{ $fail('budget_xof') }}</p>@endif
                                </label>
                                <label class="cm-field">
                                    <span>{{ __('Souhaitée pour le') }}</span>
                                    <input type="date" name="wanted_by" min="{{ now()->addDay()->toDateString() }}" value="{{ old('wanted_by') }}" class="cm-input {{ $fail('wanted_by') ? 'is-bad' : '' }}">
                                    @if($fail('wanted_by'))<p class="cm-err">{{ $fail('wanted_by') }}</p>@endif
                                </label>
                            </div>
                        </section>

                        {{-- 4. Références --}}
                        <section class="cm-block">
                            <div class="cm-block__head"><span class="cm-num">4</span><div><h2>{{ __('Photos de référence') }}</h2><p>{{ __('Une pièce, une ambiance, une photo à peindre… (4 max.)') }}</p></div></div>
                            <label class="cm-drop" data-cm-drop>
                                <input type="file" name="references[]" accept="image/*" multiple data-cm-files aria-label="{{ __('Photos de référence (4 max.)') }}">
                                <x-icon name="upload" />
                                <b>{{ __('Glissez vos images ici ou touchez pour choisir') }}</b>
                                <small data-cm-files-label>{{ __('JPG, PNG ou WebP · 6 Mo max. par image') }}</small>
                            </label>
                            <div class="cm-thumbs" data-cm-thumbs></div>
                            @if($refError)<p class="cm-err mt-2">{{ $refError }}</p>@endif
                        </section>

                        <div class="cm-submit">
                            <p><x-icon name="shield-check" />{{ __('L\'acompte (:p %) n\'est demandé qu\'après acceptation du devis. :n retouches de l\'esquisse sont incluses.', ['p' => $profile->commission_deposit_percent, 'n' => \App\Models\ArtCommission::MAX_REVISIONS]) }}</p>
                            <button class="cm-btn" data-cm-submit><x-icon name="plane" />{{ __('Envoyer ma demande') }}</button>
                        </div>
                    </form>
                @endif
            @else
                <div class="cm-note">
                    <x-icon name="user-rounded" />
                    <h2>{{ __('Connectez-vous pour commander') }}</h2>
                    <p>{{ __('Votre demande et le devis de l\'artiste sont suivis depuis votre espace visiteur.') }}</p>
                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="cm-btn" style="max-width:340px;margin:0 auto">{{ __('Se connecter pour commander') }}</a>
                </div>
            @endauth
        </div>

        <aside class="cm-side" aria-label="{{ __('Récapitulatif') }}">
            <div class="cm-artist">
                <a href="{{ route('artist.show', $profile->slug) }}" class="cm-artist__top">
                    <span class="cm-avatar">@if($profile->photo)<img src="{{ $profile->photo }}" alt="">@else<span aria-hidden="true">{{ $initials }}</span>@endif</span>
                    <span class="min-w-0"><b>{{ $profile->display_name }}</b>@if($profile->tagline)<small>{{ $profile->tagline }}</small>@endif</span>
                </a>
                <div class="cm-price">
                    <small>{{ __('À partir de') }}</small>
                    <strong>{{ $profile->commission_from_xof ? number_format($profile->commission_from_xof, 0, ',', ' ').' FCFA' : __('Sur devis') }}</strong>
                </div>
                <ul class="cm-facts">
                    @if($profile->commission_deposit_percent)<li><x-icon name="wallet-money" />{{ __('Acompte de :p % après accord sur le devis', ['p' => $profile->commission_deposit_percent]) }}</li>@endif
                    <li><x-icon name="restart" />{{ __(':n retouches de l\'esquisse incluses', ['n' => \App\Models\ArtCommission::MAX_REVISIONS]) }}</li>
                    @if($profile->returns_accepted)<li><x-icon name="shield-check" />{{ __('Retours acceptés') }}</li>@endif
                    @if($profile->abidjan_delivery_xof !== null)<li><x-icon name="delivery" />{{ __('Livraison à Abidjan') }}</li>@endif
                </ul>
            </div>
            <div class="cm-steps">
                <h3>{{ __('Comment ça marche') }}</h3>
                <ol>
                    <li><b>1</b><span><strong>{{ __('Vous décrivez le projet') }}</strong><span>{{ __('Gratuit et sans engagement') }}</span></span></li>
                    <li><b>2</b><span><strong>{{ __('L\'artiste envoie son devis') }}</strong><span>{{ __('Dans votre espace visiteur') }}</span></span></li>
                    <li><b>3</b><span><strong>{{ __('Acompte et esquisse à valider') }}</strong><span>{{ __('Retouches incluses') }}</span></span></li>
                    <li><b>4</b><span><strong>{{ __('Solde et remise de l\'œuvre') }}</strong><span>{{ __('Retrait ou livraison') }}</span></span></li>
                </ol>
            </div>
        </aside>
    </div>
</main>
@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
(function () {
    const form = document.querySelector('[data-cm-form]');
    if (!form) return;
    // Compteur de la description (20 minimum)
    const brief = form.querySelector('[data-cm-brief]'), count = form.querySelector('[data-cm-count]');
    const upd = () => { const n = brief.value.length; count.textContent = n + ' / 3000'; count.classList.toggle('is-low', n > 0 && n < 20); count.classList.toggle('is-ok', n >= 20); };
    brief.addEventListener('input', upd); upd();
    // Suggestions qui remplissent le champ
    form.querySelectorAll('[data-cm-sugg]').forEach(box => {
        const input = form.querySelector('[data-cm-target="' + box.dataset.cmSugg + '"]');
        const sync = () => box.querySelectorAll('button').forEach(b => b.classList.toggle('is-on', b.textContent.trim() === input.value.trim()));
        box.querySelectorAll('button').forEach(b => b.addEventListener('click', e => { e.preventDefault(); input.value = b.textContent.trim(); sync(); }));
        input.addEventListener('input', sync); sync();
    });
    // Zone de dépôt + aperçus (4 max.)
    const drop = form.querySelector('[data-cm-drop]'), files = form.querySelector('[data-cm-files]'), thumbs = form.querySelector('[data-cm-thumbs]'), label = form.querySelector('[data-cm-files-label]');
    const defaultLabel = label.textContent;
    const T = @json(['many' => __('Vous ne pouvez joindre que 4 images.'), 'n' => __(':n image(s) sélectionnée(s)')]);
    files.addEventListener('change', () => {
        if (files.files.length > 4) {
            const dt = new DataTransfer(); [...files.files].slice(0, 4).forEach(f => dt.items.add(f)); files.files = dt.files; alert(T.many);
        }
        thumbs.innerHTML = '';
        [...files.files].forEach(f => { const fig = document.createElement('figure'); const img = document.createElement('img'); img.alt = ''; img.src = URL.createObjectURL(f); fig.appendChild(img); thumbs.appendChild(fig); });
        label.textContent = files.files.length ? T.n.replace(':n', files.files.length) : defaultLabel;
    });
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, () => drop.classList.add('is-over')));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, () => drop.classList.remove('is-over')));
    // Anti double envoi
    form.addEventListener('submit', e => {
        if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
        form.querySelector('[data-cm-submit]').disabled = true;
    });
})();
</script>
</body>
</html>
