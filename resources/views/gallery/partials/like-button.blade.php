@php
    $liked = $isLiked ?? false;
    $count = $img->likes_count ?? 0;
@endphp
<button type="button"
    class="{{ $class ?? '' }} gallery-like-btn"
    data-media-uuid="{{ $img->uuid }}"
    data-liked="{{ $liked ? '1' : '0' }}"
    aria-pressed="{{ $liked ? 'true' : 'false' }}"
    aria-label="Aimer cette image">
    <i class="{{ $liked ? 'fas text-red-500' : 'far' }} fa-heart text-[11px] like-icon"></i>
    <span class="like-count">{{ $count }}</span>
</button>
