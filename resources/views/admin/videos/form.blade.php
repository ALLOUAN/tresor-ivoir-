@extends('layouts.app')

@section('title', $video->exists ? 'Modifier la vidéo' : 'Ajouter une vidéo')
@section('page-title', $video->exists ? 'Modifier la vidéo' : 'Ajouter une vidéo')

@section('content')
<a href="{{ route('admin.videos.index') }}" class="inline-flex items-center gap-2 mb-4 text-sm text-slate-400 hover:text-orange-400"><i class="fas fa-arrow-left text-xs"></i> Toutes les vidéos</a>

<form method="POST" action="{{ $video->exists ? route('admin.videos.update', $video) : route('admin.videos.store') }}" enctype="multipart/form-data"
      class="bg-green-900 border border-slate-800 rounded-xl p-6 space-y-6">
    @csrf
    @if($video->exists) @method('PUT') @endif

    @include('admin.videos._fields', ['searchUrl' => route('admin.videos.listings')])

    <div class="flex flex-wrap items-center gap-5 pt-4 border-t border-slate-800">
        <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $video->is_featured)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> À la une (mise en avant en haut de la page)</label>
        <label class="flex items-center gap-2 text-sm text-slate-200">Ordre <input type="number" name="sort_order" value="{{ old('sort_order', $video->sort_order ?? 0) }}" class="w-20 bg-slate-800/50 border border-slate-700 rounded-lg px-2 py-1.5 text-white"></label>
        @unless($video->exists)
            <label class="flex items-center gap-2 text-sm text-slate-200"><input type="hidden" name="publish" value="0"><input type="checkbox" name="publish" value="1" checked class="rounded border-slate-600 bg-slate-800 text-orange-500"> Publier tout de suite</label>
        @endunless
        <button type="submit" class="ml-auto px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl flex items-center gap-2"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
</form>
@endsection
