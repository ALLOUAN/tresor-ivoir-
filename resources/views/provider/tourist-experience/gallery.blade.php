@extends('layouts.app')

@section('title', 'Galerie photos')
@section('page-title', 'Galerie photos')

@section('header-actions')
    <a href="{{ route('provider.tourist-experience.dashboard') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour
    </a>
@endsection

@section('content')
@php $e = $experience; @endphp

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

@if(!$e)
<div class="px-4 py-3 bg-amber-900/30 border border-amber-800 text-amber-200 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-info shrink-0"></i>
    Complétez d'abord la
    <a href="{{ route('provider.tourist-experience.profile.edit') }}" class="underline font-semibold">fiche établissement</a>
    avant d'ajouter des photos.
</div>
@else

<div class="bg-green-900 border border-slate-800 rounded-xl p-6">
    @if($e->photos->isNotEmpty())
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-5">
            @foreach($e->photos as $m)
            <div class="relative group">
                <img src="{{ $m->url }}" alt="{{ $m->alt_text ?? '' }}" loading="lazy"
                     class="w-full aspect-square object-cover rounded-lg border border-slate-700">
                <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                    <button type="submit" form="media-del-{{ $m->id }}" onclick="return confirm('Supprimer cette photo ?')"
                            class="w-6 h-6 bg-red-900/80 hover:bg-red-700 text-red-200 rounded-md flex items-center justify-center">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <p class="text-slate-500 text-sm mb-5">Aucune photo ajoutée pour le moment.</p>
    @endif

    <form method="POST" action="{{ route('provider.tourist-experience.gallery.store') }}" enctype="multipart/form-data">
        @csrf
        <label class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-6 cursor-pointer transition group">
            <i class="fas fa-cloud-arrow-up text-2xl text-slate-600 group-hover:text-orange-400/70 mb-2 transition"></i>
            <span class="text-slate-500 text-sm group-hover:text-slate-300 transition">Ajouter des photos</span>
            <span class="text-slate-700 text-xs mt-1">JPG, PNG, WebP — plusieurs fichiers acceptés</span>
            <input type="file" name="media_files[]" multiple accept="image/*" class="hidden" onchange="showMediaPreviews(this)">
        </label>
        <div id="media-previews" class="flex flex-wrap gap-2 mt-3"></div>

        <button type="submit" class="mt-4 inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            <i class="fas fa-upload"></i> Enregistrer
        </button>
    </form>
</div>

@foreach($e->photos as $m)
<form id="media-del-{{ $m->id }}" method="POST" action="{{ route('provider.tourist-experience.gallery.destroy', $m) }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endforeach

@endif

@push('scripts')
<script>
function showMediaPreviews(input) {
    const container = document.getElementById('media-previews');
    container.innerHTML = '';
    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'];
    const files = Array.from(input.files);
    const rejected = files.filter(f => !validTypes.includes(f.type));
    if (rejected.length) {
        const dt = new DataTransfer();
        files.filter(f => validTypes.includes(f.type)).forEach(f => dt.items.add(f));
        input.files = dt.files;
        const warn = document.createElement('p');
        warn.className = 'w-full text-xs text-red-400 mb-1';
        warn.textContent = rejected.map(f => `« ${f.name} » ignoré (non-image)`).join(' · ');
        container.appendChild(warn);
    }
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded-lg border border-slate-700';
            img.title = file.name;
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
@endsection
