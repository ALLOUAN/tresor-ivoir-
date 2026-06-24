@extends('layouts.app')

@section('title', 'Cultures Ivoiriennes — Domaines')
@section('page-title', 'Domaines Culturels')

@section('header-actions')
<button onclick="document.getElementById('modal-domain-create').classList.remove('hidden')"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouveau domaine
</button>
@endsection

@section('content')

@include('admin.cultural.partials.subnav')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Domaines racines + sous-domaines --}}
<div class="space-y-4">
    @forelse($roots as $root)
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">

        {{-- Racine --}}
        <div class="flex items-center gap-4 px-5 py-4 border-b border-slate-800">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                style="background-color: {{ $root->color }}22; border: 1px solid {{ $root->color }}44">
                <i class="{{ $root->icon }}" style="color: {{ $root->color }}; font-size: 14px;"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold text-sm">{{ $root->name }}</p>
                <p class="text-slate-500 text-xs truncate">{{ $root->description }}</p>
            </div>
            <span class="shrink-0 px-2 py-0.5 bg-slate-800 text-slate-400 text-[11px] rounded-full">
                {{ $root->elements_count }} élément(s)
            </span>
            <div class="flex items-center gap-1.5 shrink-0">
                <button onclick="openEditDomainModal('root-{{ $root->id }}')"
                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                    <i class="fas fa-pen text-xs"></i>
                </button>
                <form method="POST" action="{{ route('admin.cultural.domains.destroy', $root) }}"
                    onsubmit="return confirm('Supprimer ce domaine et ses sous-domaines ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Formulaire édition racine --}}
        <div id="edit-root-{{ $root->id }}" class="hidden px-5 py-4 bg-slate-800/50 border-b border-slate-800">
            <form method="POST" action="{{ route('admin.cultural.domains.update', $root) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div><label class="text-xs text-slate-400 mb-1 block">Nom</label>
                        <input type="text" name="name" value="{{ $root->name }}" required
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"></div>
                    <div><label class="text-xs text-slate-400 mb-1 block">Icône FontAwesome</label>
                        <input type="text" name="icon" value="{{ $root->icon }}" placeholder="fas fa-music"
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"></div>
                    <div><label class="text-xs text-slate-400 mb-1 block">Couleur (hex)</label>
                        <input type="text" name="color" value="{{ $root->color }}" placeholder="#8B5CF6"
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"></div>
                    <div class="md:col-span-3"><label class="text-xs text-slate-400 mb-1 block">Description</label>
                        <input type="text" name="description" value="{{ $root->description }}"
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"></div>
                    <div class="md:col-span-3 flex justify-end gap-2">
                        <button type="button" onclick="closeEditDomainModal('root-{{ $root->id }}')"
                            class="px-3 py-1.5 bg-slate-700 text-slate-300 text-xs rounded-lg transition">Annuler</button>
                        <button type="submit"
                            class="px-4 py-1.5 bg-amber-500 text-black font-semibold text-xs rounded-lg transition">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Sous-domaines --}}
        @if($root->children->isNotEmpty())
        <div class="divide-y divide-slate-800">
            @foreach($root->children as $child)
            <div class="flex items-center gap-4 px-5 py-3 pl-12">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                    style="background-color: {{ $child->color }}22">
                    <i class="{{ $child->icon }}" style="color: {{ $child->color }}; font-size: 11px;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-slate-300 text-sm">{{ $child->name }}</p>
                </div>
                <span class="shrink-0 px-2 py-0.5 bg-slate-800/60 text-slate-500 text-[10px] rounded-full">
                    {{ $child->elements_count }}
                </span>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button onclick="openEditDomainModal('child-{{ $child->id }}')"
                        class="w-6 h-6 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                        <i class="fas fa-pen text-[10px]"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.cultural.domains.destroy', $child) }}"
                        onsubmit="return confirm('Supprimer « {{ addslashes($child->name) }} » ?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-6 h-6 rounded-md bg-slate-800 hover:bg-red-900/50 text-slate-600 hover:text-red-400 flex items-center justify-center transition">
                            <i class="fas fa-trash text-[10px]"></i>
                        </button>
                    </form>
                </div>
            </div>
            {{-- Formulaire édition sous-domaine --}}
            <div id="edit-child-{{ $child->id }}" class="hidden px-5 py-4 pl-12 bg-slate-800/30">
                <form method="POST" action="{{ route('admin.cultural.domains.update', $child) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div><label class="text-xs text-slate-400 mb-1 block">Nom</label>
                            <input type="text" name="name" value="{{ $child->name }}" required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"></div>
                        <div><label class="text-xs text-slate-400 mb-1 block">Icône</label>
                            <input type="text" name="icon" value="{{ $child->icon }}"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"></div>
                        <div><label class="text-xs text-slate-400 mb-1 block">Couleur</label>
                            <input type="text" name="color" value="{{ $child->color }}"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"></div>
                        <div class="md:col-span-3 flex justify-end gap-2">
                            <button type="button" onclick="closeEditDomainModal('child-{{ $child->id }}')"
                                class="px-3 py-1.5 bg-slate-700 text-slate-300 text-xs rounded-lg">Annuler</button>
                            <button type="submit"
                                class="px-4 py-1.5 bg-amber-500 text-black font-semibold text-xs rounded-lg">Enregistrer</button>
                        </div>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Ajouter sous-domaine --}}
        <div class="px-5 py-3 pl-12 bg-slate-800/20">
            <button onclick="document.getElementById('add-sub-{{ $root->id }}').classList.toggle('hidden')"
                class="text-xs text-slate-500 hover:text-amber-400 transition flex items-center gap-1.5">
                <i class="fas fa-plus text-[10px]"></i> Ajouter un sous-domaine
            </button>
            <form id="add-sub-{{ $root->id }}" method="POST" action="{{ route('admin.cultural.domains.store') }}" class="hidden mt-3">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $root->id }}">
                <div class="flex gap-2">
                    <input type="text" name="name" placeholder="Nom du sous-domaine…" required maxlength="100"
                        class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <input type="text" name="icon" placeholder="fas fa-…" maxlength="80"
                        class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <button type="submit"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-12 text-center text-slate-600">
        <i class="fas fa-layer-group text-4xl mb-3 block"></i>
        Aucun domaine culturel.
    </div>
    @endforelse
</div>

{{-- Modal création domaine racine --}}
<div id="modal-domain-create" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg">
        <div class="flex items-center justify-between p-5 border-b border-slate-800">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-layer-group text-amber-400"></i> Nouveau domaine racine
            </h3>
            <button onclick="document.getElementById('modal-domain-create').classList.add('hidden')"
                class="text-slate-500 hover:text-white transition"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.cultural.domains.store') }}" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required maxlength="100"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Icône FontAwesome</label>
                    <input type="text" name="icon" placeholder="fas fa-music"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Couleur (hex)</label>
                    <input type="text" name="color" placeholder="#8B5CF6"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none"></textarea>
                </div>
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Ordre</label>
                    <input type="number" name="sort_order" value="0" min="0"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-domain-create').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-800 text-slate-300 text-xs rounded-lg">Annuler</button>
                <button type="submit"
                    class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">Créer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditDomainModal(key) { document.getElementById('edit-' + key).classList.remove('hidden'); }
function closeEditDomainModal(key) { document.getElementById('edit-' + key).classList.add('hidden'); }
</script>
@endsection
