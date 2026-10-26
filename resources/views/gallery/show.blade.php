@php
    use Illuminate\Support\Str;
    $metaDesc = Str::limit(strip_tags(trim((string) ($media->caption ?? $media->title ?? $media->original_name ?? ''))), 165);
    if ($metaDesc === '') {
        $metaDesc = 'Visuel — Galerie '.$siteBrand['site_name'];
    }
    $imgAlt = trim((string) ($media->alt_text ?? ''));
    if ($imgAlt === '') {
        $imgAlt = trim((string) ($media->title ?? $media->original_name ?? __('Image')));
    }
    $title = trim((string) ($media->title ?? ''));
    $caption = trim((string) ($media->caption ?? ''));
    $credit = trim((string) ($media->credit ?? ''));
    $uploaderName = $media->uploader
        ? trim(($media->uploader->first_name ?? '').' '.($media->uploader->last_name ?? ''))
        : null;
    $authorLabel = $credit !== '' ? $credit : $uploaderName;
    $authorInitial = $authorLabel ? mb_strtoupper(mb_substr($authorLabel, 0, 1)) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.theme-init')
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ $pageTitle }} — {{ __('Galerie') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDesc }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <style>
        * { box-sizing: border-box; }
        body { background: #D8E9E0; }
        .pin-card { break-inside: avoid; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background:#F3F8F5; }
        ::-webkit-scrollbar-thumb { background: #EB6E00; border-radius: 3px; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="bg-[#D8E9E0] text-[#0E3B2A] antialiased font-sans">
    @include('partials.page-background')

@include('partials.public-top-nav')

<section class="pt-20 sm:pt-24 pb-16 sm:pb-24">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,480px)_1fr] gap-8 xl:gap-12 items-start">

            {{-- ══ Colonne gauche : la « pin » ═══════════════════════════ --}}
            <div class="lg:sticky lg:top-24">
                <div class="relative rounded-[1.75rem] overflow-hidden bg-gray-100 shadow-[0_2px_24px_rgba(0,0,0,0.12)]">
                    <a href="{{ route('gallery.public') }}"
                       class="absolute top-4 left-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white text-[#0E3B2A] shadow-md hover:bg-gray-100 transition"
                       aria-label="{{ __('Retour à la galerie') }}" title="{{ __('Retour à la galerie') }}">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <a href="{{ url($media->url) }}" download
                       class="gallery-download-btn absolute top-4 right-4 z-10 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-red-600 text-white text-sm font-bold shadow-md hover:bg-red-700 transition"
                       data-media-uuid="{{ $media->uuid }}">
                        <i class="fas fa-download text-xs"></i>
                        {{ __('Télécharger (') }}<span class="download-count">{{ $media->downloads_count ?? 0 }}</span>)
                    </a>
                    <img src="{{ url($media->url) }}" alt="{{ $imgAlt }}"
                         class="w-full h-auto block">
                </div>

                <div class="mt-4 px-1">
                    @include('gallery.partials.like-button', [
                        'img' => $media,
                        'isLiked' => ($likedMediaIds[$media->id] ?? false),
                        'class' => 'inline-flex items-center gap-2 h-10 px-4 rounded-full border border-gray-200 text-[#0E3B2A] text-sm font-semibold hover:bg-gray-50 transition',
                    ])
                </div>

                {{-- Bandeau auteur / infos, à la Pinterest --}}
                @if($title !== '' || $authorLabel)
                    <div class="mt-4 px-1">
                        @if($title !== '')
                            <h1 class="font-serif text-xl sm:text-2xl font-semibold leading-snug text-[#0E3B2A]">{{ $title }}</h1>
                        @endif
                        @if($caption !== '')
                            <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">{{ $caption }}</p>
                        @endif
                        @if($authorLabel)
                            <div class="mt-4 flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-500 text-white text-sm font-bold">{{ $authorInitial }}</span>
                                <span class="text-sm font-semibold text-[#0E3B2A]">{{ $authorLabel }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ══ Colonne droite : « Idées susceptibles de vous plaire » ══ --}}
            @if($relatedImages->isNotEmpty())
                <div>
                    <h2 class="font-plus text-lg sm:text-xl font-bold text-[#0E3B2A] mb-4">{{ __('Idées susceptibles de vous plaire') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-3.5">
                        @foreach($relatedImages as $img)
                            @include('gallery.partials.related-card', ['img' => $img, 'isLiked' => ($likedMediaIds[$img->id] ?? false)])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

@include('partials.gallery-like-script')
@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
