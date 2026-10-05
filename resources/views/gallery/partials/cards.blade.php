@foreach($galleryImages as $img)
    @include('gallery.partials.card', ['img' => $img, 'isLiked' => ($likedMediaIds[$img->id] ?? false)])
@endforeach
