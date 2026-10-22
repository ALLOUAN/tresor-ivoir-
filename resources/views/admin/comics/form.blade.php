@extends('layouts.app')

@section('title', $comic->exists ? 'Modifier la BD' : 'Ajouter une BD')
@section('page-title', $comic->exists ? 'Modifier la BD' : 'Ajouter une BD')

@section('content')
<a href="{{ route('admin.comics.index') }}" class="inline-flex items-center gap-2 mb-4 text-sm text-slate-400 hover:text-orange-400"><i class="fas fa-arrow-left text-xs"></i> Toutes les BD</a>

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-200 text-sm rounded-xl">
    <ul class="list-disc pl-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $comic->exists ? route('admin.comics.update', $comic) : route('admin.comics.store') }}" enctype="multipart/form-data"
      class="bg-green-900 border border-slate-800 rounded-xl p-6 space-y-6">
    @csrf
    @if($comic->exists) @method('PUT') @endif

    <div class="grid md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-200 mb-1.5" for="title">Titre *</label>
            <input id="title" name="title" type="text" required maxlength="160" value="{{ old('title', $comic->title) }}" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2.5 text-white" placeholder="Ex. : Mian et le secret du kédjénou">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-200 mb-1.5" for="summary">Résumé</label>
            <textarea id="summary" name="summary" rows="3" maxlength="2000" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2.5 text-white" placeholder="Deux ou trois phrases pour donner envie de lire">{{ old('summary', $comic->summary) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-200 mb-1.5" for="category">Rubrique *</label>
            <select id="category" name="category" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2.5 text-white">
                @foreach(\App\Models\Comic::CATEGORIES as $key => $cat)
                    <option value="{{ $key }}" @selected(old('category', $comic->category) === $key)>{{ $cat['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-200 mb-1.5" for="age_label">Public</label>
            <input id="age_label" name="age_label" type="text" maxlength="40" value="{{ old('age_label', $comic->age_label) }}" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2.5 text-white" placeholder="Ex. : Dès 7 ans, Tout public">
        </div>
    </div>

    {{-- Couverture --}}
    <div class="pt-5 border-t border-slate-800">
        <p class="text-sm font-semibold text-slate-200 mb-1">Couverture</p>
        <p class="text-xs text-slate-400 mb-3">Format portrait conseillé (3:4). Sans couverture, la première planche est utilisée.</p>
        <div class="flex flex-wrap items-start gap-4">
            @if($comic->cover_path)
                <label class="block">
                    <img src="{{ $comic->coverUrl() }}" alt="" class="w-28 h-36 object-cover rounded-lg border border-slate-700">
                    <span class="mt-2 flex items-center gap-2 text-xs text-slate-300"><input type="checkbox" name="remove_cover" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500"> Retirer la couverture</span>
                </label>
            @endif
            <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="text-sm text-slate-300 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-orange-500 file:text-black file:font-semibold">
        </div>
    </div>

    {{-- Planches --}}
    <div class="pt-5 border-t border-slate-800">
        <p class="text-sm font-semibold text-slate-200 mb-1">Planches <span class="text-slate-400 font-normal">(ordre de lecture)</span></p>
        <p class="text-xs text-slate-400 mb-3">Images JPG, PNG ou WebP, 8 Mo maximum chacune, {{ \App\Models\Comic::MAX_PAGES }} planches au plus. Les nouvelles planches s'ajoutent à la fin, dans l'ordre de sélection.</p>

        @php $pages = array_values(array_filter((array) $comic->pages)); @endphp
        @if($pages)
            <ol class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-4" data-comic-pages>
                @foreach($pages as $p)
                    <li class="relative rounded-lg border border-slate-700 bg-slate-800/50 p-2" data-page>
                        <input type="hidden" name="pages_keep[]" value="{{ $p }}">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p) }}" alt="" class="w-full aspect-[3/4] object-cover rounded">
                        <div class="mt-2 flex items-center justify-between gap-1 text-xs text-slate-300">
                            <span class="font-semibold" data-page-num>{{ $loop->iteration }}</span>
                            <span class="flex gap-1">
                                <button type="button" class="w-7 h-7 rounded border border-slate-600 hover:border-orange-500" data-move="-1" aria-label="Avancer"><i class="fas fa-arrow-left text-[10px]"></i></button>
                                <button type="button" class="w-7 h-7 rounded border border-slate-600 hover:border-orange-500" data-move="1" aria-label="Reculer"><i class="fas fa-arrow-right text-[10px]"></i></button>
                                <button type="button" class="w-7 h-7 rounded border border-red-800/60 text-red-300 hover:bg-red-900/30" data-remove aria-label="Retirer"><i class="fas fa-xmark text-[10px]"></i></button>
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
        <input type="file" name="new_pages[]" multiple accept="image/jpeg,image/png,image/webp" class="text-sm text-slate-300 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-orange-500 file:text-black file:font-semibold">
    </div>

    <div class="flex flex-wrap items-center gap-5 pt-4 border-t border-slate-800">
        <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $comic->is_published)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Publiée</label>
        <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $comic->is_featured)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> À la une</label>
        <label class="flex items-center gap-2 text-sm text-slate-200">Ordre <input type="number" name="sort_order" value="{{ old('sort_order', $comic->sort_order ?? 0) }}" class="w-20 bg-slate-800/50 border border-slate-700 rounded-lg px-2 py-1.5 text-white"></label>
        <button type="submit" class="ml-auto px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl flex items-center gap-2"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
</form>

<script>
(function () {
    var list = document.querySelector('[data-comic-pages]');
    if (!list) return;
    function renumber() { list.querySelectorAll('[data-page]').forEach(function (li, i) { li.querySelector('[data-page-num]').textContent = i + 1; }); }
    list.addEventListener('click', function (e) {
        var btn = e.target.closest('button'); if (!btn) return;
        var li = btn.closest('[data-page]');
        if (btn.hasAttribute('data-remove')) { li.remove(); renumber(); return; }
        var dir = +btn.dataset.move;
        if (dir < 0 && li.previousElementSibling) list.insertBefore(li, li.previousElementSibling);
        if (dir > 0 && li.nextElementSibling) list.insertBefore(li.nextElementSibling, li);
        renumber();
    });
})();
</script>
@endsection
