@extends('layouts.app')

@section('title', $room ? 'Modifier la chambre' : 'Ajouter une chambre')
@section('page-title', $room ? 'Modifier la chambre' : 'Ajouter une chambre')

@section('header-actions')
    <a href="{{ route('provider.accommodation.rooms.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour
    </a>
@endsection

@section('content')

<div class="max-w-2xl">
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $room ? route('provider.accommodation.rooms.update', $room['id']) : route('provider.accommodation.rooms.store') }}"
          enctype="multipart/form-data"
          class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6 space-y-4">
        @csrf
        @if($room) @method('PUT') @endif

        <div>
            <label class="block text-xs text-slate-400 mb-1">Nom de la chambre <span class="text-red-400">*</span></label>
            <input type="text" name="name" required maxlength="150" value="{{ old('name', $room['name'] ?? '') }}" placeholder="Chambre Standard"
                   class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] text-slate-500 mb-1">Adultes max</label>
                <input type="number" name="max_adults" min="1" max="10" value="{{ old('max_adults', $room['max_adults'] ?? 2) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-[11px] text-slate-500 mb-1">Enfants max</label>
                <input type="number" name="max_children" min="0" max="10" value="{{ old('max_children', $room['max_children'] ?? 0) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-[11px] text-slate-500 mb-1">Surface m²</label>
                <input type="number" step="0.5" name="area_m2" value="{{ old('area_m2', $room['area_m2'] ?? '') }}" placeholder="25"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[11px] text-slate-500 mb-1">Prix XOF/nuit <span class="text-rose-400">*</span></label>
                <input type="number" name="price_xof" value="{{ old('price_xof', $room['price_xof'] ?? '') }}" placeholder="50 000" required min="1"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-[11px] text-slate-500 mb-1">Prix EUR/nuit</label>
                <input type="number" step="0.01" name="price_eur" value="{{ old('price_eur', $room['price_eur'] ?? '') }}" placeholder="75"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
        </div>

        <div>
            <label class="block text-[11px] text-slate-500 mb-1">Équipements <span class="text-slate-600">(séparés par virgule)</span></label>
            <input type="text" name="amenities"
                   value="{{ old('amenities', is_array($room['amenities'] ?? null) ? implode(', ', $room['amenities']) : '') }}"
                   placeholder="Climatisation, TV, Coffre-fort"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>

        <div>
            <label class="block text-[11px] text-slate-500 mb-1">Lits</label>
            <input type="text" name="beds" value="{{ old('beds', $room['beds'] ?? '') }}" placeholder="1 lit double"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>

        <div>
            <label class="block text-[11px] text-slate-500 mb-1">Description</label>
            <textarea name="description" rows="3" placeholder="Description de cette chambre…"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none resize-none">{{ old('description', $room['description'] ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-[11px] text-slate-500 mb-1">Conditions applicables à cette chambre</label>
            <textarea name="conditions" rows="3" placeholder="Ex : petit-déjeuner inclus, non remboursable…"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none resize-none">{{ old('conditions', $room['conditions'] ?? '') }}</textarea>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-[11px] text-slate-500 flex items-center gap-1">
                    <i class="fas fa-images text-[9px] text-orange-400/60"></i> Photos de la chambre
                </label>
                <button type="button" onclick="addRoomPhotoRow()"
                        class="inline-flex items-center gap-1 text-[10px] text-orange-400 hover:text-orange-300 transition">
                    <i class="fas fa-plus text-[8px]"></i>Ajouter URL
                </button>
            </div>
            <input type="hidden" name="room_photos" id="room-photos-input"
                   value="{{ old('room_photos', is_array($room['photos'] ?? null) ? implode(', ', array_filter($room['photos'])) : '') }}">
            <div id="room-photos-list" class="space-y-1.5 mb-2">
                @foreach(array_filter($room['photos'] ?? []) as $photoUrl)
                <div class="room-photo-row flex gap-2 items-center">
                    <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">
                        <img src="{{ $photoUrl }}" alt="" class="w-full h-full object-cover"
                             onerror="this.style.display='none';this.nextElementSibling.style.display=''">
                        <i class="fas fa-image text-slate-600 text-xs" style="display:none"></i>
                    </div>
                    <input type="text" value="{{ $photoUrl }}" placeholder="https://…/photo.jpg"
                           class="photo-url-field flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-1.5 text-xs text-slate-100 outline-none transition"
                           oninput="syncRoomPhotos()">
                    <button type="button" onclick="removeRoomPhotoRow(this)"
                            class="w-8 h-[34px] rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                @endforeach
            </div>
            <label class="flex items-center gap-2 cursor-pointer group">
                <span class="text-[11px] text-slate-500 group-hover:text-slate-300 transition flex items-center gap-1">
                    <i class="fas fa-cloud-arrow-up text-[9px] text-orange-400/60"></i> Uploader des photos
                </span>
                <input type="file" name="room_photo_files[]" multiple accept="image/*" class="hidden" onchange="previewRoomFiles(this)">
                <span class="px-2 py-0.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-[10px] rounded transition">Choisir fichiers</span>
            </label>
            <div id="room-file-previews" class="flex flex-wrap gap-1.5 mt-1.5"></div>
        </div>

        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            <i class="fas fa-floppy-disk"></i> {{ $room ? 'Enregistrer' : 'Ajouter la chambre' }}
        </button>
    </form>
</div>

@push('scripts')
<script>
function addRoomPhotoRow() {
    const list = document.getElementById('room-photos-list');
    const row = document.createElement('div');
    row.className = 'room-photo-row flex gap-2 items-center';
    row.innerHTML =
        '<div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">'
        + '<img src="" alt="" class="w-full h-full object-cover" style="display:none"'
        + ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'\'">'
        + '<i class="fas fa-image text-slate-600 text-xs"></i>'
        + '</div>'
        + '<input type="text" placeholder="https://…/photo.jpg"'
        + ' class="photo-url-field flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-1.5 text-xs text-slate-100 outline-none transition"'
        + ' oninput="syncRoomPhotos()">'
        + '<button type="button" onclick="removeRoomPhotoRow(this)"'
        + ' class="w-8 h-[34px] rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">'
        + '<i class="fas fa-times text-xs"></i>'
        + '</button>';
    list.appendChild(row);
    row.querySelector('input[type="text"]').focus();
}

function removeRoomPhotoRow(btn) {
    btn.closest('.room-photo-row').remove();
    syncRoomPhotos();
}

function syncRoomPhotos() {
    const urls = Array.from(document.querySelectorAll('#room-photos-list .photo-url-field'))
        .map(i => i.value.trim())
        .filter(Boolean);
    document.getElementById('room-photos-input').value = urls.join(', ');
}

function previewRoomFiles(input) {
    const container = document.getElementById('room-file-previews');
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
            img.className = 'w-16 h-16 object-cover rounded-lg border border-slate-700';
            img.title = file.name;
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('DOMContentLoaded', syncRoomPhotos);
</script>
@endpush
@endsection
