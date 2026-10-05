@extends('layouts.app')

@section('title', 'Catégories de prestataires')
@section('page-title', 'Catégories de prestataires')

@section('header-actions')
<button onclick="openProvCatModal()"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvelle catégorie
</button>
@endsection

@section('content')

@include('admin.providers.partials.subnav')

{{-- Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($categories as $cat)
    <div class="bg-green-900 border border-slate-800 hover:border-slate-700 rounded-xl p-5 transition group">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
                    style="{{ $cat->color_hex ? 'background:' . $cat->color_hex . '22; color:' . $cat->color_hex : 'background:#334155; color:#94a3b8' }}">
                    <i class="fas {{ $cat->icon ?: 'fa-tag' }}"></i>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">{{ $cat->name_fr }}</p>
                    <p class="text-slate-500 text-xs">
                        {{ $cat->parent ? '↳ ' . $cat->parent->name_fr . ' · ' : '' }}{{ $cat->providers_count }} prestataire(s)
                    </p>
                </div>
            </div>
            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium border
                {{ $cat->is_active ? 'bg-emerald-900/40 text-emerald-300 border-emerald-800' : 'bg-slate-800 text-slate-500 border-slate-700' }}">
                {{ $cat->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <p class="text-slate-500 text-xs">{{ $cat->name_en }}</p>
        @if($cat->description_fr)
        <p class="text-slate-500 text-xs line-clamp-2 mt-2">{{ $cat->description_fr }}</p>
        @endif
        <div class="flex items-center justify-end gap-1 pt-3 mt-3 border-t border-slate-800">
            <button onclick="openProvCatModal({{ $cat->toJson() }})"
                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-orange-900/40 flex items-center justify-center text-slate-400 hover:text-orange-300 transition" title="Modifier">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <form method="POST" action="{{ route('admin.providers.categories.destroy', $cat) }}"
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
<div id="provCatModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-green-950/70 backdrop-blur-sm" onclick="closeProvCatModal()"></div>
    <div class="relative bg-green-900 border border-slate-700 rounded-2xl p-6 w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
        <h3 id="provCatModalTitle" class="text-white font-semibold mb-5">Nouvelle catégorie</h3>
        <form id="provCatForm" method="POST" class="space-y-4">
            @csrf
            <div id="provCatMethodField"></div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nom (FR) <span class="text-red-400">*</span></label>
                    <input type="text" name="name_fr" id="pcat_name_fr" required maxlength="150"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nom (EN) <span class="text-red-400">*</span></label>
                    <input type="text" name="name_en" id="pcat_name_en" required maxlength="150"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Icône FontAwesome</label>
                    <input type="text" name="icon" id="pcat_icon" maxlength="100" placeholder="fa-hotel"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Couleur</label>
                    <input type="color" name="color_hex" id="pcat_color_hex" value="#f2790f"
                        class="w-full h-9 bg-slate-800 border border-slate-700 rounded-lg px-2 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Catégorie parente</label>
                <select name="parent_id" id="pcat_parent_id"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <option value="">Aucune (catégorie racine)</option>
                    @foreach($parentOptions as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name_fr }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Description (FR)</label>
                <textarea name="description_fr" id="pcat_description_fr" rows="2"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none"></textarea>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Description (EN)</label>
                <textarea name="description_en" id="pcat_description_en" rows="2"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Ordre</label>
                    <input type="number" name="sort_order" id="pcat_sort_order" min="0" value="0"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300 pb-2">
                    <input type="checkbox" name="is_active" id="pcat_is_active" value="1" checked
                        class="rounded border-slate-600 bg-slate-800 text-orange-500">
                    Active
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeProvCatModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold rounded-lg transition">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const provCatStoreUrl   = "{{ route('admin.providers.categories.store') }}";
const provCatUpdateBase = "{{ url('admin/prestataires/categories') }}";

function openProvCatModal(cat = null) {
    const form  = document.getElementById('provCatForm');
    const title = document.getElementById('provCatModalTitle');
    const mf    = document.getElementById('provCatMethodField');

    if (cat) {
        title.textContent = 'Modifier la catégorie';
        form.action = `${provCatUpdateBase}/${cat.id}`;
        mf.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('pcat_name_fr').value        = cat.name_fr || '';
        document.getElementById('pcat_name_en').value         = cat.name_en || '';
        document.getElementById('pcat_icon').value            = cat.icon || '';
        document.getElementById('pcat_color_hex').value       = cat.color_hex || '#f2790f';
        document.getElementById('pcat_parent_id').value       = cat.parent_id || '';
        document.getElementById('pcat_description_fr').value  = cat.description_fr || '';
        document.getElementById('pcat_description_en').value  = cat.description_en || '';
        document.getElementById('pcat_sort_order').value      = cat.sort_order || 0;
        document.getElementById('pcat_is_active').checked     = cat.is_active == 1;

        // Une catégorie ne peut pas être sa propre parente : on masque son option.
        document.querySelectorAll('#pcat_parent_id option').forEach(opt => {
            opt.hidden = (opt.value !== '' && parseInt(opt.value) === cat.id);
        });
    } else {
        title.textContent = 'Nouvelle catégorie';
        form.action = provCatStoreUrl;
        mf.innerHTML = '';
        form.reset();
        document.getElementById('pcat_is_active').checked = true;
        document.getElementById('pcat_color_hex').value = '#f2790f';
        document.querySelectorAll('#pcat_parent_id option').forEach(opt => opt.hidden = false);
    }
    const modal = document.getElementById('provCatModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProvCatModal() {
    const modal = document.getElementById('provCatModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeProvCatModal(); });
</script>
@endpush

@endsection
