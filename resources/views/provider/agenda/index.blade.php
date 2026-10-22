@extends('layouts.app')

@section('title', 'Planning du jour')
@section('page-title', 'Planning')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-white text-xl font-semibold">{{ ucfirst($day->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}</h1>
        <p class="text-slate-400 text-sm">{{ $entries->count() }} élément(s) au programme · {{ $provider->name }}</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="GET" action="{{ route('provider.agenda') }}">
            <input type="date" name="date" value="{{ $day->toDateString() }}" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </form>
        <button type="button" onclick="window.print()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-3 py-2 rounded-lg"><i class="fas fa-print"></i><span class="hidden sm:inline ml-1">Imprimer</span></button>
    </div>
</div>

<div class="flex gap-2 overflow-x-auto pb-2 mb-5 print:hidden">
    @foreach($week as $d)
        <a href="{{ route('provider.agenda', ['date' => $d->toDateString()]) }}"
           class="shrink-0 text-center rounded-xl border px-3 py-2 min-w-[4.5rem] {{ $d->isSameDay($day) ? 'border-orange-500 bg-orange-500/15 text-white' : 'border-slate-800 bg-green-900 text-slate-300 hover:border-slate-600' }}">
            <span class="block text-[11px] uppercase text-slate-400">{{ $d->isToday() ? 'Auj.' : $d->locale('fr')->isoFormat('ddd') }}</span>
            <span class="block font-semibold">{{ $d->format('d/m') }}</span>
        </a>
    @endforeach
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl divide-y divide-slate-800">
    @forelse($entries as $e)
        <div class="flex items-start gap-4 px-5 py-4">
            <div class="w-14 shrink-0 text-right">
                <p class="text-white font-semibold">{{ $e['time'] ?? '—' }}</p>
            </div>
            <div class="w-9 h-9 shrink-0 rounded-lg bg-slate-800 flex items-center justify-center text-orange-400"><i class="fas {{ $e['icon'] }}"></i></div>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ $e['label'] }}@if($e['status']) · <span class="{{ $e['status'] === 'À confirmer' ? 'text-amber-300' : 'text-emerald-300' }}">{{ $e['status'] }}</span>@endif</p>
                <p class="text-white font-medium truncate">{{ $e['title'] }}</p>
                <p class="text-slate-400 text-sm">{{ $e['detail'] }}</p>
            </div>
            <div class="shrink-0 flex items-center gap-2 print:hidden">
                @if($e['phone'])<a href="tel:{{ preg_replace('/[^0-9+]/', '', $e['phone']) }}" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-200" title="Appeler"><i class="fas fa-phone text-sm"></i></a>@endif
                <a href="{{ $e['url'] }}" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-200" title="Ouvrir"><i class="fas fa-arrow-right text-sm"></i></a>
            </div>
        </div>
    @empty
        <div class="px-5 py-12 text-center text-slate-400">
            <i class="fas fa-mug-hot text-2xl text-slate-600 mb-2 block"></i>
            Rien de prévu ce jour-là.
        </div>
    @endforelse
</div>
@endsection
