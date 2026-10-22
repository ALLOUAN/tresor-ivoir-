@extends('layouts.app')

@section('title', __('Types de visite'))
@section('page-title', __('Types de visite').' — '.$experience->name)

@section('header-actions')
    @if($routes['back'])
        <a href="{{ $routes['back'] }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
            <i class="fas fa-arrow-left text-xs"></i> {{ __('Retour') }}
        </a>
    @endif
    <a href="{{ route($routes['create'], $routes['params']) }}"
       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> {{ __('Créer un type de visite') }}
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div class="mb-5 px-4 py-3 bg-green-900 border border-slate-800 text-slate-300 text-sm rounded-xl flex items-start gap-3">
    <i class="fas fa-circle-info text-orange-400 mt-0.5"></i>
    <div>
        {{ __('Créez les formules de visite proposées aux visiteurs (ex. « Visite nocturne », « Visite VIP », « Sortie scolaire »). Chaque type a son prix par personne et peut inclure un guide. Les visiteurs les choisissent sur la fiche du site.') }}
        @if($routes['sessions'])
            <br><span class="text-slate-500">{{ __('Les visites groupées à date fixe se gèrent dans') }} <a href="{{ $routes['sessions'] }}" class="text-orange-400 hover:text-orange-300 underline">{{ __('Sessions de visite groupée') }}</a>.</span>
        @endif
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">{{ __('Type de visite') }}</th>
                    <th class="text-left px-5 py-3">{{ __('Prix / pers.') }}</th>
                    <th class="text-left px-5 py-3">{{ __('Guide') }}</th>
                    <th class="text-left px-5 py-3">{{ __('Durée') }}</th>
                    <th class="text-left px-5 py-3">{{ __('Max.') }}</th>
                    <th class="text-left px-5 py-3">{{ __('Réservations') }}</th>
                    <th class="text-left px-5 py-3">{{ __('Statut') }}</th>
                    <th class="text-right px-5 py-3">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($types as $type)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 align-top">
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 shrink-0 rounded-lg bg-orange-500/15 text-orange-400 flex items-center justify-center"><i class="fas {{ $type->iconClass() }}"></i></span>
                                <div class="min-w-0">
                                    <p class="text-white font-medium">{{ $type->name }}</p>
                                    @if($type->description)<p class="text-slate-500 text-xs mt-0.5">{{ $type->description }}</p>@endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-200 whitespace-nowrap">
                            {{ $type->price_xof ? number_format((int) $type->price_xof, 0, ',', ' ').' XOF' : __('Gratuit') }}
                        </td>
                        <td class="px-5 py-3 align-top text-slate-300 whitespace-nowrap">
                            @if($type->includes_guide)
                                <i class="fas fa-user-tie text-orange-400 mr-1"></i>{{ __('Oui') }}
                                @if($type->guide_supplement_xof)<span class="text-slate-500 text-xs"> (+{{ number_format((int) $type->guide_supplement_xof, 0, ',', ' ') }} XOF)</span>@endif
                            @else
                                <span class="text-slate-500">{{ __('Non') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 align-top text-slate-300 whitespace-nowrap">{{ $type->duration_minutes ? $type->duration_minutes.' min' : '—' }}</td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $type->max_participants ?: '—' }}</td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $type->visits_count }}</td>
                        <td class="px-5 py-3 align-top">
                            <form method="POST" action="{{ route($routes['toggle'], $routes['params'] + ['visitType' => $type->id]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="{{ $type->is_active ? __('Désactiver') : __('Activer') }}"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($type->is_active) bg-emerald-500/20 text-emerald-300 border border-emerald-500/35
                                        @else bg-slate-700/40 text-slate-300 border border-slate-600/40 @endif">
                                    {{ $type->is_active ? __('Actif') : __('Inactif') }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route($routes['edit'], $routes['params'] + ['visitType' => $type->id]) }}" class="text-slate-400 hover:text-white text-xs mr-3">
                                <i class="fas fa-pen"></i> {{ __('Modifier') }}
                            </a>
                            <form method="POST" action="{{ route($routes['destroy'], $routes['params'] + ['visitType' => $type->id]) }}" class="inline"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Supprimer ce type de visite ? Les visites déjà réservées sont conservées.')) }});">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs" aria-label="{{ __('Supprimer') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                            {{ __('Aucun type de visite pour l\'instant.') }}
                            <a href="{{ route($routes['create'], $routes['params']) }}" class="text-orange-400 hover:text-orange-300 underline">{{ __('Créer le premier') }}</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
