@extends('layouts.visitor-public')

@section('title', 'Mes commandes Art & Créations')
@section('page-title', 'Mes commandes — Art & Créations')

@section('content')
<div class="max-w-5xl mx-auto">

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="bg-green-900 border border-slate-800 rounded-xl p-14 text-center">
            <i class="fas fa-palette text-slate-700 text-4xl mb-3 block"></i>
            <p class="text-slate-400 text-sm">Vous n'avez pas encore acheté d'œuvre.</p>
            <a href="{{ route('art.index') }}" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-black text-sm font-semibold transition">
                <i class="fas fa-palette text-xs"></i>
                Découvrir les œuvres
            </a>
        </div>
    @else
        <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-800">
            @foreach($orders as $order)
                <div class="flex items-center gap-4 px-5 py-4">
                    <a href="{{ $order->artwork ? route('art.show', $order->artwork->slug) : '#' }}"
                       class="shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-slate-800 border border-slate-700 hover:border-orange-500/60 transition">
                        @if(!empty($order->artwork?->images[0]))
                            <img src="{{ $order->artwork->images[0] }}" alt="{{ $order->artwork->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-image text-slate-600 text-xl"></i>
                            </div>
                        @endif
                    </a>

                    <div class="min-w-0 flex-1">
                        <p class="text-white font-medium truncate">{{ $order->artwork?->title ?? 'Œuvre indisponible' }}</p>
                        <p class="text-slate-500 text-xs">{{ $order->provider?->name }} · {{ $order->reference }}</p>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-white font-semibold text-sm">{{ number_format((int) $order->amount_total_xof, 0, ',', ' ') }} XOF</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold mt-1
                            @if($order->status === \App\Models\ArtworkOrder::STATUS_DELIVERED) bg-emerald-500/20 text-emerald-300
                            @elseif($order->status === \App\Models\ArtworkOrder::STATUS_PAID || $order->status === \App\Models\ArtworkOrder::STATUS_SHIPPED) bg-orange-500/20 text-orange-300
                            @elseif($order->status === \App\Models\ArtworkOrder::STATUS_REFUNDED || $order->status === \App\Models\ArtworkOrder::STATUS_CANCELLED) bg-red-500/20 text-red-300
                            @else bg-slate-700/40 text-slate-300 @endif">
                            {{ $order->labelForStatus() }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
