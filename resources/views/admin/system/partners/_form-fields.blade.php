@php
    $isEdit = isset($partner) && $partner->exists;
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-building text-amber-400/90"></i> Informations du partenaire
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Nom du partenaire <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $partner->name) }}" required maxlength="200"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Type de partenariat <span class="text-red-400">*</span></label>
                    <select name="partnership_type" required
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none">
                        <option value="">— Choisir —</option>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('partnership_type', $partner->partnership_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Site web</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $partner->website_url) }}" maxlength="500" placeholder="https://exemple.com"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none placeholder-slate-600">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Date de début du partenariat</label>
                    <input type="date" name="partnership_start_date" value="{{ old('partnership_start_date', $partner->partnership_start_date?->format('Y-m-d')) }}"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Description</label>
                    <textarea name="description" rows="5" placeholder="Description du partenariat…"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none resize-y placeholder-slate-600">{{ old('description', $partner->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-address-card text-amber-400/90"></i> Contact
            </h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1.5">Personne de contact</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $partner->contact_person) }}" maxlength="150"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">E-mail de contact</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $partner->contact_email) }}" maxlength="255"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Téléphone de contact</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $partner->contact_phone) }}" maxlength="50"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4">Logo du partenaire</h2>
            @if($isEdit && $partner->logo_url)
                <div class="mb-4 flex justify-center">
                    <img src="{{ $partner->logo_url }}" alt="" class="max-h-24 max-w-full object-contain rounded-lg border border-slate-700 bg-slate-800 p-2">
                </div>
            @endif
            <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 hover:file:bg-slate-600 cursor-pointer">
            <p class="text-slate-500 text-[11px] mt-2">JPG, PNG, WebP — max 2&nbsp;Mo</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4">Paramètres</h2>
            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        class="rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500/30 w-4 h-4"
                        @checked(old('is_active', $partner->is_active ? '1' : '0') === '1')>
                    <span class="text-slate-300 text-sm">Partenaire actif</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1"
                        class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500/30 w-4 h-4"
                        @checked(old('is_featured', $partner->is_featured ? '1' : '0') === '1')>
                    <span class="text-slate-300 text-sm">Mettre en vedette</span>
                </label>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Ordre d’affichage</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $partner->sort_order ?? 0) }}" min="0" max="99999"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2.5 text-slate-200 text-sm outline-none">
                </div>
            </div>
        </div>
    </div>
</div>
