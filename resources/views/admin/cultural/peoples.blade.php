@extends('layouts.app')

@section('title', 'Cultures Ivoiriennes — Peuples')
@section('page-title', 'Peuples & Ethnies')

@section('header-actions')
<button onclick="openPeopleModal()"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouveau peuple
</button>
@endsection

@section('content')

@include('admin.cultural.partials.subnav')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['fas fa-people-group', 'text-slate-300',   $counts['total'],    'Total'],
        ['fas fa-circle-check', 'text-emerald-400', $counts['active'],   'Actifs'],
        ['fas fa-star',         'text-orange-400',   $counts['featured'], 'En vedette'],
    ] as [$icon, $color, $val, $lbl])
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <i class="{{ $icon }} {{ $color }} text-sm mb-2 block"></i>
        <p class="text-2xl font-bold text-white">{{ $val }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $lbl }}</p>
    </div>
    @endforeach
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('admin.cultural.peoples.index') }}" class="mb-5 flex gap-2">
    <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un peuple…"
        class="flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-slate-100 text-sm outline-none transition placeholder-slate-500">
    <select name="zone"
        class="bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-slate-100 text-sm outline-none">
        <option value="">Toutes zones</option>
        @foreach(['Nord','Sud','Est','Ouest','Centre'] as $z)
        <option value="{{ $z }}" {{ $zone === $z ? 'selected' : '' }}>{{ $z }}</option>
        @endforeach
    </select>
    <button class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-search"></i>
    </button>
</form>

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Table --}}
<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Peuple</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Zone / Famille</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">Population</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($peoples as $people)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($people->thumbnail)
                            <img src="{{ $people->thumbnail }}" class="w-10 h-10 rounded-lg object-cover shrink-0 hidden sm:block">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 hidden sm:block">
                                <i class="fas fa-people-group text-slate-600 text-sm"></i>
                            </div>
                            @endif
                            <div>
                                <p class="text-white font-medium">{{ $people->name }}</p>
                                @if($people->is_featured)
                                <span class="text-orange-400 text-[10px]"><i class="fas fa-star mr-0.5"></i>En vedette</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($people->zone_geographique)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[11px]">
                            <i class="fas fa-map-location-dot text-orange-400/60 text-[9px]"></i>
                            {{ $people->zone_geographique }}
                        </span>
                        @endif
                        <p class="text-slate-500 text-xs mt-1">{{ $people->famille_linguistique }}</p>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell text-slate-400 text-xs">
                        {{ $people->population_estimee ? number_format($people->population_estimee, 0, ',', ' ') : '—' }}
                    </td>
                    <td class="px-5 py-4">
                        <form method="POST" action="{{ route('admin.cultural.peoples.toggle', $people) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold transition
                                    {{ $people->is_active
                                        ? 'bg-emerald-900/40 text-emerald-400 hover:bg-red-900/40 hover:text-red-400'
                                        : 'bg-slate-800 text-slate-500 hover:bg-emerald-900/40 hover:text-emerald-400' }}">
                                <i class="fas fa-circle text-[8px]"></i>
                                {{ $people->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('admin.cultural.peoples.featured', $people) }}">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $people->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center transition
                                        {{ $people->is_featured ? 'bg-orange-500/20 text-orange-400' : 'bg-slate-800 text-slate-600 hover:text-orange-400' }}">
                                    <i class="fas fa-star text-xs"></i>
                                </button>
                            </form>
                            <button onclick="openEditPeopleModal({{ $people->id }})"
                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.cultural.peoples.destroy', $people) }}"
                                onsubmit="return confirm('Supprimer {{ addslashes($people->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Modal édition inline --}}
                <tr id="edit-people-{{ $people->id }}" class="hidden bg-slate-800/50">
                    <td colspan="5" class="px-5 py-5">
                        <form method="POST" action="{{ route('admin.cultural.peoples.update', $people) }}" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Nom</label>
                                    <input type="text" name="name" value="{{ $people->name }}" required maxlength="100"
                                        class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Zone géographique</label>
                                    <select name="zone_geographique"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                        <option value="">—</option>
                                        @foreach(['Nord','Sud','Est','Ouest','Centre'] as $z)
                                        <option value="{{ $z }}" {{ $people->zone_geographique === $z ? 'selected' : '' }}>{{ $z }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Famille linguistique</label>
                                    <input type="text" name="famille_linguistique" value="{{ $people->famille_linguistique }}" maxlength="100"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Langue principale</label>
                                    <input type="text" name="langue_principale" value="{{ $people->langue_principale }}" maxlength="100"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Population estimée</label>
                                    <input type="number" name="population_estimee" value="{{ $people->population_estimee }}" min="0"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Capitale culturelle</label>
                                    <input type="text" name="capitale_culturelle" value="{{ $people->capitale_culturelle }}" maxlength="100"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="text-xs text-slate-400 mb-1 block">Description (Présentation)</label>
                                    <textarea name="description" rows="3"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ $people->description }}</textarea>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="text-xs text-slate-400 mb-1 block">Histoire</label>
                                    <textarea name="histoire" rows="5"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ $people->histoire }}</textarea>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="text-xs text-slate-400 mb-1 block">Symboles</label>
                                    <div id="symboles-container-{{ $people->id }}" class="space-y-2">
                                        @foreach(($people->symboles ?: []) as $symbole)
                                        <div class="flex gap-2 items-center symbole-row">
                                            <input type="text" name="symbole_label[]" value="{{ $symbole['label'] ?? '' }}" placeholder="Label (ex: Couleur)"
                                                class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                            <input type="text" name="symbole_valeur[]" value="{{ $symbole['valeur'] ?? '' }}" placeholder="Valeur (ex: Blanc)"
                                                class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                            <button type="button" onclick="this.closest('.symbole-row').remove()"
                                                class="w-8 h-8 shrink-0 rounded-lg bg-slate-800 hover:bg-red-900/40 text-slate-500 hover:text-red-400 flex items-center justify-center transition">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button type="button" onclick="addSymboleRow('symboles-container-{{ $people->id }}')"
                                        class="mt-2 text-xs text-orange-400 hover:text-orange-300 transition">
                                        <i class="fas fa-circle-plus"></i> Ajouter un symbole
                                    </button>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Miniature</label>
                                    <img id="preview-thumb-{{ $people->id }}" src="{{ $people->thumbnail ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBTAA7' }}" class="w-16 h-16 object-cover rounded-lg border border-slate-700 mb-2 {{ $people->thumbnail ? '' : 'hidden' }}">
                                    <input type="file" name="thumbnail_file" accept="image/jpeg,image/png,image/webp"
                                        onchange="previewPeopleImage(this, 'preview-thumb-{{ $people->id }}')"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 outline-none file:mr-2 file:px-2 file:py-1 file:rounded-md file:border-0 file:bg-slate-700 file:text-slate-200 file:text-xs">
                                    @if($people->thumbnail)
                                    <label class="flex items-center gap-2 text-[11px] text-slate-500 mt-1.5 cursor-pointer">
                                        <input type="checkbox" name="remove_thumbnail" value="1" class="rounded border-slate-600 bg-slate-800 text-red-500"
                                            onchange="toggleRemoveImagePreview(this, 'preview-thumb-{{ $people->id }}')">
                                        Supprimer l'image actuelle
                                    </label>
                                    @endif
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs text-slate-400 mb-1 block">
                                        Bannières{{ !empty($people->cover_images) ? ' ('.count($people->cover_images).')' : '' }}
                                    </label>
                                    @if(!empty($people->cover_images))
                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-2">
                                        @foreach($people->cover_images as $url)
                                        <div>
                                            <img src="{{ $url }}" class="w-full h-16 object-cover rounded-lg border border-slate-700 mb-1" loading="lazy">
                                            <label class="flex items-center gap-1.5 text-[10px] text-slate-500 cursor-pointer">
                                                <input type="checkbox" name="remove_cover_images[]" value="{{ $url }}" class="rounded border-slate-600 bg-slate-800 text-red-500">
                                                Supprimer
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                    <div id="new-covers-preview-{{ $people->id }}" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-2"></div>
                                    <input type="file" name="cover_images_file[]" multiple accept="image/jpeg,image/png,image/webp"
                                        onchange="previewPeopleMultiImages(this, 'new-covers-preview-{{ $people->id }}')"
                                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 outline-none file:mr-2 file:px-2 file:py-1 file:rounded-md file:border-0 file:bg-slate-700 file:text-slate-200 file:text-xs">
                                    <p class="text-[10px] text-slate-500 mt-1">Plusieurs images peuvent être sélectionnées en une fois — elles s'ajoutent aux bannières existantes.</p>
                                </div>
                                <div class="md:col-span-3 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ $people->is_active ? 'checked' : '' }}
                                                class="rounded border-slate-600 bg-slate-800 text-orange-500">
                                            Actif
                                        </label>
                                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                                            <input type="checkbox" name="is_featured" value="1" {{ $people->is_featured ? 'checked' : '' }}
                                                class="rounded border-slate-600 bg-slate-800 text-orange-500">
                                            En vedette
                                        </label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="closeEditPeopleModal({{ $people->id }})"
                                            class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs rounded-lg transition">
                                            Annuler
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-black font-semibold text-xs rounded-lg transition">
                                            Enregistrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-slate-600">
                        <i class="fas fa-people-group text-4xl mb-3 block"></i>
                        Aucun peuple trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $peoples->links() }}

{{-- Modal création --}}
<div id="modal-people-create" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-green-950/70 backdrop-blur-sm">
    <div class="bg-green-900 border border-slate-700 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-5 border-b border-slate-800">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-people-group text-orange-400"></i> Nouveau peuple
            </h3>
            <button onclick="closePeopleModal()" class="text-slate-500 hover:text-white transition"><i class="fas fa-times"></i></button>
        </div>
        <form id="form-people-create" method="POST" action="{{ route('admin.cultural.peoples.store') }}" enctype="multipart/form-data" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required maxlength="100"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Zone géographique</label>
                    <select name="zone_geographique"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                        <option value="">—</option>
                        @foreach(['Nord','Sud','Est','Ouest','Centre'] as $z)
                        <option value="{{ $z }}">{{ $z }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Famille linguistique</label>
                    <input type="text" name="famille_linguistique" maxlength="100" placeholder="Kwa, Mandé, Gur, Krou…"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Langue principale</label>
                    <input type="text" name="langue_principale" maxlength="100"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Capitale culturelle</label>
                    <input type="text" name="capitale_culturelle" maxlength="100"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Population estimée</label>
                    <input type="number" name="population_estimee" min="0"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="0" min="0"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Description (Présentation)</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Histoire</label>
                    <textarea name="histoire" rows="4"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Symboles</label>
                    <div id="symboles-container-create" class="space-y-2">
                        <div class="flex gap-2 items-center symbole-row">
                            <input type="text" name="symbole_label[]" placeholder="Label (ex: Couleur)"
                                class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            <input type="text" name="symbole_valeur[]" placeholder="Valeur (ex: Blanc)"
                                class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            <button type="button" onclick="this.closest('.symbole-row').remove()"
                                class="w-8 h-8 shrink-0 rounded-lg bg-slate-700 hover:bg-red-900/40 text-slate-500 hover:text-red-400 flex items-center justify-center transition">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" onclick="addSymboleRow('symboles-container-create')"
                        class="mt-2 text-xs text-orange-400 hover:text-orange-300 transition">
                        <i class="fas fa-circle-plus"></i> Ajouter un symbole
                    </button>
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Miniature</label>
                    <img id="preview-thumb-create" src="" class="w-16 h-16 object-cover rounded-lg border border-slate-700 mb-2 hidden">
                    <input type="file" name="thumbnail_file" accept="image/jpeg,image/png,image/webp"
                        onchange="previewPeopleImage(this, 'preview-thumb-create')"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 outline-none file:mr-2 file:px-2 file:py-1 file:rounded-md file:border-0 file:bg-slate-700 file:text-slate-200 file:text-xs">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Bannières</label>
                    <div id="new-covers-preview-create" class="grid grid-cols-3 gap-2 mb-2"></div>
                    <input type="file" name="cover_images_file[]" multiple accept="image/jpeg,image/png,image/webp"
                        onchange="previewPeopleMultiImages(this, 'new-covers-preview-create')"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 outline-none file:mr-2 file:px-2 file:py-1 file:rounded-md file:border-0 file:bg-slate-700 file:text-slate-200 file:text-xs">
                    <p class="text-[10px] text-slate-500 mt-1">Plusieurs images peuvent être sélectionnées en une fois.</p>
                </div>
                <div class="md:col-span-2 flex items-center gap-4">
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-600 bg-slate-700 text-orange-500">
                        Actif
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-600 bg-slate-700 text-orange-500">
                        En vedette
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="closePeopleModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">Annuler</button>
                <button type="submit"
                    class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-black font-semibold text-xs rounded-lg transition">Créer le peuple</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPeopleModal() { document.getElementById('modal-people-create').classList.remove('hidden'); }
function closePeopleModal() {
    document.getElementById('modal-people-create').classList.add('hidden');
    document.getElementById('form-people-create').reset();
    const c = document.getElementById('symboles-container-create');
    c.querySelectorAll('.symbole-row').forEach((row, i) => { if (i > 0) row.remove(); });
    const thumb = document.getElementById('preview-thumb-create');
    thumb.src = '';
    thumb.classList.add('hidden');
    document.getElementById('new-covers-preview-create').innerHTML = '';
}

// Aperçu local de l'image sélectionnée avant tout envoi au serveur
// (exigence « prévisualiser l'image avant son enregistrement »).
function previewPeopleImage(input, previewId) {
    const img = document.getElementById(previewId);
    const file = input.files && input.files[0];
    if (!file) {
        img.src = '';
        img.classList.add('hidden');
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        img.src = e.target.result;
        img.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function toggleRemoveImagePreview(checkbox, previewId) {
    const img = document.getElementById(previewId);
    img.classList.toggle('hidden', checkbox.checked);
}

// Aperçu local de plusieurs bannières sélectionnées en une fois (champ multiple)
// avant tout envoi au serveur.
function previewPeopleMultiImages(input, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    const files = input.files ? Array.from(input.files) : [];
    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-16 object-cover rounded-lg border border-slate-700';
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
function openEditPeopleModal(id) { document.getElementById('edit-people-' + id).classList.remove('hidden'); }
function closeEditPeopleModal(id) { document.getElementById('edit-people-' + id).classList.add('hidden'); }

function addSymboleRow(containerId) {
    const container = document.getElementById(containerId);
    const row = document.createElement('div');
    row.className = 'flex gap-2 items-center symbole-row';
    row.innerHTML = `
        <input type="text" name="symbole_label[]" placeholder="Label (ex: Couleur)"
            class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
        <input type="text" name="symbole_valeur[]" placeholder="Valeur (ex: Blanc)"
            class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
        <button type="button" onclick="this.closest('.symbole-row').remove()"
            class="w-8 h-8 shrink-0 rounded-lg bg-slate-700 hover:bg-red-900/40 text-slate-500 hover:text-red-400 flex items-center justify-center transition">
            <i class="fas fa-times text-xs"></i>
        </button>`;
    container.appendChild(row);
}
</script>
@endsection
