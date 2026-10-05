@extends('layouts.app')

@section('title', $activity->exists ? 'Modifier l\'activité' : 'Nouvelle activité')
@section('page-title', $activity->exists ? 'Modifier l\'activité' : 'Nouvelle activité — Loisirs & Culture')

@section('header-actions')
    <a href="{{ route('admin.activities.index') }}"
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

    @if($operators->isEmpty())
        <div class="mb-4 px-4 py-3 bg-amber-900/30 border border-amber-700/40 text-amber-200 text-sm rounded-xl">
            Aucun prestataire n'est encore rattaché à la catégorie Loisirs & Culture. Créez-en un d'abord (Prestataires → catégorie « Loisirs & Culture »).
        </div>
    @endif

    <form method="POST"
          action="{{ $activity->exists ? route('admin.activities.update', $activity) : route('admin.activities.store') }}"
          enctype="multipart/form-data"
          class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6 space-y-5">
        @csrf
        @if($activity->exists) @method('PUT') @endif

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Prestataire</label>
            <select name="provider_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                <option value="">Sélectionner un prestataire…</option>
                @foreach($operators as $operator)
                    <option value="{{ $operator->id }}" @selected(old('provider_id', $activity->provider_id) == $operator->id)>{{ $operator->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Nom de l'activité</label>
            <input type="text" name="name" required maxlength="255" value="{{ old('name', $activity->name) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Catégorie</label>
                <select name="category_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $activity->category_id) == $category->id)>{{ $category->name_fr }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Prix (XOF)</label>
                <input type="number" name="price_xof" required min="0" value="{{ old('price_xof', $activity->price_xof) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Durée (minutes)</label>
                <input type="number" name="duration_minutes" min="1" max="10080" value="{{ old('duration_minutes', $activity->duration_minutes) }}" placeholder="60"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Participants max</label>
                <input type="number" name="max_participants" min="1" max="999" value="{{ old('max_participants', $activity->max_participants) }}" placeholder="10"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
            </div>
        </div>

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Description</label>
            <textarea name="description" rows="4" maxlength="5000"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">{{ old('description', $activity->description) }}</textarea>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="hidden" name="is_available" value="0">
            <input type="checkbox" name="is_available" value="1"
                   class="rounded border-slate-600 bg-slate-800 text-orange-500"
                   {{ old('is_available', $activity->exists ? $activity->is_available : true) ? 'checked' : '' }}>
            <span class="text-sm text-slate-300 group-hover:text-white transition">
                Disponible <span class="text-slate-500 text-xs">(visible sur la fiche du prestataire)</span>
            </span>
        </label>

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Photos de l'activité</label>
            @if($activity->exists && !empty($activity->images))
                <div class="flex flex-wrap gap-3 mb-3">
                    @foreach($activity->images as $img)
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
            <p class="text-slate-600 text-[11px] mt-1">{{ $activity->exists ? 'Les nouvelles photos s\'ajoutent aux existantes.' : 'Optionnel.' }}</p>
        </div>

        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            <i class="fas fa-floppy-disk"></i> {{ $activity->exists ? 'Enregistrer' : 'Créer l\'activité' }}
        </button>
    </form>
</div>

@endsection
