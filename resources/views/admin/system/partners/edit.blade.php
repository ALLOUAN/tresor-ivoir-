@extends('layouts.app')

@section('title', 'Modifier — '.$partner->name)
@section('page-title', 'Tableau de bord')

@section('header-actions')
    <a href="{{ route('admin.administration.partners') }}"
        class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
@endsection

@section('content')

    <nav class="text-xs text-slate-500 mb-6">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-300">Tableau de bord</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('admin.administration.partners') }}" class="hover:text-slate-300">Partenaires</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-400 truncate inline-block max-w-[200px] align-bottom">{{ $partner->name }}</span>
    </nav>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.administration.partners.update', $partner) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.system.partners._form-fields', ['partner' => $partner, 'typeOptions' => $typeOptions])

        <div class="flex justify-end pt-2">
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-rose-500 hover:from-violet-500 hover:to-rose-400 shadow-lg transition">
                <i class="fas fa-floppy-disk"></i> Enregistrer
            </button>
        </div>
    </form>

@endsection
