@extends('layouts.visitor-public')

@section('title', 'Mes achats')
@section('page-title', 'Mes achats')

@section('content')
<div class="max-w-5xl mx-auto">

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($purchases->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-14 text-center">
            <i class="fas fa-image text-slate-700 text-4xl mb-3 block"></i>
            <p class="text-slate-400 text-sm">Vous n'avez pas encore effectué d'achat.</p>
            <a href="{{ route('gallery.public') }}" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black text-sm font-semibold transition">
                <i class="fas fa-images text-xs"></i>
                Découvrir la galerie
            </a>
        </div>
    @else
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-800">
            @foreach($purchases as $purchase)
                @php $media = $purchase->media; @endphp
                <div class="flex items-center gap-4 px-5 py-4">

                    {{-- Miniature --}}
                    <a href="{{ route('gallery.public.show', $media->uuid) }}" class="shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-slate-800 border border-slate-700 hover:border-amber-500/60 transition">
                        @if($media->url)
                            <img src="{{ $media->url }}" alt="{{ $media->alt_text ?? $media->title }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-image text-slate-600 text-xl"></i>
                            </div>
                        @endif
                    </a>

                    {{-- Infos --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">
                            {{ $media->title ?? $media->original_name }}
                        </p>
                        <p class="text-slate-500 text-xs mt-0.5">
                            Acheté le {{ $purchase->paid_at->format('d/m/Y à H:i') }}
                        </p>
                        <p class="text-amber-400 text-xs font-semibold mt-0.5">
                            {{ number_format((float) $purchase->amount, 0, ',', ' ') }} FCFA
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="shrink-0 flex items-center gap-2">
                        <a href="{{ route('gallery.public.show', $media->uuid) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-medium transition">
                            <i class="fas fa-eye text-[10px]"></i>
                            Voir
                        </a>
                        @if($media->file_path)
                            <a href="{{ route('visitor.purchases.download', $purchase->uuid) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-medium transition">
                                <i class="fas fa-download text-[10px]"></i>
                                Télécharger
                            </a>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-5">
            {{ $purchases->links() }}
        </div>
    @endif

</div>
@endsection
