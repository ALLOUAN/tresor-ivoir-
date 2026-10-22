<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->meta_title_fr ?: $article->localized('title') }} — {{ $siteBrand['site_name'] }}</title>
    @php
        // Aperçu du lien partagé (Facebook, WhatsApp, LinkedIn, X, Telegram…) : la couverture de l'article.
        $shareCover = $article->cover_url;
        $shareImage = null;
        if ($shareCover && str_starts_with($shareCover, '/storage/')) {
            $shareRel = ltrim(substr(parse_url($shareCover, PHP_URL_PATH) ?: '', strlen('/storage/')), '/');
            $shareAbs = \Illuminate\Support\Facades\Storage::disk('public')->path($shareRel);
            $shareImage = url(route('img.og', ['path' => $shareRel.'.jpg'], false)).(is_file($shareAbs) ? '?v='.filemtime($shareAbs) : '');
        } elseif ($shareCover && preg_match('#^https?://#i', $shareCover)) {
            $shareImage = $shareCover;
        } elseif (!empty($siteBrand['logo_url'])) {
            $shareImage = url($siteBrand['logo_url']);
        }
        $shareTitle = $article->meta_title_fr ?: $article->title_fr;
        $shareDesc = \Illuminate\Support\Str::limit(trim(strip_tags($article->meta_desc_fr ?: ($article->excerpt_fr ?: $article->content_fr))), 200);
        $shareUrl = route('articles.show', $article->slug_fr);
    @endphp
    <link rel="canonical" href="{{ $shareUrl }}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="{{ $siteBrand['site_name'] }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:title" content="{{ $shareTitle }}">
    <meta property="og:description" content="{{ $shareDesc }}">
    @if($shareImage)
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:secure_url" content="{{ $shareImage }}">
    @if(str_contains($shareImage, '/img/og/'))
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @endif
    <meta property="og:image:alt" content="{{ $article->cover_alt ?: $article->localized('title') }}">
    @endif
    @if($article->published_at)
    <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
    @endif
    <meta name="twitter:card" content="{{ $shareImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $shareTitle }}">
    <meta name="twitter:description" content="{{ $shareDesc }}">
    @if($shareImage)
    <meta name="twitter:image" content="{{ $shareImage }}">
    @endif
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    @if($article->meta_desc_fr)
    <meta name="description" content="{{ $article->meta_desc_fr }}">
    @endif
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .prose-content { font-family: 'Lora', serif; font-size: 1.0625rem; line-height: 1.85; color: #44413a; }
        .prose-content p { margin-bottom: 1.5rem; }
        .prose-content h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1c1915; margin: 2.5rem 0 1rem; }
        .prose-content h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 600; color: #2d2a23; margin: 2rem 0 0.75rem; }
        .prose-content blockquote { border-left: 3px solid #f2790f; padding-left: 1.25rem; margin: 2rem 0; color: #544f47; font-style: italic; }
        .prose-content ul { list-style: none; padding: 0; margin-bottom: 1.5rem; }
        .prose-content ul li::before { content: '—'; color: #f2790f; margin-right: .6rem; }
        .prose-content a { color: #a54a0b; text-decoration: underline; }
        .prose-content strong { color: #1c1915; font-weight: 600; }
    </style>
    @include('partials.prose-clean')
    @include('partials.article-header')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="min-h-screen" style="background:#f6f3ed">
    @include('partials.page-background')
@include('partials.public-top-nav')
@php
    // Le compte « Super Admin » n'est jamais affiché publiquement (auteur, carte auteur, contributeurs).
    $isSuperAdmin = fn ($name) => strcasecmp(trim((string) $name), 'Super Admin') === 0;
    $authorHidden = $isSuperAdmin($article->author->full_name ?? '');
    $contributors = $article->display_uploaders->reject(fn ($c) => $isSuperAdmin($c->full_name));
    $galleryImages = $article->media
        ->where('type', 'image')
        ->filter(fn ($m) => ! empty($m->url) && $m->url !== $article->cover_url)
        ->values();
    // Photos téléchargeables (couverture + galerie, fichiers présents sur le site).
    $downloadablePhotos = collect(\App\Http\Controllers\ArticlePhotoController::photos($article));
    $downloadableIds = $downloadablePhotos->pluck('media_id')->filter()->all();
    $authorLabel = $authorHidden ? __('Rédaction') : ($article->author->full_name ?? __('Rédaction'));
    $authorInitial = $authorHidden ? 'T' : strtoupper(substr($article->author->first_name ?? 'R', 0, 1));
@endphp
<style>
    /* Article : lecture confortable (colonne de 720 px), sommaire et partage dans une colonne collante,
       barre de progression. Charte du site, aplats, ombre au survol seulement. */
    .ar-progress { position: fixed; left: 0; top: 0; z-index: 60; height: 3px; width: 0; background: #b9500a; transition: width .1s linear; }
    .ar { max-width: 1180px; margin: 0 auto; padding: 22px 20px 56px; color: #1d1a16; }
    .ar-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; margin: 0; padding: 0; list-style: none; font-size: 13px; color: #6b6458; }
    .ar-crumb a { color: #9a4308; font-weight: 600; text-decoration: none; }
    .ar-crumb a:hover { text-decoration: underline; text-underline-offset: 3px; }
    .ar-crumb li:first-child a { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; font-weight: 700; }
    .ar-crumb .sep { color: #c2b89e; }
    .ar-head { max-width: 860px; margin: 26px auto 0; text-align: center; }
    .ar-badges { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
    .ar-badge { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: #fdf4ec; color: #9a4308; font-size: 11.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; text-decoration: none; }
    .ar-badge.is-green { background: #e7f3ec; color: #1b6b3a; }
    .ar-badge.is-ghost { background: #fff; border: 1px solid #e6dccb; color: #6b6458; }
    .ar-title { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(32px, 2.8vw + 18px, 58px); font-weight: 700; line-height: 1.08; letter-spacing: -.015em; color: #1d1a16; }
    .ar-lead { margin: 16px auto 0; max-width: 720px; font-family: 'Playfair Display', serif; font-style: italic; font-size: 20px; line-height: 1.55; color: #4a443a; }
    .ar-meta { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 10px 22px; margin: 22px 0 0; font-size: 14px; color: #4a443a; }
    .ar-meta > span { display: inline-flex; align-items: center; gap: 8px; }
    .ar-avatar { width: 34px; height: 34px; border-radius: 999px; background: #1b6b3a; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-weight: 700; }
    .ar-meta .ico { color: #b9500a; font-size: 17px; }
    .ar-contrib { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 12px; }
    .ar-contrib span { height: 26px; padding: 0 11px; border-radius: 999px; background: #fff; border: 1px solid #ece5d6; font-size: 12px; font-weight: 600; color: #3a2f22; display: inline-flex; align-items: center; }
    .ar-cover { margin: 30px 0 0; border-radius: 28px; overflow: hidden; background: #e9e1d2; }
    .ar-cover img { display: block; width: 100%; height: clamp(260px, 50vw, 620px); object-fit: cover; }
    .ar-cover figcaption { padding: 10px 16px; background: #fff; font-size: 13px; font-style: italic; color: #6b6458; text-align: center; }

    .ar-grid { display: grid; grid-template-columns: 220px minmax(0, 720px) 1fr; gap: 40px; margin-top: 40px; }
    .ar-side { position: sticky; top: 140px; align-self: start; display: flex; flex-direction: column; gap: 18px; }
    .ar-side h3 { margin: 0 0 10px; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #8a7f6b; }
    .ar-toc { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 2px; border-left: 2px solid #ece5d6; }
    .ar-toc a { display: block; padding: 6px 12px; margin-left: -2px; border-left: 2px solid transparent; font-size: 13.5px; line-height: 1.4; color: #4a443a; text-decoration: none; }
    .ar-toc a.is-sub { padding-left: 24px; font-size: 13px; }
    .ar-toc a:hover { color: #9a4308; }
    .ar-toc a.is-on { border-left-color: #b9500a; color: #1d1a16; font-weight: 700; }
    .ar-share { display: flex; flex-wrap: wrap; gap: 8px; }
    .ar-share a, .ar-share button, .ar-fav { width: 42px; height: 42px; border-radius: 999px; border: 1px solid #e6dccb; background: #fff; color: #3a2f22; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer; text-decoration: none; transition: border-color .15s ease, color .15s ease; }
    .ar-share a:hover, .ar-share button:hover { border-color: #b9500a; color: #9a4308; }
    .ar-fav { color: #b9500a; }
    .ar-fav.is-on { background: #e7f3ec; border-color: #c4e2cf; color: #1b6b3a; }
    .ar-body { min-width: 0; }
    .ar-body .prose-content { font-size: 1.1rem; }
    .ar-body .prose-content h2, .ar-body .prose-content h3 { scroll-margin-top: 150px; }

    .ar-block { margin-top: 40px; padding-top: 32px; border-top: 1px solid #e6dccb; }
    .ar-block h2 { margin: 0 0 16px; font-family: 'Playfair Display', serif; font-size: 24px; color: #1d1a16; }
    .ar-block__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .ar-block__head h2 { margin: 0; }
    .ar-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 18px; border-radius: 999px; border: 0; background: #b9500a; color: #fff; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; }
    .ar-btn:hover { background: #9a4308; color: #fff; }
    .ar-gallery { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .ar-gallery figure { position: relative; margin: 0; border-radius: 16px; overflow: hidden; background: #e9e1d2; aspect-ratio: 4 / 3; }
    .ar-gallery img { width: 100%; height: 100%; object-fit: cover; }
    .ar-gallery a { position: absolute; right: 8px; bottom: 8px; width: 36px; height: 36px; border-radius: 999px; background: #fff; color: #1d1a16; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .ar-gallery a:hover { background: #b9500a; color: #fff; }
    .ar-tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .ar-tags a { height: 34px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #ece5d6; color: #3a2f22; font-size: 13.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; }
    .ar-tags a:hover { border-color: #b9500a; color: #9a4308; }
    .ar-author { display: flex; gap: 16px; padding: 20px; border-radius: 24px; background: #fff; border: 1px solid #ece5d6; }
    .ar-author .ar-avatar { width: 56px; height: 56px; font-size: 22px; flex: none; }
    .ar-author b { display: block; font-family: 'Playfair Display', serif; font-size: 19px; }
    .ar-author small { display: block; margin-top: 2px; font-size: 12.5px; color: #8a7f6b; }
    .ar-author p { margin: 8px 0 0; font-size: 14.5px; line-height: 1.6; color: #4a443a; }

    .ar-comments { display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px; }
    .ar-comment { display: flex; gap: 12px; padding: 16px; border-radius: 20px; background: #fff; border: 1px solid #ece5d6; }
    .ar-comment .ar-avatar { width: 40px; height: 40px; flex: none; background: #fdf4ec; color: #9a4308; }
    .ar-comment b { font-size: 14.5px; }
    .ar-comment small { margin-left: 8px; font-size: 12.5px; color: #8a7f6b; }
    .ar-comment p { margin: 6px 0 0; font-size: 14.5px; line-height: 1.6; color: #3a2f22; }
    .ar-form { padding: 22px; border-radius: 24px; background: #fff; border: 1px solid #ece5d6; }
    .ar-form h3 { margin: 0 0 14px; font-family: 'Playfair Display', serif; font-size: 20px; }
    .ar-form label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #1d1a16; }
    .ar-input { width: 100%; padding: 12px 14px; border-radius: 14px; border: 1.5px solid #e6dccb; background: #fbf9f5; color: #1d1a16; font: inherit; font-size: 15px; outline: none; }
    .ar-input:focus { border-color: #b9500a; background: #fff; }
    .ar-note { margin: 6px 0 0; font-size: 12.5px; color: #8a7f6b; }
    .ar-flash { display: flex; gap: 10px; padding: 12px 16px; margin-bottom: 18px; border-radius: 16px; font-size: 14px; }
    .ar-flash.is-ok { background: #e7f3ec; color: #1b6b3a; }
    .ar-flash.is-err { background: #fdecea; color: #b42318; }

    .ar-related { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .ar-rel { display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
    .ar-rel:hover { border-color: #d8cdb8; box-shadow: 0 18px 32px -26px rgba(29,26,22,.5); }
    .ar-rel__img { aspect-ratio: 16 / 10; background: #f3eee4; overflow: hidden; }
    .ar-rel__img img { width: 100%; height: 100%; object-fit: cover; }
    .ar-rel__body { padding: 14px 16px 16px; }
    .ar-rel__body small { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #b9500a; }
    .ar-rel__body b { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 4px; font-family: 'Playfair Display', serif; font-size: 17px; line-height: 1.25; color: #1d1a16; }
    .ar-rel__body span { display: block; margin-top: 6px; font-size: 12.5px; color: #6b6458; }
    .ar a:focus-visible, .ar button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

    @media (max-width: 1100px) {
        .ar-grid { grid-template-columns: minmax(0, 720px); justify-content: center; }
        .ar-side { position: static; order: -1; flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: space-between; }
        .ar-side .ar-toc-wrap { display: none; }
    }
    @media (max-width: 640px) {
        .ar { padding: 16px 16px 40px; }
        .ar-lead { font-size: 17px; }
        .ar-meta { gap: 8px 14px; font-size: 13px; }
        .ar-cover { border-radius: 20px; margin-left: -6px; margin-right: -6px; }
        .ar-gallery, .ar-related { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ar-related { grid-template-columns: minmax(0, 1fr); }
    }
    @media (prefers-reduced-motion: reduce) { .ar-progress { transition: none; } }
</style>

<div class="ar-progress" aria-hidden="true" data-ar-progress></div>

<article class="ar">
    <ol class="ar-crumb" aria-label="{{ __('Fil d\'Ariane') }}">
        <li><a href="{{ route('articles.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Magazine') }}</a></li>
        @if($article->category)
            <li class="sep" aria-hidden="true">/</li>
            <li><a href="{{ route('articles.index', ['categorie' => $article->category->slug]) }}">{{ $article->category->localized('name') }}</a></li>
        @endif
    </ol>

    <header class="ar-head">
        <div class="ar-badges">
            @if($article->category)<a href="{{ route('articles.index', ['categorie' => $article->category->slug]) }}" class="ar-badge">{{ $article->category->localized('name') }}</a>@endif
            @if($article->is_featured)<span class="ar-badge is-green"><x-icon name="star" />{{ __('À la une') }}</span>@endif
            @if($article->is_destination)<span class="ar-badge is-green">{{ __('Destination') }}</span>@endif
            @if($article->is_sponsored)
                @if($article->sponsor)
                    <a href="{{ route('providers.show', $article->sponsor->slug) }}" class="ar-badge is-ghost">{{ __('Sponsorisé par') }} {{ $article->sponsor->name }}</a>
                @else
                    <span class="ar-badge is-ghost">{{ __('Sponsorisé') }}</span>
                @endif
            @endif
        </div>
        <h1 class="ar-title">{{ $article->localized('title') }}</h1>
        @if($article->excerpt_fr)<p class="ar-lead">{{ $article->localized('excerpt') }}</p>@endif
        <div class="ar-meta">
            <span><span class="ar-avatar" aria-hidden="true">{{ $authorInitial }}</span>{{ $authorLabel }}</span>
            @if($article->published_at)<span><x-icon name="calendar" />{{ $article->published_at->locale(app()->getLocale())->translatedFormat('d F Y') }}</span>@endif
            @if($article->reading_time)<span><x-icon name="clock-circle" />{{ $article->reading_time }} {{ __('min de lecture') }}</span>@endif
            <span><x-icon name="eye" />{{ number_format($article->views_count, 0, ',', ' ') }} {{ __('vues') }}</span>
        </div>
        @if($contributors->isNotEmpty())
            <div class="ar-contrib" aria-label="{{ __('Contributeurs') }}">@foreach($contributors as $contributor)<span>{{ $contributor->full_name }}</span>@endforeach</div>
        @endif
    </header>

    @if($article->cover_url)
        <figure class="ar-cover">
            <img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt ?: $article->localized('title') }}" decoding="async" fetchpriority="high">
            @if($article->cover_alt)<figcaption>{{ $article->cover_alt }}</figcaption>@endif
        </figure>
    @endif

    <div class="ar-grid">
        <aside class="ar-side" aria-label="{{ __('Outils de lecture') }}">
            <div class="ar-toc-wrap" data-ar-toc-wrap hidden>
                <h3>{{ __('Sommaire') }}</h3>
                <ol class="ar-toc" data-ar-toc></ol>
            </div>
            <div>
                <h3>{{ __('Partager') }}</h3>
                <div class="ar-share">
                    @auth
                        @if(auth()->user()->role === 'visitor')
                            @if(! ($isFavorited ?? false))
                                <form method="POST" action="{{ route('visitor.favorites.store') }}">
                                    @csrf
                                    <input type="hidden" name="type" value="article">
                                    <input type="hidden" name="id" value="{{ $article->id }}">
                                    <button type="submit" class="ar-fav" title="{{ __('Ajouter aux favoris') }}" aria-label="{{ __('Ajouter aux favoris') }}"><i class="fas fa-heart"></i></button>
                                </form>
                            @else
                                <span class="ar-fav is-on" title="{{ __('Déjà favori') }}"><i class="fas fa-heart-circle-check"></i></span>
                            @endif
                        @endif
                    @endauth
                    <a href="https://wa.me/?text={{ urlencode($shareTitle.' — '.$shareUrl) }}" target="_blank" rel="noopener" aria-label="WhatsApp" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}" target="_blank" rel="noopener" aria-label="X" title="X"><i class="fab fa-x-twitter"></i></a>
                    <button type="button" data-ar-copy data-done="{{ __('Lien copié') }}" aria-label="{{ __('Copier le lien') }}" title="{{ __('Copier le lien') }}"><i class="fas fa-link"></i></button>
                </div>
            </div>
        </aside>

        <div class="ar-body">
            @if($article->content_fr)
                <div class="prose-content max-w-none" data-ar-content>
                    {!! \App\Support\HtmlSanitizer::articleBody($article->localized('content')) !!}
                </div>
            @endif

            {{-- Galerie --}}
            @if($galleryImages->isNotEmpty() || $downloadablePhotos->isNotEmpty())
                <section class="ar-block">
                    <div class="ar-block__head">
                        <h2>{{ $galleryImages->isNotEmpty() ? __('Galerie photos') : __('Photo de l\'article') }}</h2>
                        @if($downloadablePhotos->count() > 1)
                            <a href="{{ route('articles.photos.zip', $article->slug_fr) }}" download class="ar-btn"><i class="fas fa-file-zipper"></i>{{ trans_choice('Télécharger la photo|Télécharger les :count photos', $downloadablePhotos->count(), ['count' => $downloadablePhotos->count()]) }}</a>
                        @elseif($downloadablePhotos->count() === 1)
                            <a href="{{ $downloadablePhotos->first()['media_id'] ? route('articles.photos.download', [$article->slug_fr, $downloadablePhotos->first()['media_id']]) : route('articles.photos.cover', $article->slug_fr) }}" download class="ar-btn"><i class="fas fa-download"></i>{{ __('Télécharger la photo') }}</a>
                        @endif
                    </div>
                    @if($galleryImages->isNotEmpty())
                        <div class="ar-gallery">
                            @foreach($galleryImages as $image)
                                <figure>
                                    <img src="{{ $image->url }}" alt="{{ $image->alt_text ?: $article->localized('title') }}" loading="lazy">
                                    @if(in_array($image->id, $downloadableIds, true))
                                        <a href="{{ route('articles.photos.download', [$article->slug_fr, $image->id]) }}" download title="{{ __('Télécharger cette photo') }}" aria-label="{{ __('Télécharger cette photo') }}"><i class="fas fa-download text-sm"></i></a>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            {{-- Tags --}}
            @if($article->tags->isNotEmpty())
                <section class="ar-block">
                    <h2>{{ __('Thèmes') }}</h2>
                    <div class="ar-tags">@foreach($article->tags as $tag)<a href="{{ route('articles.index', ['tag' => $tag->slug]) }}">#{{ $tag->localized('name') }}</a>@endforeach</div>
                </section>
            @endif

            {{-- Auteur --}}
            @unless($authorHidden)
                <section class="ar-block">
                    <div class="ar-author">
                        <span class="ar-avatar" aria-hidden="true">{{ $authorInitial }}</span>
                        <div>
                            <b>{{ $article->author->full_name ?? __('Rédaction ').$siteBrand['site_name'] }}</b>
                            <small>{{ __('Contributeur') }} · {{ $siteBrand['site_name'] }}</small>
                            <p>{{ __('Passionné de culture et de tourisme ivoirien, notre équipe éditoriale explore et documente les richesses de la Côte d\'Ivoire.') }}</p>
                        </div>
                    </div>
                </section>
            @endunless

            {{-- Commentaires --}}
            <section class="ar-block" id="commentaires">
                <h2>{{ __('Commentaires') }} @if($comments->isNotEmpty())<span style="font-family:Inter,sans-serif;font-size:15px;font-weight:600;color:#8a7f6b">({{ $comments->count() }})</span>@endif</h2>
                @if(session('comment_sent'))<p class="ar-flash is-ok"><i class="fas fa-circle-check mt-0.5"></i>{{ session('comment_sent') }}</p>@endif
                @if($comments->isNotEmpty())
                    <div class="ar-comments">
                        @foreach($comments as $comment)
                            <div class="ar-comment">
                                <span class="ar-avatar" aria-hidden="true">{{ strtoupper(substr($comment->display_name, 0, 1)) }}</span>
                                <div class="min-w-0"><b>{{ $comment->display_name }}</b><small>{{ $comment->created_at->diffForHumans() }}</small><p>{{ $comment->content }}</p></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="ar-note" style="margin-bottom:18px">{{ __('Aucun commentaire pour l\'instant. Soyez le premier !') }}</p>
                @endif

                <div class="ar-form">
                    <h3>{{ __('Laisser un commentaire') }}</h3>
                    @if($errors->has('content') || $errors->has('author_name') || $errors->has('author_email'))
                        <div class="ar-flash is-err" role="alert"><div>@foreach($errors->all() as $error)<p class="m-0">{{ $error }}</p>@endforeach</div></div>
                    @endif
                    <form method="POST" action="{{ route('articles.comments.store', $article) }}" class="space-y-4">
                        @csrf
                        @guest
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label for="ar-name">{{ __('Nom') }} <span style="color:#b9500a">*</span></label><input id="ar-name" type="text" name="author_name" value="{{ old('author_name') }}" required class="ar-input" placeholder="{{ __('Votre nom') }}"></div>
                                <div><label for="ar-mail">{{ __('E-mail') }} <span style="color:#b9500a">*</span></label><input id="ar-mail" type="email" name="author_email" value="{{ old('author_email') }}" required class="ar-input" placeholder="{{ __('Non publié') }}"></div>
                            </div>
                        @else
                            <p class="ar-note">{{ __('Connecté en tant que') }} <b style="color:#9a4308">{{ auth()->user()->full_name }}</b></p>
                        @endguest
                        <div>
                            <label for="ar-content">{{ __('Commentaire') }} <span style="color:#b9500a">*</span></label>
                            <textarea id="ar-content" name="content" rows="4" required minlength="10" maxlength="2000" class="ar-input" style="resize:vertical" placeholder="{{ __('Partagez votre avis sur cet article…') }}">{{ old('content') }}</textarea>
                            <p class="ar-note">{{ __('Votre commentaire sera publié après modération.') }}</p>
                        </div>
                        <button type="submit" class="ar-btn"><x-icon name="plane" />{{ __('Envoyer le commentaire') }}</button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    {{-- Dans la même rubrique --}}
    @if($related->isNotEmpty())
        <section class="ar-block" style="margin-top:56px">
            <div class="ar-block__head"><h2>{{ __('Dans la même rubrique') }}</h2><a href="{{ route('articles.index') }}" class="ar-badge is-ghost" style="height:38px">{{ __('Tous les articles') }} →</a></div>
            <div class="ar-related">
                @foreach($related as $rel)
                    <a href="{{ route('articles.show', $rel->slug_fr) }}" class="ar-rel">
                        <span class="ar-rel__img">@if($rel->cover_url)<img src="{{ $rel->cover_url }}" alt="{{ $rel->cover_alt }}" loading="lazy">@endif</span>
                        <span class="ar-rel__body"><small>{{ $rel->category?->localized('name') ?? '—' }}</small><b>{{ $rel->localized('title') }}</b><span>{{ $rel->published_at?->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</span></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</article>

@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
(function () {
    // Barre de progression de lecture
    const bar = document.querySelector('[data-ar-progress]'), content = document.querySelector('[data-ar-content]');
    if (bar && content) {
        const upd = () => {
            const r = content.getBoundingClientRect(), total = r.height - window.innerHeight * .6;
            bar.style.width = Math.max(0, Math.min(100, (-r.top / Math.max(1, total)) * 100)) + '%';
        };
        window.addEventListener('scroll', upd, { passive: true }); upd();
    }
    // Sommaire automatique à partir des intertitres
    const toc = document.querySelector('[data-ar-toc]'), wrap = document.querySelector('[data-ar-toc-wrap]');
    if (toc && content) {
        const heads = [...content.querySelectorAll('h2, h3')].filter(h => h.textContent.trim());
        if (heads.length >= 2) {
            heads.forEach((h, i) => {
                if (!h.id) h.id = 'section-' + (i + 1);
                const li = document.createElement('li'), a = document.createElement('a');
                a.href = '#' + h.id; a.textContent = h.textContent.trim();
                if (h.tagName === 'H3') a.classList.add('is-sub');
                li.appendChild(a); toc.appendChild(li);
            });
            wrap.hidden = false;
            const links = [...toc.querySelectorAll('a')];
            const io = new IntersectionObserver(es => es.forEach(e => {
                if (e.isIntersecting) links.forEach(l => l.classList.toggle('is-on', l.hash === '#' + e.target.id));
            }), { rootMargin: '-140px 0px -65% 0px' });
            heads.forEach(h => io.observe(h));
        }
    }
    // Copier le lien
    document.querySelector('[data-ar-copy]')?.addEventListener('click', async e => {
        const b = e.currentTarget;
        try { await navigator.clipboard.writeText(location.href.split('#')[0]); b.title = b.dataset.done; b.style.borderColor = '#1b6b3a'; b.style.color = '#1b6b3a'; } catch (err) {}
    });
})();
</script>
</body>
</html>
