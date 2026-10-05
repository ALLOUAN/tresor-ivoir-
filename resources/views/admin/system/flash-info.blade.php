@extends('layouts.app')

@section('title', 'Flash info')
@section('page-title', 'Bandeau flash info')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'flash-info'])

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
        <p class="font-semibold text-rose-200 mb-2 flex items-center gap-2">
            <i class="fas fa-circle-exclamation"></i> Le message n'a pas pu être enregistré
        </p>
        <ul class="list-disc list-inside space-y-1 text-rose-100/95">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $typeMeta = [
        'info'    => ['label' => 'Info',    'icon' => 'fa-circle-info',        'badge' => 'bg-slate-500/20 text-slate-300 border-slate-500/30'],
        'success' => ['label' => 'Succès',  'icon' => 'fa-circle-check',       'badge' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'],
        'warning' => ['label' => 'Alerte',  'icon' => 'fa-triangle-exclamation','badge' => 'bg-amber-500/20 text-amber-300 border-amber-500/30'],
        'urgent'  => ['label' => 'Urgent',  'icon' => 'fa-bolt',                'badge' => 'bg-rose-500/20 text-rose-300 border-rose-500/30'],
    ];
@endphp

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20 mb-6">
    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-green-700 via-green-600 to-green-600">
        <div>
            <h2 class="text-white font-semibold text-lg tracking-tight">Bandeau flash info</h2>
            <p class="text-green-100/80 text-xs mt-0.5">Message d'annonce affiché juste sous l'en-tête, sur tout le site public.</p>
        </div>
        <button type="button" onclick="openCreateFlashModal()"
                class="inline-flex items-center justify-center gap-2 shrink-0 bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus"></i> Ajouter un message
        </button>
    </div>

    <div class="p-5 space-y-3">
        @forelse($flashInfos as $flash)
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-slate-800 bg-slate-800/20 hover:border-slate-700/80 transition">
                <div class="shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $typeMeta[$flash->type]['badge'] }}">
                        <i class="fas {{ $typeMeta[$flash->type]['icon'] }} text-[10px]"></i>
                        {{ $typeMeta[$flash->type]['label'] }}
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-white font-medium truncate">{{ $flash->message }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $flash->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-600/30 text-slate-300 border border-slate-600/40' }}">
                            {{ $flash->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/20 text-slate-300 border border-slate-600/40">
                            Ordre : {{ $flash->display_order }}
                        </span>
                        @if($flash->link_url)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/40">
                                <i class="fas fa-link text-[10px]"></i> {{ $flash->link_label ?: $flash->link_url }}
                            </span>
                        @endif
                        @if($flash->starts_at || $flash->ends_at)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/40">
                                <i class="fas fa-clock text-[10px]"></i>
                                {{ $flash->starts_at?->format('d/m/Y H:i') ?? '…' }} → {{ $flash->ends_at?->format('d/m/Y H:i') ?? '…' }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end sm:justify-center gap-1 shrink-0 border-t border-slate-800/80 sm:border-0 pt-3 sm:pt-0">
                    <button type="button"
                            onclick="openEditFlashModal(this)"
                            data-id="{{ $flash->id }}"
                            data-message="{{ e($flash->message) }}"
                            data-link-url="{{ e($flash->link_url ?? '') }}"
                            data-link-label="{{ e($flash->link_label ?? '') }}"
                            data-type="{{ $flash->type }}"
                            data-display-order="{{ $flash->display_order }}"
                            data-is-active="{{ $flash->is_active ? '1' : '0' }}"
                            data-is-dismissible="{{ $flash->is_dismissible ? '1' : '0' }}"
                            data-starts-at="{{ $flash->starts_at?->format('Y-m-d\TH:i') }}"
                            data-ends-at="{{ $flash->ends_at?->format('Y-m-d\TH:i') }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white transition"
                            title="Modifier">
                        <i class="fas fa-pen"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.administration.flash-info.toggle', $flash) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white transition"
                                title="{{ $flash->is_active ? 'Désactiver sur le site' : 'Activer sur le site' }}">
                            <i class="fas {{ $flash->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.administration.flash-info.destroy', $flash) }}" class="inline" onsubmit="return confirm('Supprimer ce message ?');">
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
                <i class="fas fa-bullhorn text-3xl mb-3 text-slate-600"></i>
                <p>Aucun message flash info pour le moment.</p>
                <button type="button" onclick="openCreateFlashModal()" class="mt-4 text-orange-400 hover:text-orange-300 text-sm font-medium">Ajouter le premier message</button>
            </div>
        @endforelse
    </div>

    @if($flashInfos->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $flashInfos->links() }}
        </div>
    @endif
</div>

{{-- ── Modal création ─────────────────────────────────────────────── --}}
<div id="create-flash-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeCreateFlashModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Ajouter un message flash info</h2>
                <button type="button" onclick="closeCreateFlashModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.administration.flash-info.store') }}" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @include('admin.system.partials.flash-info-form-fields')
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeCreateFlashModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal édition ──────────────────────────────────────────────── --}}
<div id="edit-flash-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeEditFlashModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Modifier le message</h2>
                <button type="button" onclick="closeEditFlashModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" id="edit-flash-form" action="" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @method('PATCH')
                @include('admin.system.partials.flash-info-form-fields', ['isEdit' => true])
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditFlashModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateFlashModal() { document.getElementById('create-flash-modal').classList.remove('hidden'); }
    function closeCreateFlashModal() { document.getElementById('create-flash-modal').classList.add('hidden'); }
    function closeEditFlashModal() { document.getElementById('edit-flash-modal').classList.add('hidden'); }

    function openEditFlashModal(button) {
        const form = document.getElementById('edit-flash-form');
        const base = "{{ route('admin.administration.flash-info.update', ['flashInfo' => '__ID__']) }}";
        form.action = base.replace('__ID__', button.dataset.id);

        document.getElementById('edit_message').value = button.dataset.message || '';
        document.getElementById('edit_link_url').value = button.dataset.linkUrl || '';
        document.getElementById('edit_link_label').value = button.dataset.linkLabel || '';
        document.getElementById('edit_type').value = button.dataset.type || 'info';
        document.getElementById('edit_display_order').value = button.dataset.displayOrder || '0';
        document.getElementById('edit_is_active').checked = button.dataset.isActive === '1';
        document.getElementById('edit_is_dismissible').checked = button.dataset.isDismissible === '1';
        document.getElementById('edit_starts_at').value = button.dataset.startsAt || '';
        document.getElementById('edit_ends_at').value = button.dataset.endsAt || '';

        document.getElementById('edit-flash-modal').classList.remove('hidden');
    }
</script>
@endpush
