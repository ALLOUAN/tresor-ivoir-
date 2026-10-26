<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>@yield('title', 'Mon espace') — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
        @stack('styles')
    <style>
        html:not(.dark) body                    { background-color:#D8E9E0!important; color:#0E3B2A!important; }
        html:not(.dark) .text-white             { color:#0E3B2A!important; }
        html:not(.dark) .text-slate-100         { color:#0E3B2A!important; }
        html:not(.dark) .text-slate-200         { color:#0E3B2A!important; }
        html:not(.dark) .text-slate-300         { color:#24503D!important; }
        html:not(.dark) .text-slate-400         { color:#24503D!important; }
        html:not(.dark) .text-slate-500         { color:#557466!important; }
        html:not(.dark) .text-slate-600         { color:#24503D!important; }
        html:not(.dark) .bg-green-900           { background-color:#D8E9E0!important; }
        html:not(.dark) .bg-slate-800           { background-color:#D8E9E0!important; }
        html:not(.dark) .bg-slate-700           { background-color:#8AA99B!important; }
        html:not(.dark) .border-slate-800       { border-color:#8AA99B!important; }
        html:not(.dark) .border-slate-700       { border-color:#8AA99B!important; }
        html:not(.dark) .divide-slate-800>*+*   { border-color:#8AA99B!important; }
        html:not(.dark) .border-white\/10       { border-color:rgba(14,59,42,.1)!important; }
        html:not(.dark) .border-white\/6        { border-color:rgba(14,59,42,.06)!important; }
        html:not(.dark) .bg-white\/2            { background-color:rgba(14,59,42,.02)!important; }
        html:not(.dark) .bg-white\/4            { background-color:rgba(14,59,42,.04)!important; }
        /* Page-title bar */
        html:not(.dark) .page-title-bar         { border-bottom-color:rgba(14,59,42,.07)!important; background-color:rgba(14,59,42,.015)!important; }
        /* Flash messages */
        html:not(.dark) .bg-emerald-900\/30     { background-color:rgba(216,233,224,.6)!important; border-color:rgba(0,135,79,.3)!important; }
        html:not(.dark) .bg-rose-900\/30        { background-color:rgba(255,228,230,.6)!important; border-color:rgba(244,63,94,.3)!important; }
        /* Welcome banner on visitor dashboard */
        html:not(.dark) .from-orange-900\/30     { --tw-gradient-from:rgba(253,239,227,.7)!important; }
        html:not(.dark) .to-green-900           { --tw-gradient-to:#ffffff!important; }
        /* Hover states on cards */
        html:not(.dark) .hover\:border-orange-600\/50:hover { border-color:rgba(235,110,0,.35)!important; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-a2d53ae5.css') }}?v={{ @filemtime(public_path('css/tw-a2d53ae5.css')) }}">
    @include('partials.visitor-skin')
</head>
<body class="min-h-screen flex flex-col font-sans" style="background:#F3F8F5">

    {{-- Espace client : barre dédiée, sans la barre orange, le menu du site ni le Flash info. --}}
    @include('partials.visitor-bar')

    {{-- Barre latérale (ordinateur) / panneau coulissant (mobile) + colonne de contenu --}}
    <div class="vs-shell">
    @include('partials.visitor-sidebar')
    <div class="vs-main">

    {{-- En-tête de page : fil d'Ariane + titre (motifs discrets) --}}
    @hasSection('page-title')
    <header class="va-head">
        @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
        <div class="va-head__in">
            <p class="va-crumb"><a href="{{ route('visitor.dashboard') }}">{{ __('Mon espace') }}</a><span aria-hidden="true">›</span><span>@yield('page-title')</span></p>
            <h1 class="va-title">@yield('page-title')</h1>
        </div>
    </header>
    @else
    <div class="va-top"></div>
    @endif

    {{-- Flash messages --}}
    <div class="va-flash max-w-5xl mx-auto w-full px-4 sm:px-6 mt-4">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
                <i class="fas fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl flex items-center gap-2">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Contenu principal --}}
    <main class="va flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 py-6 pb-14">
        @yield('content')
    </main>

    </div>{{-- /.vs-main --}}
    </div>{{-- /.vs-shell --}}

    @include('partials.homepage-footer')

    @include('partials.image-protection')
    {{-- Repris de partials.public-top-nav (qui n'est plus inclus ici) : assistant, thème clair, charte commune. --}}
    @include('partials.ai-chat-widget')
    @include('partials.theme-light-bridge')
    @include('partials.ui-kit')
    @stack('scripts')
</body>
</html>
