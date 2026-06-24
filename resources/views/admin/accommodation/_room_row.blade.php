{{-- Partial: une ligne de type de chambre (données existantes) --}}
<div class="room-row bg-slate-800/60 border border-slate-700 rounded-xl p-4 space-y-3">
    <div class="flex gap-2 items-center">
        <input type="text" name="room_name[]"
               value="{{ $r['name'] ?? '' }}"
               placeholder="Chambre Standard"
               class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none font-medium">
        <button type="button" onclick="this.closest('.room-row').remove()"
                class="w-8 h-9 rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">Adultes max</span>
            <input type="number" name="room_max_adults[]" min="1" max="10"
                   value="{{ $r['max_adults'] ?? 2 }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">Enfants max</span>
            <input type="number" name="room_max_children[]" min="0" max="10"
                   value="{{ $r['max_children'] ?? 0 }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">Surface m²</span>
            <input type="number" step="0.5" name="room_area_m2[]"
                   value="{{ $r['area_m2'] ?? '' }}" placeholder="25"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div><!-- spacer --></div>
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">Prix XOF/nuit</span>
            <input type="number" name="room_price_xof[]"
                   value="{{ $r['price_xof'] ?? '' }}" placeholder="50 000"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">Prix EUR/nuit</span>
            <input type="number" step="0.01" name="room_price_eur[]"
                   value="{{ $r['price_eur'] ?? '' }}" placeholder="75"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div class="col-span-2">
            <span class="text-[11px] text-slate-500 mb-1 block">
                Équipements <span class="text-slate-600">(séparés par virgule)</span>
            </span>
            <input type="text" name="room_amenities[]"
                   value="{{ is_array($r['amenities'] ?? null) ? implode(', ', $r['amenities']) : ($r['amenities'] ?? '') }}"
                   placeholder="Climatisation, TV, Coffre-fort"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div class="col-span-2">
            {{-- Étiquette + bouton ajouter URL --}}
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] text-slate-500 flex items-center gap-1">
                    <i class="fas fa-images text-[9px] text-amber-400/60"></i>
                    Photos de la chambre
                </span>
                <button type="button" onclick="addRoomPhotoRow(this)"
                        class="inline-flex items-center gap-1 text-[10px] text-amber-400 hover:text-amber-300 transition">
                    <i class="fas fa-plus text-[8px]"></i>Ajouter URL
                </button>
            </div>
            {{-- Input caché qui stocke la valeur CSV finale des URL --}}
            <input type="hidden" name="room_photos[]" class="room-photos-input"
                   value="{{ is_array($r['photos'] ?? null) ? implode(', ', array_filter($r['photos'])) : ($r['photos'] ?? '') }}">
            {{-- Répéteur visuel des URL existantes --}}
            <div class="room-photos-list space-y-1.5 mb-2">
                @foreach(array_filter(is_array($r['photos'] ?? null) ? $r['photos'] : (array_filter(array_map('trim', explode(',', $r['photos'] ?? ''))))) as $photoUrl)
                @php $photoUrl = trim($photoUrl); @endphp
                @if($photoUrl)
                <div class="room-photo-row flex gap-2 items-center">
                    <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">
                        <img src="{{ $photoUrl }}" alt=""
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none';this.nextElementSibling.style.display=''">
                        <i class="fas fa-image text-slate-600 text-xs" style="display:none"></i>
                    </div>
                    <input type="text" value="{{ $photoUrl }}"
                           placeholder="https://…/photo.jpg"
                           class="photo-url-field flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-1.5 text-xs text-slate-100 outline-none transition"
                           oninput="syncRoomPhotos(this)">
                    <button type="button" onclick="removeRoomPhotoRow(this)"
                            class="w-8 h-[34px] rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                @endif
                @endforeach
            </div>
            {{-- Upload fichiers --}}
            <label class="flex items-center gap-2 cursor-pointer group">
                <span class="text-[11px] text-slate-500 group-hover:text-slate-300 transition flex items-center gap-1">
                    <i class="fas fa-cloud-arrow-up text-[9px] text-amber-400/60"></i> Uploader des photos
                </span>
                <input type="file" name="room_photo_files[{{ $i }}][]"
                       multiple accept="image/*"
                       class="room-photo-file-input hidden"
                       onchange="previewRoomFiles(this)">
                <span class="px-2 py-0.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-[10px] rounded transition">
                    Choisir fichiers
                </span>
            </label>
            <div class="room-file-previews flex flex-wrap gap-1.5 mt-1.5"></div>
        </div>
    </div>
</div>
