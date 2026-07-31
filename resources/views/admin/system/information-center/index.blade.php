@extends('layouts.app')

@section('title', 'Centre d\'information')
@section('page-title', 'Centre d\'information')

@section('content')

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
            <div class="w-10 h-10 rounded-lg bg-orange-500/15 border border-orange-500/30 flex items-center justify-center shrink-0">
                <i class="fas fa-circle-info text-orange-300"></i>
            </div>
            <div>
                <h2 class="text-white font-semibold text-lg">Centre d'information</h2>
                <p class="text-slate-400 text-xs mt-0.5">Chaque page dispose d’un <span class="text-orange-200/90">éditeur visuel distinct</span> (mise en page moderne). Même données : titres + contenus FR/EN.</p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($pages as $p)
            @php $slug = $p->slugEnum(); @endphp
            <a href="{{ route('admin.administration.info-center.edit', $p) }}"
                class="group bg-green-900 border border-slate-800 hover:border-orange-500/35 rounded-xl p-5 flex gap-4 transition shadow-lg shadow-green-950/10 hover:shadow-orange-900/5">
                <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0 group-hover:bg-orange-500/10 group-hover:border-orange-500/30 transition">
                    <i class="fas {{ $slug?->icon() ?? 'fa-file-lines' }} text-orange-400/80 group-hover:text-orange-300"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-white font-semibold text-sm group-hover:text-orange-100 transition leading-snug">{{ $p->title_fr }}</h3>
                    @if($slug)
                        <p class="text-slate-500 text-xs mt-1.5 leading-relaxed">{{ $slug->description() }}</p>
                    @endif
                    <p class="text-orange-400/70 text-[11px] font-medium mt-3 inline-flex items-center gap-1">
                        Modifier <i class="fas fa-arrow-right text-[9px]"></i>
                    </p>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-14 text-slate-500 border border-dashed border-slate-700 rounded-xl">
                <p class="mb-2">Aucune page en base.</p>
                <code class="text-xs bg-slate-800 px-2 py-1 rounded">php artisan db:seed --class=InformationPageSeeder</code>
            </div>
        @endforelse
    </div>

@endsection
