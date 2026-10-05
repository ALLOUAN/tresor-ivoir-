@extends('layouts.app')

@section('title', 'Bulles interactives')
@section('page-title', 'Gestion — Bulles interactives')

@section('header-actions')
<a href="{{ route('home') }}" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-eye"></i> Voir la page d'accueil
</a>
@endsection

@section('content')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-green-700 via-green-600 to-green-600">
        <div>
            <h2 class="text-white font-semibold text-lg tracking-tight">Bulles interactives — page d'accueil</h2>
            <p class="text-green-100/80 text-xs mt-0.5">Chaque bulle est positionnée sur la page d'accueil et ouvre une galerie d'images au clic.</p>
        </div>
        <button type="button" onclick="openCreateModal()"
                class="inline-flex items-center justify-center gap-2 shrink-0 bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus"></i> Ajouter une bulle
        </button>
    </div>

    <div class="p-5 space-y-3">
        @forelse($bubbles as $bubble)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-slate-800 bg-slate-800/20 hover:border-slate-700/80 transition">
            <div class="shrink-0 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full text-white text-lg shrink-0"
                      style="background: {{ $bubble->color_hex ?: '#f2790f' }};">
                    <i class="{{ $bubble->icon ?: 'fas fa-image' }}"></i>
                </span>
                @if($bubble->images->isNotEmpty())
                <div class="flex -space-x-2">
                    @foreach($bubble->images->take(3) as $img)
                        <img src="{{ $img->image_url }}" class="w-8 h-8 rounded-full object-cover border-2 border-green-900">
                    @endforeach
                </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold truncate">{{ $bubble->title ?: 'Sans titre' }}</p>
                @if($bubble->description)
                    <p class="text-slate-500 text-xs mt-0.5 line-clamp-1">{{ $bubble->description }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bubble->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-600/30 text-slate-300 border border-slate-600/40' }}">
                        {{ $bubble->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/40">
                        Position : {{ number_format($bubble->position_top, 0) }}% / {{ number_format($bubble->position_left, 0) }}%
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/40">
                        {{ $bubble->images->count() }} image{{ $bubble->images->count() > 1 ? 's' : '' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/40">
                        Ordre : {{ $bubble->display_order }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-end sm:justify-center gap-1 shrink-0 border-t border-slate-800/80 sm:border-0 pt-3 sm:pt-0">
                <button type="button"
                        onclick="openImagesModal(this)"
                        data-id="{{ $bubble->id }}"
                        data-title="{{ e($bubble->title ?? 'Sans titre') }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition"
                        title="Gérer les images">
                    <i class="fas fa-images"></i>
                </button>
                <button type="button"
                        onclick="openEditModal(this)"
                        data-id="{{ $bubble->id }}"
                        data-title="{{ e($bubble->title ?? '') }}"
                        data-description="{{ e($bubble->description ?? '') }}"
                        data-position-top="{{ $bubble->position_top }}"
                        data-position-left="{{ $bubble->position_left }}"
                        data-size="{{ $bubble->size }}"
                        data-color-hex="{{ $bubble->color_hex ?: '#f2790f' }}"
                        data-icon="{{ e($bubble->icon ?? '') }}"
                        data-link-url="{{ e($bubble->link_url ?? '') }}"
                        data-link-label="{{ e($bubble->link_label ?? '') }}"
                        data-display-order="{{ $bubble->display_order }}"
                        data-is-active="{{ $bubble->is_active ? '1' : '0' }}"
                        data-pages="{{ e(json_encode($bubble->pages ?? [])) }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition"
                        title="Modifier">
                    <i class="fas fa-pen"></i>
                </button>
                <form method="POST" action="{{ route('admin.homepage-bubbles.toggle', $bubble) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white transition"
                            title="{{ $bubble->is_active ? 'Désactiver' : 'Activer' }}">
                        <i class="fas {{ $bubble->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.homepage-bubbles.destroy', $bubble) }}" class="inline" onsubmit="return confirm('Supprimer cette bulle et toutes ses images ?');">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-red-700/90 hover:bg-red-600 text-white transition"
                            title="Supprimer">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-14 text-slate-500 border border-dashed border-slate-700 rounded-xl">
            <i class="fas fa-circle-dot text-3xl mb-3 text-slate-600"></i>
            <p>Aucune bulle pour le moment.</p>
            <button type="button" onclick="openCreateModal()" class="mt-4 text-orange-400 hover:text-orange-300 text-sm font-medium">Ajouter la première bulle</button>
        </div>
        @endforelse
    </div>

    @if($bubbles->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $bubbles->links() }}
        </div>
    @endif
</div>

{{-- ── Modal création ─────────────────────────────────────────────── --}}
<div id="create-bubble-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeCreateModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center overflow-y-auto">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col my-auto">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Ajouter une bulle</h2>
                <button type="button" onclick="closeCreateModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.homepage-bubbles.store') }}" enctype="multipart/form-data" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @include('admin.homepage-bubbles.partials.bubble-form-fields')
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal édition ──────────────────────────────────────────────── --}}
<div id="edit-bubble-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeEditModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center overflow-y-auto">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col my-auto">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Modifier la bulle</h2>
                <button type="button" onclick="closeEditModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" id="edit-bubble-form" action="" enctype="multipart/form-data" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @method('PUT')
                @include('admin.homepage-bubbles.partials.bubble-form-fields', ['isEdit' => true])
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal gestion des images ───────────────────────────────────── --}}
<div id="images-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeImagesModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center overflow-y-auto">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col my-auto">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Images — <span id="images-modal-title"></span></h2>
                <button type="button" onclick="closeImagesModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <div class="p-5 overflow-y-auto space-y-5">
                <div id="images-modal-grid" class="grid grid-cols-3 gap-3"></div>

                <form method="POST" id="images-modal-upload-form" action="" enctype="multipart/form-data" class="border-t border-slate-800 pt-4">
                    @csrf
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-4 cursor-pointer transition group">
                        <i class="fas fa-cloud-arrow-up text-xl text-slate-600 group-hover:text-orange-400/70 mb-1.5 transition"></i>
                        <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Ajouter une image (JPG, PNG, WebP — 5 Mo max)</span>
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.requestSubmit()">
                    </label>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openCreateModal() { document.getElementById('create-bubble-modal').classList.remove('hidden'); }
    function closeCreateModal() { document.getElementById('create-bubble-modal').classList.add('hidden'); }
    function closeEditModal() { document.getElementById('edit-bubble-modal').classList.add('hidden'); }

    function openEditModal(button) {
        const form = document.getElementById('edit-bubble-form');
        const base = "{{ route('admin.homepage-bubbles.update', ['bubble' => '__ID__']) }}";
        form.action = base.replace('__ID__', button.dataset.id);

        document.getElementById('edit_title').value = button.dataset.title || '';
        document.getElementById('edit_description').value = button.dataset.description || '';
        document.getElementById('edit_position_top').value = button.dataset.positionTop || '50';
        document.getElementById('edit_position_left').value = button.dataset.positionLeft || '50';
        document.getElementById('edit_size').value = button.dataset.size || 'md';
        document.getElementById('edit_color_hex').value = button.dataset.colorHex || '#f2790f';
        document.getElementById('edit_icon').value = button.dataset.icon || '';
        document.getElementById('edit_link_url').value = button.dataset.linkUrl || '';
        document.getElementById('edit_link_label').value = button.dataset.linkLabel || '';
        document.getElementById('edit_display_order').value = button.dataset.displayOrder || '0';
        document.getElementById('edit_is_active').checked = button.dataset.isActive === '1';

        let selectedPages = [];
        try { selectedPages = JSON.parse(button.dataset.pages || '[]'); } catch (e) { selectedPages = []; }
        document.querySelectorAll('#edit_pages_list input[type="checkbox"]').forEach(function (box) {
            box.checked = selectedPages.indexOf(box.dataset.pageKey) !== -1;
        });

        if (window.edit_syncBubblePicker) window.edit_syncBubblePicker();

        document.getElementById('edit-bubble-modal').classList.remove('hidden');
    }

    const bubbleImagesData = @json($bubbles->keyBy('id')->map(fn($b) => $b->images->map(fn($i) => ['id' => $i->id, 'url' => $i->image_url])));
    const destroyImageBase = "{{ route('admin.homepage-bubbles.images.destroy', ['image' => '__ID__']) }}";
    const storeImageBase = "{{ route('admin.homepage-bubbles.images.store', ['bubble' => '__ID__']) }}";

    function closeImagesModal() { document.getElementById('images-modal').classList.add('hidden'); }

    function openImagesModal(button) {
        const bubbleId = button.dataset.id;
        document.getElementById('images-modal-title').textContent = button.dataset.title || '';
        document.getElementById('images-modal-upload-form').action = storeImageBase.replace('__ID__', bubbleId);

        const grid = document.getElementById('images-modal-grid');
        grid.innerHTML = '';
        const images = bubbleImagesData[bubbleId] || [];
        images.forEach((img) => {
            const cell = document.createElement('div');
            cell.className = 'relative group';
            cell.innerHTML = `
                <img src="${img.url}" class="w-full h-20 object-cover rounded-lg border border-slate-700">
                <form method="POST" action="${destroyImageBase.replace('__ID__', img.id)}" onsubmit="return confirm('Supprimer cette image ?');" class="absolute top-1 right-1">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="w-6 h-6 rounded-full bg-red-700/90 hover:bg-red-600 text-white text-xs flex items-center justify-center">
                        <i class="fas fa-xmark"></i>
                    </button>
                </form>
            `;
            grid.appendChild(cell);
        });
        if (images.length === 0) {
            grid.innerHTML = '<p class="col-span-3 text-slate-500 text-xs text-center py-4">Aucune image pour cette bulle.</p>';
        }

        document.getElementById('images-modal').classList.remove('hidden');
    }
</script>
@endpush
