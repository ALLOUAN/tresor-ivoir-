@extends('layouts.app')

@section('title', 'Mes avis')
@section('page-title', 'Avis sur mon établissement')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">Tous</p><p class="text-white text-2xl font-bold mt-1">{{ number_format($counts['all']) }}</p></div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">En attente</p><p class="text-amber-400 text-2xl font-bold mt-1">{{ number_format($counts['pending']) }}</p></div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">Approuvés</p><p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($counts['approved']) }}</p></div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">Rejetés</p><p class="text-red-400 text-2xl font-bold mt-1">{{ number_format($counts['rejected']) }}</p></div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-800/80">
    @forelse($reviews as $review)
        <div class="px-5 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-white font-medium">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }} · <span class="text-amber-400">{{ $review->rating }}★</span></p>
                    <p class="text-slate-300 text-sm mt-1">{{ $review->comment }}</p>
                    @if($review->reply)
                        <div class="mt-2 p-2 bg-slate-800 rounded text-xs text-slate-300">
                            <p class="text-amber-400 mb-1">Votre réponse</p>
                            <p>{{ $review->reply->reply_text }}</p>
                        </div>
                    @endif
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs {{ $review->status === 'approved' ? 'bg-emerald-500/20 text-emerald-300' : ($review->status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 'bg-red-500/20 text-red-300') }}">
                    {{ $review->status }}
                </span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('provider.reviews.reply', $review) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="reply_text" placeholder="Répondre à cet avis..."
                           class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs text-slate-100 min-w-[260px]">
                    <button class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded">Répondre</button>
                </form>
                @if($review->reply)
                    <form method="POST" action="{{ route('provider.reviews.reply.destroy', $review) }}">
                        @csrf @method('DELETE')
                        <button class="bg-red-700 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded">Supprimer réponse</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="px-5 py-8 text-center text-slate-500">Aucun avis pour le moment.</div>
    @endforelse

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
