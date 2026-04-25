@extends('layouts.app')

@section('title', $page->title_fr.' — Centre d\'information')
@section('page-title', 'Centre d\'information')

@section('header-actions')
    <a href="{{ route('admin.administration.info-center') }}"
        class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
        <i class="fas fa-arrow-left"></i> Toutes les pages
    </a>
@endsection

@section('content')

    @php $slug = $page->slugEnum(); @endphp

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

    <div class="mb-6 flex flex-wrap items-center gap-3 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700/80 text-slate-400">
            <i class="fas fa-palette text-amber-400/80"></i> Interface dédiée à cette page
        </span>
        @if($slug)
            <span>{{ $slug->label() }}</span>
        @endif
    </div>

    @switch($page->slug)
        @case('about')
            @include('admin.system.information-center.forms.about', ['page' => $page])
            @break
        @case('user-guide')
            @include('admin.system.information-center.forms.user-guide', ['page' => $page])
            @break
        @case('faq')
            @include('admin.system.information-center.forms.faq', ['page' => $page])
            @break
        @case('legal-notice')
            @include('admin.system.information-center.forms.legal-notice', ['page' => $page])
            @break
        @case('privacy-policy')
            @include('admin.system.information-center.forms.privacy-policy', ['page' => $page])
            @break
        @default
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden p-6 sm:p-8">
                @include('admin.system.information-center.forms.default', ['page' => $page])
            </div>
    @endswitch

@endsection
