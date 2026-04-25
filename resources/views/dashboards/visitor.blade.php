@extends('layouts.app')

@section('title', 'Mon Espace')
@section('page-title', 'Mon espace visiteur')

@section('content')

@if(session('newsletter_success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check"></i> {{ session('newsletter_success') }}
    </div>
@endif
@if(session('newsletter_info'))
    <div class="mb-5 px-4 py-3 bg-slate-800 border border-slate-600 text-slate-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-info text-amber-400"></i> {{ session('newsletter_info') }}
    </div>
@endif
@if(session('newsletter_error'))
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation"></i> {{ session('newsletter_error') }}
    </div>
@endif
@if($errors->has('newsletter_email'))
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">
        {{ $errors->first('newsletter_email') }}
    </div>
@endif

{{-- ── Welcome banner ──────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-r from-amber-900/30 to-slate-900 border border-amber-700/20 rounded-2xl p-6 mb-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-5">
    <div class="w-14 h-14 rounded-2xl bg-amber-500 flex items-center justify-center text-white text-xl font-bold shrink-0">
        {{ auth()->user()->initials }}
    </div>
    <div class="flex-1">
        <h2 class="text-white text-xl font-bold">Bienvenue, {{ auth()->user()->first_name }} !</h2>
        <p class="text-slate-400 text-sm mt-0.5">Découvrez les richesses culturelles et touristiques de Côte d'Ivoire.</p>
    </div>
    {{-- Newsletter status --}}
    @if($newsletter)
    <div class="hidden sm:flex items-center gap-2 bg-emerald-900/30 border border-emerald-700/30 rounded-lg px-4 py-2">
        <i class="fas fa-envelope-circle-check text-emerald-400"></i>
        <span class="text-emerald-300 text-xs font-medium">Abonné à la newsletter</span>
    </div>
    @else
    <form method="post" action="{{ route('newsletter.subscribe') }}" class="hidden sm:flex items-stretch shrink-0">
        @csrf
        <input type="hidden" name="newsletter_email" value="{{ old('newsletter_email', auth()->user()->email) }}">
        <input type="hidden" name="redirect_to" value="dashboard">
        <button type="submit" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg px-4 py-2 transition text-left">
            <i class="fas fa-envelope text-amber-400"></i>
            <span class="text-slate-300 text-xs font-medium">S’abonner avec mon e-mail</span>
        </button>
    </form>
    @endif
    @if(! $newsletter)
    <form method="post" action="{{ route('newsletter.subscribe') }}" class="sm:hidden w-full">
        @csrf
        <input type="hidden" name="newsletter_email" value="{{ old('newsletter_email', auth()->user()->email) }}">
        <input type="hidden" name="redirect_to" value="dashboard">
        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg px-4 py-2.5 transition text-sm text-slate-300 font-medium">
            <i class="fas fa-envelope text-amber-400"></i>
            S’abonner à la newsletter
        </button>
    </form>
    @endif
</div>

{{-- ── Two columns ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Featured articles --}}
    <div class="xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-newspaper text-amber-400"></i> Articles à la une
            </h2>
            <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Tous les articles →</a>
        </div>

        @if($featured_articles->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-10 text-center text-slate-500 text-sm">
            <i class="fas fa-newspaper text-slate-700 text-3xl mb-2 block"></i>
            Aucun article à la une pour le moment.
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($featured_articles as $article)
            <a href="#" class="group bg-slate-900 border border-slate-800 rounded-xl overflow-hidden hover:border-amber-700/50 transition">
                {{-- Cover placeholder --}}
                <div class="h-32 bg-gradient-to-br from-slate-800 to-slate-700 flex items-center justify-center relative overflow-hidden">
                    <i class="fas fa-image text-slate-600 text-3xl"></i>
                    @if($article->is_featured)
                    <span class="absolute top-2 left-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                        <i class="fas fa-star mr-1 text-[10px]"></i>À la une
                    </span>
                    @endif
                </div>
                <div class="p-4">
                    <span class="text-xs text-amber-400 font-medium">{{ $article->category->name ?? 'Non classé' }}</span>
                    <h3 class="text-white text-sm font-semibold mt-1 line-clamp-2 group-hover:text-amber-400 transition">
                        {{ $article->title }}
                    </h3>
                    <div class="flex items-center gap-3 mt-2 text-xs text-slate-500">
                        <span>{{ $article->author->first_name ?? 'Rédaction' }}</span>
                        <span>·</span>
                        <span>{{ $article->published_at?->diffForHumans() }}</span>
                        <span>·</span>
                        <span><i class="fas fa-eye mr-0.5"></i>{{ number_format($article->views_count) }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Sidebar: upcoming events --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-calendar-days text-violet-400"></i> Événements à venir
            </h2>
            <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Voir tout →</a>
        </div>

        <div class="space-y-3">
            @forelse($upcoming_events as $event)
            <a href="#" class="group flex items-start gap-3 bg-slate-900 border border-slate-800 hover:border-violet-700/40 rounded-xl p-4 transition">
                {{-- Date badge --}}
                <div class="shrink-0 w-12 text-center bg-violet-900/30 border border-violet-800/40 rounded-lg py-1.5">
                    <p class="text-violet-300 text-xs font-bold uppercase">{{ $event->starts_at->format('M') }}</p>
                    <p class="text-white text-xl font-bold leading-none">{{ $event->starts_at->format('d') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium group-hover:text-violet-300 transition line-clamp-2">
                        {{ $event->title }}
                    </p>
                    <p class="text-slate-500 text-xs mt-1">
                        <i class="fas fa-location-dot mr-1"></i>{{ $event->city }}
                        @if($event->category)
                        · <span class="text-violet-400">{{ $event->category->name }}</span>
                        @endif
                    </p>
                </div>
            </a>
            @empty
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-slate-500 text-sm">
                <i class="fas fa-calendar text-slate-700 text-2xl mb-2 block"></i>
                Aucun événement à venir.
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── My reviews ──────────────────────────────────────────────────────── --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-star text-amber-400"></i> Mes derniers avis
        </h2>
        <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Voir mes avis →</a>
    </div>
    <div class="divide-y divide-slate-800">
        @forelse($my_reviews as $review)
        <div class="px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="text-white text-sm font-medium">{{ $review->provider->business_name ?? '—' }}</span>
                    @php
                    $rstatus = ['approved'=>['text-emerald-400','bg-emerald-900/30','Approuvé'],'pending'=>['text-amber-400','bg-amber-900/30','En attente'],'rejected'=>['text-red-400','bg-red-900/30','Refusé']];
                    [$rc, $rbg, $rl] = $rstatus[$review->status] ?? ['text-slate-400','bg-slate-800',$review->status];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs {{ $rc }} {{ $rbg }}">{{ $rl }}</span>
                </div>
                <div class="flex items-center gap-1 mb-1.5">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-700' }}"></i>
                    @endfor
                    <span class="text-slate-500 text-xs ml-2">{{ $review->created_at->diffForHumans() }}</span>
                </div>
                @if($review->comment)
                <p class="text-slate-400 text-xs line-clamp-2">{{ $review->comment }}</p>
                @endif
            </div>
            <a href="#" class="shrink-0 text-slate-600 hover:text-amber-400 text-xs transition">
                <i class="fas fa-pen"></i>
            </a>
        </div>
        @empty
        <div class="px-5 py-10 text-center text-slate-500 text-sm">
            <i class="fas fa-star text-slate-700 text-3xl mb-3 block"></i>
            <p class="mb-3">Vous n'avez pas encore rédigé d'avis.</p>
            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition">
                <i class="fas fa-compass"></i> Explorer les prestataires
            </a>
        </div>
        @endforelse
    </div>
</div>

@endsection
