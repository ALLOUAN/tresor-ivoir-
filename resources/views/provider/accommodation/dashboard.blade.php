@extends('layouts.app')

@section('title', 'Mon hébergement')
@section('page-title', 'Vue d\'ensemble — Mon hébergement')

@section('content')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif

@if(!$accommodation)
<div class="mb-6 px-4 py-3 bg-amber-900/30 border border-amber-800 text-amber-200 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-info shrink-0"></i>
    Aucune fiche établissement pour l'instant. Commencez par la
    <a href="{{ route('provider.accommodation.profile.edit') }}" class="underline font-semibold">créer</a>.
</div>
@endif

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-emerald-900/40 to-green-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Types de chambres</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($roomCount) }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-900/40 to-green-900 border border-orange-500/30 rounded-xl p-4">
        <p class="text-orange-300/80 text-xs font-medium">Photos en galerie</p>
        <p class="text-orange-200 text-2xl font-bold mt-1">{{ number_format($photoCount) }}</p>
    </div>
    <div class="bg-gradient-to-br from-blue-900/40 to-green-900 border border-blue-500/30 rounded-xl p-4">
        <p class="text-blue-300/80 text-xs font-medium">Publication</p>
        <p class="text-blue-200 text-2xl font-bold mt-1">{{ ($accommodation?->is_active ?? false) ? 'Actif' : 'Inactif' }}</p>
    </div>
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Liens de réservation</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format(count($accommodation?->booking_links ?? [])) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('provider.accommodation.profile.edit') }}" class="bg-green-900 border border-slate-800 rounded-xl p-5 hover:border-orange-500/40 transition">
        <i class="fas fa-hotel text-orange-400 text-lg"></i>
        <p class="text-white font-semibold mt-2">Fiche établissement</p>
        <p class="text-slate-500 text-xs mt-1">Informations générales, contact, commodités, images principales.</p>
    </a>
    <a href="{{ route('provider.accommodation.rooms.index') }}" class="bg-green-900 border border-slate-800 rounded-xl p-5 hover:border-orange-500/40 transition">
        <i class="fas fa-bed text-orange-400 text-lg"></i>
        <p class="text-white font-semibold mt-2">Chambres & tarifs</p>
        <p class="text-slate-500 text-xs mt-1">Ajouter, modifier ou retirer vos types de chambres.</p>
    </a>
    <a href="{{ route('provider.accommodation.gallery.index') }}" class="bg-green-900 border border-slate-800 rounded-xl p-5 hover:border-orange-500/40 transition">
        <i class="fas fa-photo-film text-orange-400 text-lg"></i>
        <p class="text-white font-semibold mt-2">Galerie photos</p>
        <p class="text-slate-500 text-xs mt-1">Ajouter ou retirer des photos de l'établissement.</p>
    </a>
</div>

@if($accommodation)
<div class="bg-green-900 border border-slate-800 rounded-xl p-5">
    <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
        <i class="fas fa-list-check text-orange-400"></i> Complétude de la fiche
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        @foreach([
            'description' => 'Description complète renseignée',
            'cover_image' => 'Image de couverture ajoutée',
            'rooms' => 'Au moins une chambre ajoutée',
            'booking_links' => 'Au moins un lien de réservation',
        ] as $key => $label)
        <div class="flex items-center gap-2 text-sm {{ $checklist[$key] ? 'text-emerald-300' : 'text-slate-500' }}">
            <i class="fas {{ $checklist[$key] ? 'fa-circle-check' : 'fa-circle' }} text-xs"></i>
            {{ $label }}
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
