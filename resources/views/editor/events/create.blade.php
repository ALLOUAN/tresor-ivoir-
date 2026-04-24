@extends('layouts.app')

@section('title', 'Créer un événement')
@section('page-title', 'Nouvel événement')

@section('content')
<div class="max-w-5xl mx-auto bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
        <h2 class="text-white font-semibold">Créer un événement</h2>
        <a href="{{ route('admin.events.index') }}" class="text-slate-300 hover:text-white text-sm">
            Retour aux événements
        </a>
    </div>

    @if($errors->any())
        <div class="mx-5 mt-5 p-3 bg-red-900/30 border border-red-700 rounded-lg text-red-200 text-sm">
            <p class="font-semibold mb-1">Merci de corriger les champs suivants :</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('editor.events.store') }}" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf

        <div>
            <label class="block text-sm text-slate-300 mb-1">Titre (FR) *</label>
            <input type="text" name="title_fr" required value="{{ old('title_fr') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Titre (EN)</label>
            <input type="text" name="title_en" value="{{ old('title_en') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                   placeholder="Laisser vide pour génération automatique">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Catégorie *</label>
            <select name="category_id" required
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Choisir...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                        {{ $category->name_fr }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Description (FR)</label>
            <textarea name="description_fr" rows="4"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description_fr') }}</textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Description (EN)</label>
            <textarea name="description_en" rows="3"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description_en') }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Image (URL)</label>
            <input type="url" name="cover_url" value="{{ old('cover_url') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Lien billetterie</label>
            <input type="url" name="ticket_url" value="{{ old('ticket_url') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Date/heure début *</label>
            <input type="datetime-local" name="starts_at" required value="{{ old('starts_at') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Date/heure fin</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Lieu</label>
            <input type="text" name="location_name" value="{{ old('location_name') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Ville</label>
            <input type="text" name="city" value="{{ old('city') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Adresse</label>
            <input type="text" name="address" value="{{ old('address') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Organisateur</label>
            <input type="text" name="organizer_name" value="{{ old('organizer_name') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Téléphone organisateur</label>
            <input type="text" name="organizer_phone" value="{{ old('organizer_phone') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Statut *</label>
            <select name="status" required
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                @foreach(['draft' => 'Brouillon', 'published' => 'Publié', 'cancelled' => 'Annulé'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" name="is_free" value="1" @checked(old('is_free'))
                       class="rounded border-slate-600 bg-slate-800 text-amber-500">
                Événement gratuit
            </label>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Prix</label>
            <input type="number" min="0" step="0.01" name="price" value="{{ old('price') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Meta titre (FR)</label>
            <input type="text" name="meta_title_fr" maxlength="70" value="{{ old('meta_title_fr') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Meta description (FR)</label>
            <input type="text" name="meta_desc_fr" maxlength="165" value="{{ old('meta_desc_fr') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="md:col-span-2 flex justify-end gap-2 pt-2">
            <a href="{{ route('admin.events.index') }}"
               class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                Annuler
            </a>
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
