@extends('layouts.app')

@section('title', 'Modifier un événement')
@section('page-title', 'Modifier événement')

@section('header-actions')
<div class="flex items-center gap-2 flex-wrap">
    <button type="button" onclick="openEventPreviewTab()"
            class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-eye"></i> Prévisualiser
    </button>
    @if($event->status === 'published')
        <a href="{{ route('events.show', $event->slug) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
            <i class="fas fa-external-link-alt"></i> Voir en ligne
        </a>
    @endif
    <a href="{{ route('editor.events.index') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
        <h2 class="text-white font-semibold">Modifier l'événement</h2>
        <a href="{{ route('editor.events.index') }}" class="text-slate-300 hover:text-white text-sm">
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

    <form id="eventEditorForm" method="POST" action="{{ route('editor.events.update', $event) }}" enctype="multipart/form-data" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @method('PUT')

        <div class="md:col-span-2 pb-3 border-b border-slate-800/60">
            <p id="autosaveStatus" class="text-xs text-slate-600 min-h-[1.25rem]" aria-live="polite"></p>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Titre (FR) *</label>
            <input type="text" name="title_fr" required value="{{ old('title_fr', $event->title_fr) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Titre (EN)</label>
            <input type="text" name="title_en" value="{{ old('title_en', $event->title_en) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $event->slug) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Catégorie *</label>
            <select name="category_id" required
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Choisir...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $event->category_id) === (string) $category->id)>
                        {{ $category->name_fr }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Description (FR)</label>
            <textarea name="description_fr" id="description_fr" rows="6"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description_fr', $event->description_fr) }}</textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Description (EN)</label>
            <textarea name="description_en" id="description_en" rows="5"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description_en', $event->description_en) }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Image (URL)</label>
            <input type="url" name="cover_url" id="cover_url" value="{{ old('cover_url', $event->cover_url) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Image depuis mon ordinateur</label>
            <input type="file" name="cover_image" id="cover_image" accept="image/jpeg,image/png,image/webp"
                   class="w-full bg-slate-800 border border-slate-700 file:border-0 file:bg-slate-700 file:text-slate-300 file:px-3 file:py-2 file:mr-3 rounded-lg px-3 py-2 text-slate-400 text-xs">
            <p class="text-[11px] text-slate-500 mt-1">JPG, PNG ou WEBP (max 4 Mo). Le fichier remplace l'URL si les deux sont remplis.</p>
            @error('cover_image') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div id="cover_preview_wrapper" class="md:col-span-2 {{ old('cover_url', $event->cover_url) ? '' : 'hidden' }}">
            <div class="rounded-lg overflow-hidden h-44 bg-slate-800 border border-slate-700">
                <img id="cover_preview_img" src="{{ old('cover_url', $event->cover_url) }}" alt="Aperçu couverture" class="w-full h-full object-cover">
            </div>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Texte alternatif image</label>
            <input type="text" name="cover_alt" value="{{ old('cover_alt', $event->cover_alt) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                   placeholder="Description de l'image pour l'accessibilité">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Lien billetterie</label>
            <input type="url" name="ticket_url" value="{{ old('ticket_url', $event->ticket_url) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Date/heure début *</label>
            <input type="datetime-local" name="starts_at" required value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Date/heure fin</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Lieu</label>
            <input type="text" name="location_name" value="{{ old('location_name', $event->location_name) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Ville</label>
            <input type="text" name="city" value="{{ old('city', $event->city) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Adresse</label>
            <input type="text" name="address" value="{{ old('address', $event->address) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Latitude</label>
            <input type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $event->latitude) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Longitude</label>
            <input type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $event->longitude) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Prestataire lié (optionnel)</label>
            <select name="provider_id"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Aucun</option>
                @foreach(($providers ?? collect()) as $provider)
                    <option value="{{ $provider->id }}" @selected((string) old('provider_id', $event->provider_id) === (string) $provider->id)>{{ $provider->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Organisateur</label>
            <input type="text" name="organizer_name" value="{{ old('organizer_name', $event->organizer_name) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Téléphone organisateur</label>
            <input type="text" name="organizer_phone" value="{{ old('organizer_phone', $event->organizer_phone) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Email organisateur</label>
            <input type="email" name="organizer_email" value="{{ old('organizer_email', $event->organizer_email) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Statut *</label>
            <select name="status" required
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                @foreach(['draft' => 'Brouillon', 'published' => 'Publié', 'cancelled' => 'Annulé'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $event->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="hidden" name="is_free" value="0">
                <input type="checkbox" name="is_free" id="is_free" value="1" @checked(old('is_free', $event->is_free))
                       class="rounded border-slate-600 bg-slate-800 text-amber-500">
                Événement gratuit
            </label>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Prix</label>
            <input type="number" min="0" step="0.01" name="price" id="price" value="{{ old('price', $event->price) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <p class="text-[11px] text-slate-500 mt-1">Montant en FCFA (laisser 0 si gratuit).</p>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Capacité</label>
            <input type="number" min="1" max="1000000" name="capacity" value="{{ old('capacity', $event->capacity) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Date limite d'inscription</label>
            <input type="datetime-local" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline?->format('Y-m-d\TH:i')) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Fuseau horaire</label>
            <input type="text" name="timezone" value="{{ old('timezone', $event->timezone ?: 'Africa/Abidjan') }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="hidden" name="is_recurring" value="0">
                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" @checked(old('is_recurring', $event->is_recurring))
                       class="rounded border-slate-600 bg-slate-800 text-amber-500">
                Événement récurrent
            </label>
        </div>

        <div class="md:col-span-2 {{ old('is_recurring', $event->is_recurring) ? '' : 'hidden' }}" id="recurrence_group">
            <label class="block text-sm text-slate-300 mb-1">Règle de récurrence</label>
            <input type="text" name="recurrence_rule" value="{{ old('recurrence_rule', $event->recurrence_rule) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                   placeholder="Ex: weekly, every_2_weeks, monthly">
            @error('recurrence_rule') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Meta titre (FR)</label>
            <input type="text" name="meta_title_fr" maxlength="70" value="{{ old('meta_title_fr', $event->meta_title_fr) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Meta description (FR)</label>
            <input type="text" name="meta_desc_fr" maxlength="165" value="{{ old('meta_desc_fr', $event->meta_desc_fr) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Meta titre (EN)</label>
            <input type="text" name="meta_title_en" maxlength="70" value="{{ old('meta_title_en', $event->meta_title_en) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Meta description (EN)</label>
            <input type="text" name="meta_desc_en" maxlength="165" value="{{ old('meta_desc_en', $event->meta_desc_en) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="md:col-span-2 flex justify-end gap-2 pt-2">
            <a href="{{ route('editor.events.index') }}"
               class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                Annuler
            </a>
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                Mettre à jour
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewEventCoverFromUrl(url) {
    const wrapper = document.getElementById('cover_preview_wrapper');
    const img = document.getElementById('cover_preview_img');
    if (!wrapper || !img) return;

    if (url && url.startsWith('http')) {
        img.src = url;
        wrapper.classList.remove('hidden');
    } else {
        wrapper.classList.add('hidden');
    }
}

function previewEventCoverFromFile(fileInputId) {
    const input = document.getElementById(fileInputId);
    const wrapper = document.getElementById('cover_preview_wrapper');
    const img = document.getElementById('cover_preview_img');
    if (!input || !input.files || !input.files[0] || !wrapper || !img) return;

    img.src = URL.createObjectURL(input.files[0]);
    wrapper.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const coverUrl = document.getElementById('cover_url');
    const coverFile = document.getElementById('cover_image');
    const isFreeCheckbox = document.getElementById('is_free');
    const priceInput = document.getElementById('price');

    if (coverUrl && coverUrl.value) {
        previewEventCoverFromUrl(coverUrl.value);
    }

    if (coverUrl) {
        coverUrl.addEventListener('input', (e) => previewEventCoverFromUrl(e.target.value));
    }

    if (coverFile) {
        coverFile.addEventListener('change', () => previewEventCoverFromFile('cover_image'));
    }

    if (isFreeCheckbox && priceInput) {
        const syncPriceState = () => {
            const isFree = isFreeCheckbox.checked;
            priceInput.disabled = isFree;
            priceInput.required = !isFree;

            if (isFree) {
                priceInput.value = '0';
            }
        };

        isFreeCheckbox.addEventListener('change', syncPriceState);
        syncPriceState();
    }

    const recurring = document.getElementById('is_recurring');
    const recurringGroup = document.getElementById('recurrence_group');
    if (recurring && recurringGroup) {
        recurring.addEventListener('change', () => {
            recurringGroup.classList.toggle('hidden', !recurring.checked);
        });
    }
});
</script>
@include('editor.partials.event-rich-tools', ['event' => $event, 'errorsPresent' => $errors->any()])
@endpush
@endsection
