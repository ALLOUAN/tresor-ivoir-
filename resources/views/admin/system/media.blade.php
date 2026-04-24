@extends('layouts.app')

@section('title', 'Gestionnaire de médias')
@section('page-title', 'Gestionnaire de médias')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'media'])

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-800/40">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center shrink-0">
                <i class="fas fa-folder-open text-indigo-300"></i>
            </div>
            <div>
                <h2 class="text-white font-semibold text-lg">Gestionnaire de médias</h2>
                <p class="text-slate-400 text-xs mt-0.5">Images, vidéos et documents pour le site et les contenus.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.administration.media.store') }}" enctype="multipart/form-data" class="shrink-0">
            @csrf
            <label class="inline-flex items-center justify-center gap-2 cursor-pointer rounded-lg bg-gradient-to-r from-amber-500 to-fuchsia-600 hover:from-amber-400 hover:to-fuchsia-500 text-white text-sm font-semibold px-4 py-2.5 transition">
                <i class="fas fa-cloud-arrow-up"></i>
                Télécharger un média
                <input type="file" name="file" class="hidden" required onchange="if (this.files.length) this.form.submit()">
            </label>
        </form>
    </div>

    <div class="px-5 py-4 border-b border-slate-800">
        <div class="rounded-lg border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm text-sky-100">
            <p class="font-medium text-sky-200 mb-1 flex items-center gap-2">
                <i class="fas fa-circle-info text-sky-300"></i> À propos
            </p>
            <p class="text-sky-100/90 text-xs sm:text-sm leading-relaxed">
                Cette section permet de gérer vos médias (images, vidéos, documents). Vous pouvez les utiliser dans les paramètres et le contenu du site. Taille maximale par fichier&nbsp;: 50&nbsp;Mo.
            </p>
        </div>
    </div>

    <div class="p-5">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                {{ $errors->first() }}
            </div>
        @endif

        @if($items->isEmpty())
            <div class="text-center py-16 rounded-xl border border-dashed border-slate-700 bg-slate-800/10">
                <i class="fas fa-folder-open text-5xl text-slate-600 mb-4"></i>
                <p class="text-slate-400 font-medium">Aucun média pour le moment</p>
                <p class="text-slate-600 text-sm mt-2">Utilisez le bouton « Télécharger un média » pour ajouter un fichier.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($items as $item)
                    <div class="rounded-xl border border-slate-800 bg-slate-800/20 p-4 flex flex-col gap-3">
                        <div class="aspect-video rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden">
                            @if($item->type === 'image')
                                <img src="{{ $item->url }}" alt="" class="max-h-full max-w-full object-contain">
                            @elseif($item->type === 'video')
                                <i class="fas fa-film text-4xl text-slate-500"></i>
                            @else
                                <i class="fas fa-file-lines text-4xl text-slate-500"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate" title="{{ $item->original_name }}">{{ $item->original_name }}</p>
                            <p class="text-slate-500 text-xs mt-1">
                                <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 bg-slate-700/80 text-slate-300 capitalize">{{ $item->type }}</span>
                                @php
                                    $bytes = (int) $item->size_bytes;
                                    $human = $bytes >= 1048576
                                        ? number_format($bytes / 1048576, 1).' Mo'
                                        : ($bytes >= 1024 ? number_format($bytes / 1024, 1).' Ko' : $bytes.' o');
                                @endphp
                                <span class="text-slate-600">·</span> {{ $human }}
                            </p>
                            <p class="text-slate-600 text-xs mt-0.5 truncate">{{ $item->created_at?->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-auto">
                            <input type="text" readonly value="{{ url($item->url) }}"
                                   class="flex-1 min-w-0 bg-slate-950/50 border border-slate-700 rounded px-2 py-1 text-[10px] text-slate-400 font-mono truncate"
                                   onclick="this.select()">
                            <form method="POST" action="{{ route('admin.administration.media.destroy', $item) }}" onsubmit="return confirm('Supprimer ce média ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 p-2 rounded-lg border border-slate-700 hover:border-rose-500/40 transition" title="Supprimer">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($items->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
