@extends('layouts.app')

@section('title', $menuItem->exists ? 'Modifier le plat' : 'Ajouter un plat')
@section('page-title', $menuItem->exists ? 'Modifier le plat' : 'Ajouter un plat')

@section('header-actions')
    <a href="{{ route('provider.menu.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour
    </a>
@endsection

@section('content')

<div class="max-w-2xl">
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $menuItem->exists ? route('provider.menu.update', $menuItem) : route('provider.menu.store') }}"
          enctype="multipart/form-data"
          class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6 space-y-5">
        @csrf
        @if($menuItem->exists) @method('PUT') @endif

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Nom du plat</label>
            <input type="text" name="name" required maxlength="255" value="{{ old('name', $menuItem->name) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Catégorie</label>
                <select name="category_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $menuItem->category_id) == $category->id)>{{ $category->name_fr }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Prix (XOF)</label>
                <input type="number" name="price_xof" required min="0" value="{{ old('price_xof', $menuItem->price_xof) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
            </div>
        </div>

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Description</label>
            <textarea name="description" rows="4" maxlength="2000"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">{{ old('description', $menuItem->description) }}</textarea>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="hidden" name="is_available" value="0">
            <input type="checkbox" name="is_available" value="1"
                   class="rounded border-slate-600 bg-slate-800 text-orange-500"
                   {{ old('is_available', $menuItem->exists ? $menuItem->is_available : true) ? 'checked' : '' }}>
            <span class="text-sm text-slate-300 group-hover:text-white transition">
                Disponible <span class="text-slate-500 text-xs">(visible sur la fiche du restaurant)</span>
            </span>
        </label>

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Photo du plat</label>
            @if($menuItem->exists && !empty($menuItem->images))
                <div class="flex flex-wrap gap-3 mb-3">
                    @foreach($menuItem->images as $img)
                        <label class="relative cursor-pointer group">
                            <img src="{{ $img }}" class="w-20 h-20 rounded-lg object-cover border border-slate-700 group-has-[:checked]:opacity-40 group-has-[:checked]:border-rose-500 transition">
                            <input type="checkbox" name="remove_images[]" value="{{ $img }}" class="peer sr-only">
                            <span class="absolute inset-0 hidden peer-checked:flex items-center justify-center bg-rose-900/60 rounded-lg">
                                <i class="fas fa-trash text-rose-200 text-sm"></i>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="text-slate-600 text-[11px] mb-2">Cochez une photo pour la marquer à supprimer à l'enregistrement.</p>
            @endif
            <input type="file" name="images[]" accept="image/*" multiple
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-slate-700 file:text-slate-200">
            <p class="text-slate-600 text-[11px] mt-1">{{ $menuItem->exists ? 'Les nouvelles photos s\'ajoutent aux existantes.' : 'Optionnel.' }}</p>
        </div>

        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            <i class="fas fa-floppy-disk"></i> {{ $menuItem->exists ? 'Enregistrer' : 'Ajouter à la carte' }}
        </button>
    </form>
</div>

@endsection
