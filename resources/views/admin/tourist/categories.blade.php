@extends('layouts.app')

@section('title', 'Catégories Touristiques')
@section('page-title', 'Catégories Touristiques')

@section('header-actions')
<button onclick="openCatModal()"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvelle catégorie
</button>
@endsection

@section('content')

@include('admin.tourist.partials.subnav')

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($categories as $cat)
    <div class="bg-green-900 border border-slate-800 hover:border-slate-700 rounded-xl overflow-hidden transition group">
        @if($cat->hero_image_url)
            <img src="{{ $cat->hero_image_url }}" alt="" class="w-full h-24 object-cover">
        @else
            <div class="w-full h-24 bg-slate-800/60 flex items-center justify-center text-slate-700">
                <i class="fas fa-image text-xl"></i>
            </div>
        @endif
        <div class="p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 overflow-hidden"
                    style="{{ $cat->color ? 'background:' . $cat->color . '22; color:' . $cat->color : 'background:#334155; color:#94a3b8' }}">
                    @if($cat->icon_image_url)
                        <img src="{{ $cat->icon_image_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <i class="{{ $cat->icon ?: 'fas fa-tag' }}"></i>
                    @endif
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">{{ $cat->name }}</p>
                    <p class="text-slate-500 text-xs">{{ $cat->sites_count }} site(s)</p>
                </div>
            </div>
            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium border
                {{ $cat->is_active ? 'bg-emerald-900/40 text-emerald-300 border-emerald-800' : 'bg-slate-800 text-slate-500 border-slate-700' }}">
                {{ $cat->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        @if($cat->description)
        <p class="text-slate-500 text-xs line-clamp-2 mb-3">{{ $cat->description }}</p>
        @endif
        <div class="flex items-center justify-end gap-1 pt-2 border-t border-slate-800">
            <button onclick="openCatModal({{ $cat->toJson() }})"
                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-orange-900/40 flex items-center justify-center text-slate-400 hover:text-orange-300 transition" title="Modifier">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <form method="POST" action="{{ route('admin.tourist.categories.destroy', $cat) }}"
                onsubmit="return confirm('Supprimer « {{ addslashes($cat->name) }} » ?')" class="inline">
                @csrf @method('DELETE')
                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </form>
        </div>
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
<div id="catModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-green-950/70 backdrop-blur-sm" onclick="closeCatModal()"></div>
    <div class="relative bg-green-900 border border-slate-700 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
        <h3 id="catModalTitle" class="text-white font-semibold mb-5">Nouvelle catégorie</h3>
        <form id="catForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div id="catMethodField"></div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Nom <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="cat_name" required maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-2">Image hero (bannière de la page catégorie)</label>
                <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-3 cursor-pointer transition group">
                    <i class="fas fa-cloud-arrow-up text-lg text-slate-600 group-hover:text-orange-400/70 mb-1 transition"></i>
                    <span class="text-slate-500 text-[11px] group-hover:text-slate-300 transition">JPG, PNG, WebP — 5 Mo max</span>
                    <input type="file" name="hero_image_file" id="cat_hero_image_file" accept="image/jpeg,image/png,image/webp" class="hidden"
                           onchange="const r=new FileReader();r.onload=e=>{document.getElementById('cat_hero_preview').src=e.target.result;document.getElementById('cat_hero_preview').classList.remove('hidden');};r.readAsDataURL(this.files[0])">
                </label>
                <img id="cat_hero_preview" src="" class="mt-2 w-full h-24 object-cover rounded-lg border border-slate-700 hidden">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Icône FontAwesome</label>
                    <input type="text" name="icon" id="cat_icon" maxlength="80" placeholder="fas fa-mountain"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Couleur</label>
                    <input type="color" name="color" id="cat_color" value="#f2790f"
                        class="w-full h-9 bg-slate-800 border border-slate-700 rounded-lg px-2 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-2">Icône en image <span class="text-slate-600">(remplace l'icône FontAwesome si définie)</span></label>
                <div class="flex items-center gap-3">
                    <img id="cat_icon_preview" src="" class="w-12 h-12 object-cover rounded-lg border border-slate-700 hidden shrink-0">
                    <label class="flex-1 flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-3 cursor-pointer transition group">
                        <i class="fas fa-cloud-arrow-up text-lg text-slate-600 group-hover:text-orange-400/70 mb-1 transition"></i>
                        <span class="text-slate-500 text-[11px] group-hover:text-slate-300 transition">JPG, PNG, WebP — 5 Mo max</span>
                        <input type="file" name="icon_image_file" id="cat_icon_image_file" accept="image/jpeg,image/png,image/webp" class="hidden"
                               onchange="const r=new FileReader();r.onload=e=>{document.getElementById('cat_icon_preview').src=e.target.result;document.getElementById('cat_icon_preview').classList.remove('hidden');};r.readAsDataURL(this.files[0])">
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Description</label>
                <textarea name="description" id="cat_description" rows="2"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Ordre</label>
                    <input type="number" name="sort_order" id="cat_sort_order" min="0" value="0"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300 pb-2">
                    <input type="checkbox" name="is_active" id="cat_is_active" value="1" checked
                        class="rounded border-slate-600 bg-slate-800 text-orange-500">
                    Active
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeCatModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold rounded-lg transition">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const catStoreUrl   = "{{ route('admin.tourist.categories.store') }}";
const catUpdateBase = "{{ url('admin/tourisme/categories') }}";

function openCatModal(cat = null) {
    const form  = document.getElementById('catForm');
    const title = document.getElementById('catModalTitle');
    const mf    = document.getElementById('catMethodField');

    if (cat) {
        title.textContent = 'Modifier la catégorie';
        form.action = `${catUpdateBase}/${cat.id}`;
        mf.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('cat_name').value        = cat.name || '';
        document.getElementById('cat_icon').value        = cat.icon || '';
        document.getElementById('cat_color').value       = cat.color || '#f2790f';
        document.getElementById('cat_description').value = cat.description || '';
        document.getElementById('cat_sort_order').value  = cat.sort_order || 0;
        document.getElementById('cat_is_active').checked = cat.is_active == 1;
        const preview = document.getElementById('cat_hero_preview');
        if (cat.hero_image_url) {
            preview.src = cat.hero_image_url;
            preview.classList.remove('hidden');
        } else {
            preview.src = '';
            preview.classList.add('hidden');
        }
        const iconPreview = document.getElementById('cat_icon_preview');
        if (cat.icon_image_url) {
            iconPreview.src = cat.icon_image_url;
            iconPreview.classList.remove('hidden');
        } else {
            iconPreview.src = '';
            iconPreview.classList.add('hidden');
        }
    } else {
        title.textContent = 'Nouvelle catégorie';
        form.action = catStoreUrl;
        mf.innerHTML = '';
        form.reset();
        document.getElementById('cat_is_active').checked = true;
        document.getElementById('cat_color').value = '#f2790f';
        const preview = document.getElementById('cat_hero_preview');
        preview.src = '';
        preview.classList.add('hidden');
        const iconPreview = document.getElementById('cat_icon_preview');
        iconPreview.src = '';
        iconPreview.classList.add('hidden');
    }
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
