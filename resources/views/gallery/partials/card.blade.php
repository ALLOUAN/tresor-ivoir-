@php
    $alt = trim((string) ($img->alt_text ?? ''));
    if ($alt === '') {
        $alt = trim((string) ($img->title ?? $img->original_name ?? __('Photo')));
    }
    $title = trim((string) ($img->title ?? ''));
    $showUrl = filled($img->uuid) ? route('gallery.public.show', $img->uuid) : url($img->url);
@endphp
{{-- Carte de la mosaïque : la photo garde ses proportions (hauteur calculée en JS), titre et actions au survol. --}}
<figure class="gm-card pin-card" data-gm-card>
    <a href="{{ $showUrl }}" @if(!filled($img->uuid)) target="_blank" rel="noopener noreferrer" @endif class="gm-card__link" aria-label="{{ $title !== '' ? $title : $alt }}">
        <img src="{{ url($img->url) }}" alt="{{ $alt }}" loading="lazy" decoding="async">
    </a>
    @if($img->is_featured)<span class="gm-card__badge"><x-icon name="star" />{{ __('Coup de cœur') }}</span>@endif
    <figcaption class="gm-card__cap">
        @if($title !== '')<span class="gm-card__title">{{ $title }}</span>@endif
        <span class="gm-card__actions">
            @include('gallery.partials.like-button', ['img' => $img, 'isLiked' => $isLiked ?? false, 'class' => 'gm-act'])
            @include('gallery.partials.download-button', ['img' => $img, 'class' => 'gm-act'])
        </span>
    </figcaption>
</figure>
