@extends('layouts.app')

@section('title', 'Mes avis')
@section('page-title', 'Avis sur mon établissement')

@section('content')
<style>
    .premium-shimmer-card {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }
    .premium-shimmer-card::after {
        content: '';
        position: absolute;
        top: -130%;
        left: -45%;
        width: 38%;
        height: 360%;
        transform: rotate(22deg) translateX(-180%);
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.04) 35%, rgba(255,255,255,0.16) 50%, rgba(255,255,255,0.04) 65%, transparent 100%);
        pointer-events: none;
        transition: transform .85s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .premium-shimmer-card:hover::after {
        transform: rotate(22deg) translateX(520%);
    }
    @media (prefers-reduced-motion: reduce) {
        .premium-shimmer-card::after { transition: none; }
    }
</style>

@if($errors->any())
    <div class="mb-4 rounded-xl border border-rose-500/40 bg-linear-to-r from-rose-500/15 to-rose-500/5 px-4 py-3 text-sm text-rose-200 shadow-lg shadow-rose-900/20">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="premium-shimmer-card bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-700/70 rounded-2xl p-4 shadow-xl shadow-black/20 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-500/70 hover:shadow-2xl hover:shadow-slate-900/40">
        <p class="text-slate-500 text-xs uppercase tracking-wider">Tous</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($counts['all']) }}</p>
    </div>
    <div class="premium-shimmer-card bg-linear-to-br from-amber-900/25 via-slate-900 to-slate-950 border border-amber-500/25 rounded-2xl p-4 shadow-xl shadow-black/20 transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-400/40 hover:shadow-2xl hover:shadow-amber-900/30">
        <p class="text-amber-200/70 text-xs uppercase tracking-wider">En attente</p>
        <p class="text-amber-300 text-2xl font-bold mt-1">{{ number_format($counts['pending']) }}</p>
    </div>
    <div class="premium-shimmer-card bg-linear-to-br from-emerald-900/20 via-slate-900 to-slate-950 border border-emerald-500/25 rounded-2xl p-4 shadow-xl shadow-black/20 transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-400/40 hover:shadow-2xl hover:shadow-emerald-900/30">
        <p class="text-emerald-200/70 text-xs uppercase tracking-wider">Approuvés</p>
        <p class="text-emerald-300 text-2xl font-bold mt-1">{{ number_format($counts['approved']) }}</p>
    </div>
    <div class="premium-shimmer-card bg-linear-to-br from-rose-900/20 via-slate-900 to-slate-950 border border-rose-500/25 rounded-2xl p-4 shadow-xl shadow-black/20 transition-all duration-300 hover:-translate-y-0.5 hover:border-rose-400/40 hover:shadow-2xl hover:shadow-rose-900/30">
        <p class="text-rose-200/70 text-xs uppercase tracking-wider">Rejetés</p>
        <p class="text-rose-300 text-2xl font-bold mt-1">{{ number_format($counts['rejected']) }}</p>
    </div>
</div>

<div class="bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-700/70 rounded-2xl overflow-hidden shadow-xl shadow-black/25 divide-y divide-slate-800/80">
    @forelse($reviews as $review)
        <div class="px-5 py-5 transition-all duration-300 hover:bg-slate-800/20">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-white font-semibold truncate">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }}</p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] bg-amber-500/15 border border-amber-400/30 text-amber-300">
                            <i class="fas fa-star text-[10px]"></i>{{ $review->rating }}/5
                        </span>
                    </div>
                    <p class="text-slate-300 text-sm mt-2 leading-relaxed">{{ $review->comment }}</p>
                    @if($review->replies->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach($review->replies as $reply)
                                <div class="p-3 bg-slate-800/85 border border-slate-700 rounded-xl text-xs text-slate-300 transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-400/30 hover:shadow-lg hover:shadow-amber-900/15">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-amber-300 font-semibold tracking-wide uppercase text-[10px]">Votre réponse</p>
                                        <form method="POST" action="{{ route('provider.reviews.reply.destroy', [$review, $reply]) }}"
                                              onsubmit="return confirm('Supprimer cette réponse ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 text-rose-300 hover:text-rose-200 text-[11px] transition-colors duration-200">
                                                <i class="fas fa-trash-can text-[10px]"></i>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                    <p class="mt-1.5 leading-relaxed">{{ $reply->reply_text }}</p>
                                    <p class="text-slate-500 mt-1">
                                        Publiée: {{ $reply->created_at?->translatedFormat('d M Y H:i') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs {{ $review->status === 'approved' ? 'bg-emerald-500/20 text-emerald-300' : ($review->status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 'bg-red-500/20 text-red-300') }}">
                    {{ $review->status }}
                </span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/80">
                <form method="POST" action="{{ route('provider.reviews.destroy', $review) }}" class="mb-3"
                      onsubmit="return confirm('Supprimer définitivement cet avis ?');">
                    @csrf
                    @method('DELETE')
                    <button class="inline-flex items-center gap-1.5 bg-rose-700/90 hover:bg-rose-600 text-white text-xs px-3 py-1.5 rounded-lg border border-rose-500/40 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-rose-900/30">
                        <i class="fas fa-trash-can text-[10px]"></i>
                        Supprimer avis
                    </button>
                </form>

                @if($review->status === 'rejected')
                    <p class="text-xs text-rose-300 bg-rose-500/10 border border-rose-500/20 rounded px-3 py-2 inline-flex items-center gap-2">
                        <i class="fas fa-circle-exclamation"></i>
                        Avis rejeté: réponse désactivée.
                    </p>
                @else
                    <div class="mb-2 flex items-center gap-2">
                        <span class="inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full border border-amber-500/30 bg-amber-500/10 text-amber-300 shadow-sm shadow-amber-900/20">
                            <i class="fas fa-reply"></i>
                            Répondre à cet avis
                        </span>
                    </div>
                    <form method="POST" action="{{ route('provider.reviews.reply', $review) }}" class="space-y-2">
                        @csrf
                        <textarea name="reply_text" rows="2" maxlength="1000"
                                  placeholder="Répondre à cet avis..."
                                  class="w-full max-w-2xl bg-slate-800/90 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/35 focus:border-amber-500/40">{{ old('reply_text') }}</textarea>
                        <div class="flex flex-wrap items-center gap-2">
                            <button class="inline-flex items-center gap-1.5 bg-linear-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white text-xs px-3 py-1.5 rounded-lg shadow-lg shadow-amber-900/30 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-amber-900/40">
                                <i class="fas fa-paper-plane"></i>
                                Répondre
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="px-5 py-10 text-center text-slate-500">
            <i class="fas fa-comment-dots text-3xl text-slate-700 mb-2"></i>
            <p>Aucun avis pour le moment.</p>
        </div>
    @endforelse

    <div class="px-5 py-4 border-t border-slate-800 bg-slate-900/60">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
