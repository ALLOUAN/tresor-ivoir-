@extends('layouts.app')

@section('title', 'Rôles & Permissions')
@section('page-title', 'Matrice des rôles & permissions')

@section('content')

{{-- ── Legend ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-6 mb-6">
    @php
    $roleStyles = [
        'admin'    => ['bg' => 'bg-rose-500',    'text' => 'Administrateur', 'icon' => 'fa-shield-halved'],
        'editor'   => ['bg' => 'bg-blue-500',    'text' => 'Éditeur',        'icon' => 'fa-pen-nib'],
        'provider' => ['bg' => 'bg-violet-500',  'text' => 'Prestataire',    'icon' => 'fa-store'],
        'visitor'  => ['bg' => 'bg-emerald-500', 'text' => 'Visiteur',       'icon' => 'fa-user'],
    ];
    @endphp

    @foreach($roles as $role)
    @php $s = $roleStyles[$role] ?? ['bg' => 'bg-slate-500', 'text' => ucfirst($role), 'icon' => 'fa-user']; @endphp
    <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg {{ $s['bg'] }} flex items-center justify-center">
            <i class="fas {{ $s['icon'] }} text-white text-xs"></i>
        </div>
        <span class="text-slate-300 text-sm font-medium">{{ $s['text'] }}</span>
    </div>
    @endforeach

    <div class="ml-auto flex items-center gap-4 text-xs text-slate-500">
        <span class="flex items-center gap-1.5">
            <i class="fas fa-check-circle text-emerald-400"></i> Accordé
        </span>
        <span class="flex items-center gap-1.5">
            <i class="fas fa-times-circle text-slate-700"></i> Refusé
        </span>
    </div>
</div>

{{-- ── Permission matrix ────────────────────────────────────────────────── --}}
<div class="space-y-6">
    @foreach($permissions as $group => $perms)
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">

        {{-- Group header --}}
        <div class="flex items-center gap-3 px-5 py-3 bg-slate-800/60 border-b border-slate-800">
            <i class="fas
                {{ match($group) {
                    'Articles'      => 'fa-newspaper text-amber-400',
                    'Événements'    => 'fa-calendar-days text-violet-400',
                    'Prestataires'  => 'fa-store text-blue-400',
                    'Utilisateurs'  => 'fa-users text-rose-400',
                    'Avis'          => 'fa-star text-yellow-400',
                    'Paiements'     => 'fa-credit-card text-emerald-400',
                    'Abonnements'   => 'fa-gem text-amber-300',
                    'Factures'      => 'fa-file-invoice text-slate-300',
                    'Newsletter'    => 'fa-envelope text-cyan-400',
                    'Médias'        => 'fa-images text-pink-400',
                    default         => 'fa-cog text-slate-400',
                } }}
                text-sm"></i>
            <h2 class="text-white font-semibold text-sm">{{ $group }}</h2>
            <span class="text-slate-500 text-xs">{{ $perms->count() }} permission{{ $perms->count() > 1 ? 's' : '' }}</span>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800">
                        <th class="text-left px-5 py-2.5 text-slate-500 text-xs font-medium uppercase tracking-wide w-1/2">Permission</th>
                        @foreach($roles as $role)
                        @php $s = $roleStyles[$role] ?? ['bg' => 'bg-slate-500', 'text' => ucfirst($role)]; @endphp
                        <th class="px-4 py-2.5 text-center text-xs font-medium uppercase tracking-wide">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full {{ $s['bg'] }}/20 text-xs" style="color: inherit">
                                <i class="fas {{ $s['icon'] ?? 'fa-user' }} text-[10px]" style="color: inherit"></i>
                                <span class="hidden sm:inline text-slate-300">{{ $s['text'] }}</span>
                            </span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($perms as $permission)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-3">
                            <div>
                                <p class="text-slate-200 text-sm">{{ $permission->label() }}</p>
                                <p class="text-slate-600 text-xs font-mono mt-0.5">{{ $permission->value }}</p>
                            </div>
                        </td>
                        @foreach($roles as $role)
                        @php $granted = in_array($permission->value, $map[$role] ?? []); @endphp
                        <td class="px-4 py-3 text-center">
                            @if($granted)
                                <i class="fas fa-circle-check text-emerald-400 text-base" title="Accordé à {{ $roleStyles[$role]['text'] ?? $role }}"></i>
                            @else
                                <i class="fas fa-circle-xmark text-slate-700 text-base" title="Refusé pour {{ $roleStyles[$role]['text'] ?? $role }}"></i>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Stats summary ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
    @foreach($roles as $role)
    @php
    $count = count($map[$role] ?? []);
    $total = count(\App\Enums\Permission::cases());
    $pct   = round($count / $total * 100);
    $s = $roleStyles[$role] ?? ['bg' => 'bg-slate-500', 'text' => ucfirst($role), 'icon' => 'fa-user'];
    @endphp
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 rounded-lg {{ $s['bg'] }} flex items-center justify-center">
                <i class="fas {{ $s['icon'] }} text-white text-xs"></i>
            </div>
            <span class="text-slate-300 text-sm font-medium">{{ $s['text'] }}</span>
        </div>
        <p class="text-white text-2xl font-bold">{{ $count }} <span class="text-slate-500 text-sm font-normal">/ {{ $total }}</span></p>
        <div class="mt-2 h-1.5 bg-slate-800 rounded-full overflow-hidden">
            <div class="{{ $s['bg'] }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <p class="text-slate-500 text-xs mt-1">{{ $pct }}% des permissions</p>
    </div>
    @endforeach
</div>

@endsection
