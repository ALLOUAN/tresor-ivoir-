<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $artwork->title }} — {{ __('Art & Créations') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $artwork->description), 155) }}">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        /* Champs et boutons du module d'achat */
        .field-input { width: 100%; background: #fff; border: 1.5px solid #D8E9E0; border-radius: 12px; padding: 11px 13px; font-size: 14px; color: #0E3B2A; }
        .field-input:focus { outline: none; border-color: #EB6E00; box-shadow: 0 0 0 3px rgba(235,110,0,.16); }
        .btn-primary { background: #EB6E00; color: #fff; font-weight: 700; }
        .btn-primary:hover { background: #B85600; }

        /* Galerie : l'œuvre n'est jamais recadrée (object-fit: contain sur un mur neutre). */
        .aw-stage { position: relative; display: flex; align-items: center; justify-content: center; aspect-ratio: 4 / 3.4; padding: clamp(18px, 4vw, 48px); border-radius: 28px; background: #D8E9E0; overflow: hidden; }
        .aw-stage img { width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 16px 22px rgba(14,59,42,.32)); cursor: zoom-in; }
        .aw-stage__btn { position: absolute; display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 999px; border: 0; background: rgba(255,255,255,.95); color: #0E3B2A; font-size: 13px; font-weight: 700; box-shadow: 0 6px 18px rgba(14,59,42,.18); cursor: pointer; }
        .aw-stage__btn:hover { background: #fff; color: #B85600; }
        .aw-stage__btn:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .aw-stage__zoom { right: 16px; bottom: 16px; width: 40px; padding: 0; justify-content: center; }
        .aw-stage__count { left: 16px; bottom: 16px; height: 32px; cursor: default; font-weight: 600; }
        .aw-thumbs { display: grid; grid-template-columns: repeat(auto-fill, minmax(72px, 1fr)); gap: 8px; margin-top: 10px; }
        .aw-thumb { aspect-ratio: 1; padding: 0; border: 2px solid transparent; border-radius: 14px; overflow: hidden; background: #D8E9E0; cursor: pointer; }
        .aw-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .aw-thumb.is-active { border-color: #EB6E00; }
        .aw-thumb:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

        /* Colonne d'achat */
        .aw-side { position: sticky; top: 92px; }
        .aw-title { margin: 8px 0 0; font-family: 'Montserrat', serif; font-size: clamp(30px, 2.4vw + 14px, 44px); font-weight: 700; line-height: 1.08; letter-spacing: -.015em; }
        .aw-artist { display: flex; align-items: center; gap: 10px; margin: 12px 0 0; color: #24503D; font-size: 14px; }
        .aw-artist img, .aw-artist__ph { width: 36px; height: 36px; flex: none; border-radius: 999px; object-fit: cover; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; }
        .aw-artist a { font-weight: 700; color: #0E3B2A; } .aw-artist a:hover { color: #B85600; text-decoration: underline; }
        .aw-price { margin: 16px 0 0; font-size: 28px; font-weight: 800; color: #0E3B2A; }
        .aw-price small { font-size: 13px; font-weight: 600; color: #557466; }
        .aw-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 16px 0 0; padding: 0; list-style: none; }
        .aw-facts li { padding: 10px 12px; border-radius: 14px; background: #fff; border: 1px solid #D8E9E0; }
        .aw-facts small { display: block; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #557466; }
        .aw-facts b { display: block; margin-top: 2px; font-size: 13.5px; color: #0E3B2A; }
        .aw-side #acheter { margin-top: 18px; border-radius: 22px; border-color: #D8E9E0; box-shadow: 0 18px 44px -28px rgba(14,59,42,.35); }

        /* Sections */
        .aw-sec { padding-top: 36px; margin-top: 36px; border-top: 1px solid #D8E9E0; }
        .aw-h2 { margin: 0 0 14px; font-family: 'Montserrat', serif; font-size: clamp(24px, 1.2vw + 16px, 30px); font-weight: 700; }
        .aw-kicker { margin: 0 0 4px; font-size: 12px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #EB6E00; }
        .aw-text { margin: 0; font-size: 16px; line-height: 1.75; color: #24503D; white-space: pre-line; }
        .aw-dl { margin: 0; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; overflow: hidden; }
        .aw-dl > div { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 12px; padding: 12px 16px; border-top: 1px solid #F3F8F5; font-size: 14px; }
        .aw-dl > div:first-child { border-top: 0; }
        .aw-dl dt { color: #557466; } .aw-dl dd { margin: 0; color: #0E3B2A; font-weight: 600; }
        .aw-artist-card { display: grid; grid-template-columns: 96px minmax(0, 1fr); gap: 18px; padding: 20px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; }
        .aw-artist-card__photo { width: 96px; height: 96px; border-radius: 999px; object-fit: cover; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 30px; }
        .aw-btn { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .aw-btn--dark { background: #0E3B2A; color: #fff; } .aw-btn--dark:hover { background: #000; }
        .aw-btn--line { border: 1.5px solid #D8E9E0; color: #0E3B2A; background: #fff; } .aw-btn--line:hover { border-color: #EB6E00; color: #B85600; }
        .aw-btn:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .aw-ship { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .aw-ship > div { display: flex; gap: 12px; padding: 14px 16px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; font-size: 14px; color: #24503D; }
        .aw-ship i { width: 36px; height: 36px; flex: none; border-radius: 12px; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; }
        .aw-ship b { display: block; color: #0E3B2A; }
        .aw-more { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .aw-card { display: block; border-radius: 18px; overflow: hidden; background: #fff; border: 1px solid #D8E9E0; transition: border-color .15s ease, box-shadow .15s ease; }
        .aw-card:hover { border-color: #D8E9E0; box-shadow: 0 14px 30px -20px rgba(14,59,42,.45); }
        .aw-card__img { aspect-ratio: 1; background: #D8E9E0; display: flex; align-items: center; justify-content: center; padding: 14px; }
        .aw-card__img img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .aw-card__body { display: block; padding: 10px 12px 12px; }
        .aw-card__body b { display: block; font-family: 'Montserrat', serif; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .aw-card__body small { font-size: 13px; color: #557466; }
        .aw-flash { margin: 0 0 16px; padding: 12px 16px; border-radius: 14px; font-size: 14px; }
        @media (max-width: 1023px) { .aw-side { position: static; } .aw-more { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 639px) {
            .aw-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .aw-dl > div { grid-template-columns: 110px minmax(0, 1fr); }
            .aw-ship { grid-template-columns: 1fr; }
            .aw-artist-card { grid-template-columns: 64px minmax(0, 1fr); padding: 16px; }
            .aw-artist-card__photo { width: 64px; height: 64px; font-size: 22px; }
        }
    </style>
    @include('partials.detail-theme')
    @include('partials.booking-page-theme')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $images = collect($artwork->images ?? [])->filter()->values();
    $profile = $artwork->provider ? \App\Models\ArtistProfile::forProvider($artwork->provider) : null;
    $dims = $artwork->width_cm ? (float) $artwork->width_cm.' × '.(float) $artwork->height_cm.($artwork->depth_cm ? ' × '.(float) $artwork->depth_cm : '').' cm' : $artwork->dimensions;
    $editions = $artwork->editions()->where('is_active', true)->get();
    $sold = $artwork->status === \App\Models\Artwork::STATUS_SOLD;
    $more = \App\Models\Artwork::where('provider_id', $artwork->provider_id)->whereKeyNot($artwork->id)
        ->whereIn('status', [\App\Models\Artwork::STATUS_PUBLISHED, \App\Models\Artwork::STATUS_SOLD])->latest()->take(4)->get();
    $delivery = $profile ? $profile->deliveryOptions($artwork) : [];
@endphp

<main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    <a href="{{ route('art.index') }}" class="hx-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Art & Créations') }}</a>

    <div class="mt-6">
        @if(session('success'))<p class="aw-flash bg-emerald-50 border border-emerald-200 text-emerald-800"><i class="fas fa-circle-check mr-1" aria-hidden="true"></i>{{ session('success') }}</p>@endif
        @if(session('error'))<p class="aw-flash bg-rose-50 border border-rose-200 text-rose-800"><i class="fas fa-circle-exclamation mr-1" aria-hidden="true"></i>{{ session('error') }}</p>@endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        {{-- Galerie --}}
        <div class="lg:col-span-7 min-w-0">
            <div class="aw-stage">
                @if($images->isNotEmpty())
                    <img id="artwork-main-image" src="{{ $images[0] }}" alt="{{ $artwork->title }}">
                    <button type="button" class="aw-stage__btn aw-stage__zoom" id="aw-zoom" aria-label="{{ __('Agrandir la photo') }}"><i class="fas fa-expand" aria-hidden="true"></i></button>
                    @if($images->count() > 1)<span class="aw-stage__btn aw-stage__count"><i class="fas fa-images" aria-hidden="true"></i><span id="aw-count">1</span> / {{ $images->count() }}</span>@endif
                @else
                    <i class="fas fa-palette text-5xl text-[#EB6E00]" aria-hidden="true"></i>
                @endif
            </div>
            @if($images->count() > 1)
                <div class="aw-thumbs" id="artwork-thumbs">
                    @foreach($images as $i => $img)
                        <button type="button" class="aw-thumb artwork-thumb {{ $i === 0 ? 'is-active' : '' }}" data-thumb-src="{{ $img }}" aria-label="{{ __('Photo :n', ['n' => $i + 1]) }}"><img src="{{ $img }}" alt="" loading="lazy"></button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Informations et achat --}}
        <div class="lg:col-span-5 min-w-0">
            <div class="aw-side">
                <div class="hx-pills mb-0">
                    @if($artwork->category)<span class="hx-pill"><i class="fas fa-palette" aria-hidden="true"></i>{{ $artwork->category->localized('name') }}</span>@endif
                    @if($sold)<span class="hx-pill" style="background:#0E3B2A;color:#fff">{{ __('Vendue') }}</span>@elseif($editions->isNotEmpty())<span class="hx-pill"><i class="fas fa-layer-group" aria-hidden="true"></i>{{ __('Éditions limitées') }}</span>@endif
                </div>
                <h1 class="aw-title">{{ $artwork->title }}</h1>
                @if($profile)
                    <p class="aw-artist">
                        @if($profile->photo || $artwork->provider->logo_url)<img src="{{ $profile->photo ?: $artwork->provider->logo_url }}" alt="">@else<span class="aw-artist__ph"><i class="fas fa-palette" aria-hidden="true"></i></span>@endif
                        <span>{{ __('Par') }} <a href="{{ route('artist.show', $profile->slug) }}">{{ $profile->display_name }}</a>@if($profile->city)<span class="text-[#557466]"> · {{ $profile->city }}</span>@endif</span>
                    </p>
                @endif
                <p class="aw-price">{{ $fmt((int) $artwork->price_xof) }}@if($editions->isNotEmpty()) <small>· {{ __('tirages dès :a', ['a' => $fmt((int) $editions->min('price_xof'))]) }}</small>@endif</p>

                <ul class="aw-facts">
                    @if($artwork->medium)<li><small>{{ __('Technique') }}</small><b>{{ $artwork->medium }}</b></li>@endif
                    @if($dims)<li><small>{{ __('Dimensions') }}</small><b>{{ $dims }}</b></li>@endif
                    @if($artwork->year_created)<li><small>{{ __('Année') }}</small><b>{{ $artwork->year_created }}</b></li>@endif
                </ul>
                @include('art-marketplace._wall', ['artwork' => $artwork])
                @include('art-marketplace._buy', ['artwork' => $artwork])
            </div>
        </div>
    </div>

    @if($artwork->description)
        <section class="aw-sec max-w-3xl">
            <p class="aw-kicker">{{ __('L\'œuvre') }}</p>
            <h2 class="aw-h2">{{ __('À propos de l\'œuvre') }}</h2>
            <p class="aw-text">{{ $artwork->description }}</p>
        </section>
    @endif

    <section class="aw-sec grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            <p class="aw-kicker">{{ __('Fiche technique') }}</p>
            <h2 class="aw-h2">{{ __('Détails') }}</h2>
            <dl class="aw-dl">
                <div><dt>{{ __('Titre') }}</dt><dd>{{ $artwork->title }}</dd></div>
                @if($profile)<div><dt>{{ __('Artiste') }}</dt><dd>{{ $profile->display_name }}</dd></div>@endif
                @if($artwork->category)<div><dt>{{ __('Catégorie') }}</dt><dd>{{ $artwork->category->localized('name') }}</dd></div>@endif
                @if($artwork->medium)<div><dt>{{ __('Technique') }}</dt><dd>{{ $artwork->medium }}</dd></div>@endif
                @if($dims)<div><dt>{{ __('Dimensions') }}</dt><dd>{{ $dims }}</dd></div>@endif
                @if($artwork->year_created)<div><dt>{{ __('Année') }}</dt><dd>{{ $artwork->year_created }}</dd></div>@endif
                <div><dt>{{ __('Exemplaire') }}</dt><dd>{{ $artwork->stock_quantity > 1 ? trans_choice(':count exemplaire|:count exemplaires', $artwork->stock_quantity) : __('Original, pièce unique') }}@if($editions->isNotEmpty()) · {{ trans_choice(':count édition limitée|:count éditions limitées', $editions->count()) }}@endif</dd></div>
                <div><dt>{{ __('Authenticité') }}</dt><dd>{{ __('Certificat numéroté, vérifiable en ligne') }}</dd></div>
            </dl>
        </div>
        <div>
            <p class="aw-kicker">{{ __('Achat serein') }}</p>
            <h2 class="aw-h2">{{ __('Livraison et retours') }}</h2>
            <div class="aw-ship">
                @foreach($delivery as $key => $opt)
                    <div><i class="fas {{ $key === 'pickup' ? 'fa-store' : 'fa-truck' }}" aria-hidden="true"></i><span><b>{{ \Illuminate\Support\Str::before($opt['label'], ' — ') }}</b>{{ $opt['fee'] ? $fmt($opt['fee']) : __('Gratuit') }}@if($key === 'pickup' && $profile?->pickup_address)<br><small class="text-[#557466]">{{ $profile->pickup_address }}</small>@endif</span></div>
                @endforeach
                <div><i class="fas fa-shield-halved" aria-hidden="true"></i><span><b>{{ __('Assurance transport') }}</b>{{ __(':p % de la valeur, en option', ['p' => rtrim(rtrim(number_format((float) ($profile?->insurance_percent ?? 2), 2, ',', ''), '0'), ',')]) }}</span></div>
                @if($profile?->returns_accepted)
                    <div><i class="fas fa-rotate-left" aria-hidden="true"></i><span><b>{{ __('Retour sous :n jours', ['n' => \App\Models\ArtworkOrder::RETURN_DAYS]) }}</b>{{ __('après réception, œuvre dans son état d\'origine') }}</span></div>
                @endif
                <div><i class="fas fa-lock" aria-hidden="true"></i><span><b>{{ __('Paiement protégé') }}</b>{{ __('L\'artiste est payé après la réception') }}</span></div>
            </div>
        </div>
    </section>

    @if($profile)
        <section class="aw-sec">
            <p class="aw-kicker">{{ __('L\'artiste') }}</p>
            <h2 class="aw-h2">{{ $profile->display_name }}</h2>
            <div class="aw-artist-card">
                @if($profile->photo)<img src="{{ $profile->photo }}" alt="{{ $profile->display_name }}" class="aw-artist-card__photo">@else<span class="aw-artist-card__photo"><i class="fas fa-palette" aria-hidden="true"></i></span>@endif
                <div class="min-w-0">
                    @if($profile->tagline)<p class="font-serif text-lg font-bold m-0">{{ $profile->tagline }}</p>@endif
                    @if($profile->bio)<p class="aw-text text-[15px] mt-2">{{ \Illuminate\Support\Str::limit($profile->bio, 360) }}</p>@endif
                    <div class="flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('artist.show', $profile->slug) }}" class="aw-btn aw-btn--dark">{{ __('Voir la page de l\'artiste') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        @if($profile->accepts_commissions)<a href="{{ route('artist.commission', $profile->slug) }}" class="aw-btn aw-btn--line"><i class="fas fa-paintbrush" aria-hidden="true"></i>{{ __('Commande sur mesure') }}</a>@endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($more->isNotEmpty())
        <section class="aw-sec">
            <p class="aw-kicker">{{ $profile?->display_name }}</p>
            <h2 class="aw-h2">{{ __('Autres œuvres de l\'artiste') }}</h2>
            <div class="aw-more">
                @foreach($more as $w)
                    <a href="{{ route('art.show', $w->slug) }}" class="aw-card">
                        <span class="aw-card__img">@if($w->images[0] ?? null)<img src="{{ $w->images[0] }}" alt="{{ $w->title }}" loading="lazy">@else<i class="fas fa-palette text-2xl text-[#EB6E00]" aria-hidden="true"></i>@endif</span>
                        <span class="aw-card__body"><b>{{ $w->title }}</b><small>{{ $w->status === \App\Models\Artwork::STATUS_SOLD ? __('Vendue') : $fmt((int) $w->price_xof) }}</small></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

<div class="tp-lb" id="tp-lb" role="dialog" aria-modal="true" aria-label="{{ __('Photos de l\'œuvre') }}">
    <button type="button" data-lb-close style="top:16px;right:16px" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
    <button type="button" data-lb-step="-1" style="left:16px;top:50%" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left"></i></button>
    <img src="" alt="{{ $artwork->title }}">
    <button type="button" data-lb-step="1" style="right:16px;top:50%" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right"></i></button>
</div>

@include('partials.homepage-footer')
<script>
(function () {
    const photos = @json($images);
    const main = document.getElementById('artwork-main-image');
    if (!main || !photos.length) return;
    const thumbs = Array.from(document.querySelectorAll('.artwork-thumb'));
    const count = document.getElementById('aw-count');
    let at = 0;
    function show(i) {
        at = (i + photos.length) % photos.length;
        main.src = photos[at];
        if (count) count.textContent = at + 1;
        thumbs.forEach((t, k) => t.classList.toggle('is-active', k === at));
    }
    thumbs.forEach((t, k) => t.addEventListener('click', () => show(k)));
    const lb = document.getElementById('tp-lb'), lbImg = lb.querySelector('img');
    const open = () => { lbImg.src = photos[at]; lb.classList.add('is-open'); lb.querySelector('[data-lb-close]').focus(); };
    main.addEventListener('click', open);
    document.getElementById('aw-zoom')?.addEventListener('click', open);
    lb.querySelector('[data-lb-close]').addEventListener('click', () => lb.classList.remove('is-open'));
    lb.querySelectorAll('[data-lb-step]').forEach(b => b.addEventListener('click', () => { show(at + (+b.dataset.lbStep)); lbImg.src = photos[at]; }));
    lb.addEventListener('click', e => { if (e.target === lb) lb.classList.remove('is-open'); });
    document.addEventListener('keydown', e => {
        if (!lb.classList.contains('is-open')) return;
        if (e.key === 'Escape') lb.classList.remove('is-open');
        if (e.key === 'ArrowLeft') { show(at - 1); lbImg.src = photos[at]; }
        if (e.key === 'ArrowRight') { show(at + 1); lbImg.src = photos[at]; }
    });
})();
</script>
</body>
</html>
