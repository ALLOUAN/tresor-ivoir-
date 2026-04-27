@extends('layouts.app')

@section('title', 'Mon profil prestataire')
@section('page-title', 'Mon profil prestataire')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white text-lg font-semibold">Informations de la fiche</h2>
        <p class="text-slate-400 text-sm mt-1">Mettez a jour les informations visibles sur votre fiche prestataire.</p>

        <form method="POST" action="{{ route('provider.profile.update') }}" class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs text-slate-400 mb-1">Nom</label>
                <input type="text" name="name" value="{{ old('name', $provider->name) }}" required
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Categorie</label>
                <select name="category_id" required
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $provider->category_id) === (int) $category->id)>
                            {{ $category->name_fr }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Description courte</label>
                <textarea name="short_desc_fr" rows="2"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('short_desc_fr', $provider->short_desc_fr) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Description (FR)</label>
                <textarea name="description_fr" rows="4"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description_fr', $provider->description_fr) }}</textarea>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Telephone principal</label>
                <input type="text" name="phone" value="{{ old('phone', $provider->phone) }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Telephone secondaire</label>
                <input type="text" name="phone2" value="{{ old('phone2', $provider->phone2) }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $provider->email) }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Site web</label>
                <input type="url" name="website" value="{{ old('website', $provider->website) }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Ville</label>
                <input type="text" name="city" value="{{ old('city', $provider->city) }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Region</label>
                <input type="text" name="region" value="{{ old('region', $provider->region) }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Adresse</label>
                <input type="text" name="address" value="{{ old('address', $provider->address) }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>

            <div class="md:col-span-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
                    <i class="fas fa-save"></i>
                    Enregistrer les informations
                </button>
            </div>
        </form>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white text-lg font-semibold">Horaires d'ouverture</h2>
        <p class="text-slate-400 text-sm mt-1">Configurez vos horaires par jour.</p>

        <form method="POST" action="{{ route('provider.profile.hours') }}" class="mt-5 space-y-3">
            @csrf
            @method('PUT')

            @foreach($days as $index => $label)
                @php $hour = $hours[$index]; @endphp
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-center p-3 rounded-lg border border-slate-800 bg-slate-950/40">
                    <div class="text-sm text-slate-200 font-medium">{{ $label }}</div>

                    <input type="hidden" name="hours[{{ $index }}][day_of_week]" value="{{ $index }}">

                    <div class="md:col-span-1">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                            <input type="checkbox" name="hours[{{ $index }}][is_closed]" value="1" @checked(old("hours.$index.is_closed", $hour->is_closed))
                                class="rounded border-slate-600 bg-slate-800 text-amber-500">
                            Ferme
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ouverture</label>
                        <input type="time" name="hours[{{ $index }}][open_time]"
                            value="{{ old("hours.$index.open_time", $hour->open_time ? substr((string) $hour->open_time, 0, 5) : '') }}"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Fermeture</label>
                        <input type="time" name="hours[{{ $index }}][close_time]"
                            value="{{ old("hours.$index.close_time", $hour->close_time ? substr((string) $hour->close_time, 0, 5) : '') }}"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Note</label>
                        <input type="text" name="hours[{{ $index }}][note]"
                            value="{{ old("hours.$index.note", $hour->note) }}"
                            placeholder="Ex: Service reduit"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
            @endforeach

            <button type="submit"
                class="inline-flex items-center gap-2 bg-violet-500 hover:bg-violet-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
                <i class="fas fa-clock"></i>
                Enregistrer les horaires
            </button>
        </form>
    </div>
</div>
@endsection
