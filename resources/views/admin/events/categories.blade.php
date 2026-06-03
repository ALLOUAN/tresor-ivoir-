@extends('layouts.app')

@section('title', 'Catégories d\'événements')
@section('page-title', 'Catégories d\'événements')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.events.index') }}"
        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-arrow-left"></i> Retour aux événements
    </a>
    <button onclick="openCatModal()"
        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
        <i class="fas fa-circle-plus"></i> Nouvelle catégorie
    </button>
</div>
@endsection

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Grille des catégories --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($categories as $cat)
    <div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-xl p-5 transition">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
                    style="{{ $cat->color_hex ? 'background:' . $cat->color_hex . '22; color:' . $cat->color_hex : 'background:#1e293b; color:#94a3b8' }}">
                    <i class="{{ $cat->icon ?: 'fas fa-calendar' }}"></i>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">{{ $cat->name_fr }}</p>
                    @if($cat->name_en)
                    <p class="text-slate-500 text-xs italic">{{ $cat->name_en }}</p>
                    @endif
                </div>
            </div>
            <span class="text-slate-500 text-xs bg-slate-800 px-2 py-0.5 rounded-full">
                {{ $cat->events_count }} événement(s)
            </span>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-600 mb-4">
            <span class="font-mono bg-slate-800 px-2 py-0.5 rounded">{{ $cat->slug }}</span>
            @if($cat->color_hex)
            <span class="inline-flex items-center gap-1">
                <span class="w-3 h-3 rounded-full inline-block border border-slate-700"
                    style="background: {{ $cat->color_hex }}"></span>
                {{ $cat->color_hex }}
            </span>
            @endif
            <span class="ml-auto">Ordre : {{ $cat->sort_order }}</span>
        </div>

        <div class="flex items-center justify-end gap-1 pt-3 border-t border-slate-800">
            <button onclick="openCatModal({{ $cat->toJson() }})"
                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center text-slate-400 hover:text-amber-300 transition" title="Modifier">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <form method="POST" action="{{ route('admin.events.categories.destroy', $cat) }}"
                onsubmit="return confirm('Supprimer « {{ addslashes($cat->name_fr) }} » ? Les événements liés ne seront pas supprimés.')" class="inline">
                @csrf @method('DELETE')
                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-500 bg-slate-900 border border-slate-800 rounded-xl">
        <i class="fas fa-folder-open text-4xl mb-3 block text-slate-700"></i>
        Aucune catégorie d'événement. Créez-en une !
    </div>
    @endforelse
</div>

{{-- Modal Créer / Modifier --}}
<div id="catModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeCatModal()"></div>
    <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
        <h3 id="catModalTitle" class="text-white font-semibold mb-5">Nouvelle catégorie</h3>

        <form id="catForm" method="POST" class="space-y-4">
            @csrf
            <div id="catMethodField"></div>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Nom (français) <span class="text-red-400">*</span></label>
                    <input type="text" name="name_fr" id="cat_name_fr" required maxlength="150"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                </div>

                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Nom (anglais)</label>
                    <input type="text" name="name_en" id="cat_name_en" maxlength="150"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Icône FontAwesome</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="icon" id="cat_icon" maxlength="80" placeholder="fas fa-calendar"
                            class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none"
                            oninput="document.getElementById('cat_icon_preview').className = this.value || 'fas fa-calendar'">
                        <div class="w-9 h-9 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
                            <i id="cat_icon_preview" class="fas fa-calendar text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Couleur</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color_hex" id="cat_color_hex" value="#f59e0b"
                            class="w-9 h-9 bg-slate-800 border border-slate-700 rounded-lg px-1 cursor-pointer shrink-0">
                        <input type="text" id="cat_color_text" maxlength="7" placeholder="#f59e0b"
                            class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none font-mono"
                            oninput="document.getElementById('cat_color_hex').value = this.value">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
                    <input type="number" name="sort_order" id="cat_sort_order" min="0" value="0"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeCatModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold rounded-lg transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const catStoreUrl   = "{{ route('admin.events.categories.store') }}";
const catUpdateBase = "{{ url('admin/evenements/categories') }}";

function openCatModal(cat = null) {
    const form  = document.getElementById('catForm');
    const title = document.getElementById('catModalTitle');
    const mf    = document.getElementById('catMethodField');

    if (cat) {
        title.textContent = 'Modifier la catégorie';
        form.action = `${catUpdateBase}/${cat.id}`;
        mf.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('cat_name_fr').value   = cat.name_fr || '';
        document.getElementById('cat_name_en').value   = cat.name_en || '';
        document.getElementById('cat_icon').value      = cat.icon || '';
        document.getElementById('cat_icon_preview').className = cat.icon || 'fas fa-calendar';
        const color = cat.color_hex || '#f59e0b';
        document.getElementById('cat_color_hex').value  = color;
        document.getElementById('cat_color_text').value = color;
        document.getElementById('cat_sort_order').value = cat.sort_order || 0;
    } else {
        title.textContent = 'Nouvelle catégorie';
        form.action = catStoreUrl;
        mf.innerHTML = '';
        form.reset();
        document.getElementById('cat_icon_preview').className = 'fas fa-calendar';
        document.getElementById('cat_color_hex').value  = '#f59e0b';
        document.getElementById('cat_color_text').value = '#f59e0b';
        document.getElementById('cat_sort_order').value = 0;
    }

    // Sync color picker ↔ text input
    document.getElementById('cat_color_hex').addEventListener('input', function () {
        document.getElementById('cat_color_text').value = this.value;
    });

    const modal = document.getElementById('catModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCatModal() {
    const modal = document.getElementById('catModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCatModal(); });
</script>
@endpush

@endsection
