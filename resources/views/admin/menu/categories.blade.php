@extends('layouts.app')

@section('title', 'Catégories de menu')
@section('page-title', 'Catégories de menu — Restaurants')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.menu.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour aux plats
    </a>
    <button onclick="openMenuCatModal()"
        class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black font-semibold text-xs rounded-lg transition">
        <i class="fas fa-circle-plus"></i> Nouvelle catégorie
    </button>
</div>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($categories as $cat)
    <div class="bg-green-900 border border-slate-800 hover:border-slate-700 rounded-xl p-5 transition group">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 bg-orange-500/15 text-orange-300">
                    <i class="{{ $cat->icon ?: 'fas fa-tag' }}"></i>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">{{ $cat->name_fr }}</p>
                    <p class="text-slate-500 text-xs">{{ $cat->menu_items_count }} plat(s)</p>
                </div>
            </div>
            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium border
                {{ $cat->is_active ? 'bg-emerald-900/40 text-emerald-300 border-emerald-800' : 'bg-slate-800 text-slate-500 border-slate-700' }}">
                {{ $cat->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <p class="text-slate-500 text-xs">{{ $cat->name_en }}</p>
        <div class="flex items-center justify-end gap-1 pt-3 mt-3 border-t border-slate-800">
            <button onclick="openMenuCatModal({{ $cat->toJson() }})"
                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-orange-900/40 flex items-center justify-center text-slate-400 hover:text-orange-300 transition" title="Modifier">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <form method="POST" action="{{ route('admin.menu.categories.destroy', $cat) }}"
                onsubmit="return confirm('Supprimer « {{ addslashes($cat->name_fr) }} » ?')" class="inline">
                @csrf @method('DELETE')
                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-500">
        <i class="fas fa-tags text-3xl mb-3 block text-slate-700"></i>
        Aucune catégorie. Créez-en une !
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="menuCatModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-green-950/70 backdrop-blur-sm" onclick="closeMenuCatModal()"></div>
    <div class="relative bg-green-900 border border-slate-700 rounded-2xl p-6 w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
        <h3 id="menuCatModalTitle" class="text-white font-semibold mb-5">Nouvelle catégorie</h3>
        <form id="menuCatForm" method="POST" class="space-y-4">
            @csrf
            <div id="menuCatMethodField"></div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nom (FR) <span class="text-red-400">*</span></label>
                    <input type="text" name="name_fr" id="mcat_name_fr" required maxlength="150"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nom (EN) <span class="text-red-400">*</span></label>
                    <input type="text" name="name_en" id="mcat_name_en" required maxlength="150"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Icône FontAwesome</label>
                <input type="text" name="icon" id="mcat_icon" maxlength="100" placeholder="fas fa-utensils"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Ordre</label>
                    <input type="number" name="sort_order" id="mcat_sort_order" min="0" value="0"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300 pb-2">
                    <input type="checkbox" name="is_active" id="mcat_is_active" value="1" checked
                        class="rounded border-slate-600 bg-slate-800 text-orange-500">
                    Active
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeMenuCatModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold rounded-lg transition">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const menuCatStoreUrl   = "{{ route('admin.menu.categories.store') }}";
const menuCatUpdateBase = "{{ url('admin/carte/categories') }}";

function openMenuCatModal(cat = null) {
    const form  = document.getElementById('menuCatForm');
    const title = document.getElementById('menuCatModalTitle');
    const mf    = document.getElementById('menuCatMethodField');

    if (cat) {
        title.textContent = 'Modifier la catégorie';
        form.action = `${menuCatUpdateBase}/${cat.id}`;
        mf.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('mcat_name_fr').value    = cat.name_fr || '';
        document.getElementById('mcat_name_en').value    = cat.name_en || '';
        document.getElementById('mcat_icon').value       = cat.icon || '';
        document.getElementById('mcat_sort_order').value = cat.sort_order || 0;
        document.getElementById('mcat_is_active').checked = cat.is_active == 1;
    } else {
        title.textContent = 'Nouvelle catégorie';
        form.action = menuCatStoreUrl;
        mf.innerHTML = '';
        form.reset();
        document.getElementById('mcat_is_active').checked = true;
    }
    const modal = document.getElementById('menuCatModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeMenuCatModal() {
    const modal = document.getElementById('menuCatModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenuCatModal(); });
</script>
@endpush

@endsection
