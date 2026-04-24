@extends('layouts.app')

@section('title', 'Apparence')
@section('page-title', 'Gestion de l\'apparence')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'appearance'])

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
        <p class="font-semibold text-rose-200 mb-2 flex items-center gap-2">
            <i class="fas fa-circle-exclamation"></i> Le slide n’a pas pu être enregistré
        </p>
        <ul class="list-disc list-inside space-y-1 text-rose-100/95">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div id="appearance-flash" class="hidden"
     data-has-errors="{{ $errors->any() ? '1' : '0' }}"
     data-form-context="{{ e(old('_form_context', '')) }}"
     data-edit-slide-id="{{ e((string) old('edit_slide_id', '')) }}"></div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-violet-700 via-violet-600 to-indigo-600">
        <div>
            <h2 class="text-white font-semibold text-lg tracking-tight">Gestion des Slides (Images Responsives)</h2>
            <p class="text-violet-100/80 text-xs mt-0.5">Titre, visibilité, ordre et trois visuels par breakpoint.</p>
        </div>
        <button type="button" onclick="openCreateSlideModal()"
                class="inline-flex items-center justify-center gap-2 shrink-0 bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus"></i> Ajouter un slide
        </button>
    </div>

    <div class="px-5 py-4 border-b border-slate-800">
        <div class="rounded-lg border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm text-sky-100">
            <p class="font-medium text-sky-200 mb-2 flex items-center gap-2">
                <i class="fas fa-circle-info text-sky-300"></i> Dimensions requises des images
            </p>
            <ul class="list-disc list-inside space-y-0.5 text-sky-100/90 text-xs sm:text-sm">
                <li><span class="font-medium text-white">Desktop :</span> cible 1920×800 — accepté entre env. 1600×600 et 4096×2000&nbsp;px</li>
                <li><span class="font-medium text-white">Tablette :</span> cible 1024×600 — min. env. 900×500&nbsp;px</li>
                <li><span class="font-medium text-white">Mobile :</span> cible 768×500 — min. env. 640×400&nbsp;px</li>
            </ul>
        </div>
    </div>

    <div class="p-5 space-y-3">
        @forelse($slides as $slide)
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-slate-800 bg-slate-800/20 hover:border-slate-700/80 transition">
                <div class="hidden sm:flex items-center justify-center w-8 text-slate-600 shrink-0 cursor-grab active:cursor-grabbing" title="Réordonnancement : modifiez l’ordre dans le formulaire d’édition">
                    <i class="fas fa-grip-vertical text-lg"></i>
                </div>

                <div class="flex gap-2 shrink-0">
                    @foreach([
                        ['url' => $slide->desktop_image_url, 'label' => 'D'],
                        ['url' => $slide->tablet_image_url, 'label' => 'T'],
                        ['url' => $slide->mobile_image_url, 'label' => 'M'],
                    ] as $thumb)
                        <div class="relative w-[5.5rem] h-14 sm:w-24 sm:h-14 rounded-lg overflow-hidden bg-slate-800 border border-slate-700 shrink-0">
                            @if(!empty($thumb['url']))
                                <img src="{{ $thumb['url'] }}" alt="" class="w-full h-full object-cover">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-slate-600 text-xs font-medium">{{ $thumb['label'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-white font-semibold truncate">{{ $slide->title !== '' ? $slide->title : 'Sans titre' }}</p>
                    @if($slide->subtitle)
                        <p class="text-slate-500 text-xs mt-0.5 truncate">{{ $slide->subtitle }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $slide->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-600/30 text-slate-300 border border-slate-600/40' }}">
                            {{ $slide->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-600/25 text-sky-200 border border-sky-500/35">
                            Ordre : {{ $slide->display_order }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-end sm:justify-center gap-1 shrink-0 border-t border-slate-800/80 sm:border-0 pt-3 sm:pt-0">
                    <button type="button"
                            onclick="openEditSlideModal(this)"
                            data-id="{{ $slide->id }}"
                            data-title="{{ e($slide->title) }}"
                            data-subtitle="{{ e($slide->subtitle ?? '') }}"
                            data-description="{{ e($slide->description ?? '') }}"
                            data-desktop-url="{{ e($slide->desktop_image_url) }}"
                            data-tablet-url="{{ e($slide->tablet_image_url ?? '') }}"
                            data-mobile-url="{{ e($slide->mobile_image_url ?? '') }}"
                            data-display-order="{{ $slide->display_order }}"
                            data-is-active="{{ $slide->is_active ? '1' : '0' }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600/90 hover:bg-blue-500 text-white transition"
                            title="Modifier">
                        <i class="fas fa-pen"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.administration.appearance.slides.toggle', $slide) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/90 hover:bg-amber-400 text-white transition"
                                title="{{ $slide->is_active ? 'Désactiver sur le site' : 'Activer sur le site' }}">
                            <i class="fas {{ $slide->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.administration.appearance.slides.destroy', $slide) }}" class="inline" onsubmit="return confirm('Supprimer ce slide ?');">
                        @csrf
                        @method('DELETE')
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
                <i class="fas fa-images text-3xl mb-3 text-slate-600"></i>
                <p>Aucun slide pour le moment.</p>
                <button type="button" onclick="openCreateSlideModal()" class="mt-4 text-violet-400 hover:text-violet-300 text-sm font-medium">Ajouter le premier slide</button>
            </div>
        @endforelse
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $slides->links() }}
    </div>
</div>

<div id="create-slide-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeCreateSlideModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-5xl bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-indigo-500 to-violet-600">
                <h2 class="text-white font-semibold">Ajouter un nouveau slide</h2>
                <button type="button" onclick="closeCreateSlideModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.administration.appearance.slides.store') }}" enctype="multipart/form-data" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 overflow-y-auto">
                @csrf
                @include('admin.system.partials.slide-form-fields')
                <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeCreateSlideModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">Ajouter le slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-slide-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeEditSlideModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-5xl bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-indigo-500 to-violet-600">
                <h2 class="text-white font-semibold">Modifier le slide</h2>
                <button type="button" onclick="closeEditSlideModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" id="edit-slide-form" action="" enctype="multipart/form-data" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 overflow-y-auto">
                @csrf
                @method('PATCH')
                @include('admin.system.partials.slide-form-fields', ['isEdit' => true])
                <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditSlideModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateSlideModal() { document.getElementById('create-slide-modal').classList.remove('hidden'); }
    function closeCreateSlideModal() { document.getElementById('create-slide-modal').classList.add('hidden'); }
    function closeEditSlideModal() { document.getElementById('edit-slide-modal').classList.add('hidden'); }

    function setSlidePreview(imgId, url) {
        const img = document.getElementById(imgId);
        if (!img) return;
        if (url) {
            img.src = url;
            img.classList.remove('hidden');
        } else {
            img.removeAttribute('src');
            img.classList.add('hidden');
        }
    }

    function openEditSlideModal(button) {
        const form = document.getElementById('edit-slide-form');
        const base = "{{ route('admin.administration.appearance.slides.update', ['slide' => '__ID__']) }}";
        form.action = base.replace('__ID__', button.dataset.id);

        document.getElementById('edit_title').value = button.dataset.title || '';
        document.getElementById('edit_subtitle').value = button.dataset.subtitle || '';
        document.getElementById('edit_description').value = button.dataset.description || '';
        document.getElementById('edit_display_order').value = button.dataset.displayOrder || '1';
        document.getElementById('edit_is_active').checked = button.dataset.isActive === '1';

        ['edit_desktop_image', 'edit_tablet_image', 'edit_mobile_image'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });

        setSlidePreview('edit_preview_desktop', button.dataset.desktopUrl || '');
        setSlidePreview('edit_preview_tablet', button.dataset.tabletUrl || '');
        setSlidePreview('edit_preview_mobile', button.dataset.mobileUrl || '');

        const hid = document.getElementById('edit_slide_id_hidden');
        if (hid) hid.value = button.dataset.id || '';

        document.getElementById('edit-slide-modal').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('appearance-flash');
        if (!el || el.dataset.hasErrors !== '1') return;
        var ctx = el.dataset.formContext || '';
        var editId = el.dataset.editSlideId || '';
        if (ctx === 'create') {
            openCreateSlideModal();
        } else if (ctx === 'edit' && editId) {
            var btn = document.querySelector('[data-id="' + editId.replace(/"/g, '') + '"]');
            if (btn) openEditSlideModal(btn);
        }
    });
</script>
@endpush
