@extends('layouts.app')

@section('title', 'Cultures Ivoiriennes — Peuples')
@section('page-title', 'Peuples & Ethnies')

@section('header-actions')
<button onclick="openPeopleModal()"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
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
        ['fas fa-star',         'text-amber-400',   $counts['featured'], 'En vedette'],
    ] as [$icon, $color, $val, $lbl])
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <i class="{{ $icon }} {{ $color }} text-sm mb-2 block"></i>
        <p class="text-2xl font-bold text-white">{{ $val }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $lbl }}</p>
    </div>
    @endforeach
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('admin.cultural.peoples.index') }}" class="mb-5 flex gap-2">
    <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un peuple…"
        class="flex-1 bg-slate-900 border border-slate-800 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition placeholder-slate-600">
    <select name="zone"
        class="bg-slate-900 border border-slate-800 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none">
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
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
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
                                <span class="text-amber-400 text-[10px]"><i class="fas fa-star mr-0.5"></i>En vedette</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($people->zone_geographique)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[11px]">
                            <i class="fas fa-map-location-dot text-amber-400/60 text-[9px]"></i>
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
                                        {{ $people->is_featured ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-800 text-slate-600 hover:text-amber-400' }}">
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
                                        class="w-full bg-slate-900 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Zone géographique</label>
                                    <select name="zone_geographique"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                        <option value="">—</option>
                                        @foreach(['Nord','Sud','Est','Ouest','Centre'] as $z)
                                        <option value="{{ $z }}" {{ $people->zone_geographique === $z ? 'selected' : '' }}>{{ $z }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Famille linguistique</label>
                                    <input type="text" name="famille_linguistique" value="{{ $people->famille_linguistique }}" maxlength="100"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Langue principale</label>
                                    <input type="text" name="langue_principale" value="{{ $people->langue_principale }}" maxlength="100"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Population estimée</label>
                                    <input type="number" name="population_estimee" value="{{ $people->population_estimee }}" min="0"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 mb-1 block">Capitale culturelle</label>
                                    <input type="text" name="capitale_culturelle" value="{{ $people->capitale_culturelle }}" maxlength="100"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="text-xs text-slate-400 mb-1 block">Description</label>
                                    <textarea name="description" rows="3"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ $people->description }}</textarea>
                                </div>
                                <div class="md:col-span-3 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ $people->is_active ? 'checked' : '' }}
                                                class="rounded border-slate-600 bg-slate-800 text-amber-500">
                                            Actif
                                        </label>
                                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                                            <input type="checkbox" name="is_featured" value="1" {{ $people->is_featured ? 'checked' : '' }}
                                                class="rounded border-slate-600 bg-slate-800 text-amber-500">
                                            En vedette
                                        </label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="closeEditPeopleModal({{ $people->id }})"
                                            class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs rounded-lg transition">
                                            Annuler
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
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
<div id="modal-people-create" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-5 border-b border-slate-800">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-people-group text-amber-400"></i> Nouveau peuple
            </h3>
            <button onclick="closePeopleModal()" class="text-slate-500 hover:text-white transition"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.cultural.peoples.store') }}" enctype="multipart/form-data" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required maxlength="100"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
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
                    <label class="text-xs text-slate-400 mb-1 block">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Image bannière (URL)</label>
                    <input type="url" name="cover_image" maxlength="500"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div class="md:col-span-2 flex items-center gap-4">
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-600 bg-slate-700 text-amber-500">
                        Actif
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-600 bg-slate-700 text-amber-500">
                        En vedette
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="closePeopleModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">Annuler</button>
                <button type="submit"
                    class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">Créer le peuple</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPeopleModal() { document.getElementById('modal-people-create').classList.remove('hidden'); }
function closePeopleModal() { document.getElementById('modal-people-create').classList.add('hidden'); }
function openEditPeopleModal(id) { document.getElementById('edit-people-' + id).classList.remove('hidden'); }
function closeEditPeopleModal(id) { document.getElementById('edit-people-' + id).classList.add('hidden'); }
</script>
@endsection
