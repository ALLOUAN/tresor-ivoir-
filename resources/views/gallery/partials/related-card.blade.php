@php
    $alt = trim((string) ($img->alt_text ?? ''));
    if ($alt === '') {
        $alt = trim((string) ($img->title ?? $img->original_name ?? 'Photo'));
    }
    $title = trim((string) ($img->title ?? ''));
    $showUrl = filled($img->uuid) ? route('gallery.public.show', $img->uuid) : url($img->url);
@endphp
<div class="group relative">
    <a href="{{ $showUrl }}"
       @if(!filled($img->uuid)) target="_blank" rel="noopener noreferrer" @endif
       class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-400/60 focus-visible:ring-offset-2 focus-visible:ring-offset-[#ffffff] rounded-2xl">
        <div class="relative aspect-square rounded-2xl overflow-hidden bg-gray-100">
            <img src="{{ url($img->url) }}" alt="{{ $alt }}" loading="lazy" decoding="async"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors duration-200"></div>
        </div>
        @if($title !== '')
            <p class="mt-1.5 px-0.5 text-[12px] font-medium text-[#1c1915] truncate">{{ $title }}</p>
        @endif
    </a>

    @include('gallery.partials.download-button', [
        'img' => $img,
        'class' => 'absolute top-2.5 right-2.5 z-[5] inline-flex items-center gap-1 h-7 px-2.5 rounded-full bg-white text-[#1c1915] shadow-[0_2px_8px_rgba(0,0,0,0.3)] text-[11px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-gray-100',
    ])

    @include('gallery.partials.like-button', [
        'img' => $img,
        'isLiked' => $isLiked ?? false,
        'class' => 'absolute bottom-2.5 left-2.5 z-[5] inline-flex items-center gap-1 h-7 px-2.5 rounded-full bg-white/95 text-[#1c1915] shadow-[0_2px_8px_rgba(0,0,0,0.3)] text-[11px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-gray-100',
    ])
</div>
