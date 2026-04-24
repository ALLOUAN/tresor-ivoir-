@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('content')
<div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center">
            <i class="fas {{ $icon }} text-amber-400"></i>
        </div>
        <h2 class="text-white text-xl font-semibold">{{ $title }}</h2>
    </div>
    <p class="text-slate-400 text-sm">{{ $description }}</p>
    <div class="mt-5 p-4 rounded-lg border border-dashed border-slate-700 text-slate-500 text-sm">
        Cette section est prête. Tu peux maintenant me dire quelles actions/formulaires tu veux exactement ici.
    </div>
</div>
@endsection
