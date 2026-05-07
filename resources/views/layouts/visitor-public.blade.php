<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>@yield('title', 'Mon espace') — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        plus: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        gold: { 300:'#fcd68a', 400:'#f5b942', 500:'#e8a020', 600:'#c4811a' },
                        dark: { 600:'#252520', 700:'#1c1c16', 800:'#141410', 900:'#0d0d0b' },
                    }
                }
            }
        }
    </script>
    @stack('styles')
    <style>
        html:not(.dark) body                    { background-color:#f8f5ee!important; color:#1c1915!important; }
        html:not(.dark) .text-white             { color:#1c1915!important; }
        html:not(.dark) .text-slate-100         { color:#1c1915!important; }
        html:not(.dark) .text-slate-200         { color:#2d2a23!important; }
        html:not(.dark) .text-slate-300         { color:#44413a!important; }
        html:not(.dark) .text-slate-400         { color:#6b6860!important; }
        html:not(.dark) .text-slate-500         { color:#9e9b90!important; }
        html:not(.dark) .text-slate-600         { color:#7c796f!important; }
        html:not(.dark) .bg-slate-900           { background-color:#ffffff!important; }
        html:not(.dark) .bg-slate-800           { background-color:#f4f0e8!important; }
        html:not(.dark) .bg-slate-700           { background-color:#e8e3da!important; }
        html:not(.dark) .border-slate-800       { border-color:#e8e3da!important; }
        html:not(.dark) .border-slate-700       { border-color:#d6d0c5!important; }
        html:not(.dark) .divide-slate-800>*+*   { border-color:#e8e3da!important; }
        html:not(.dark) .border-white\/10       { border-color:rgba(0,0,0,.1)!important; }
        html:not(.dark) .border-white\/6        { border-color:rgba(0,0,0,.06)!important; }
        html:not(.dark) .bg-white\/2            { background-color:rgba(0,0,0,.02)!important; }
        html:not(.dark) .bg-white\/4            { background-color:rgba(0,0,0,.04)!important; }
        /* Page-title bar */
        html:not(.dark) .page-title-bar         { border-bottom-color:rgba(0,0,0,.07)!important; background-color:rgba(0,0,0,.015)!important; }
        /* Flash messages */
        html:not(.dark) .bg-emerald-900\/30     { background-color:rgba(209,250,229,.6)!important; border-color:rgba(16,185,129,.3)!important; }
        html:not(.dark) .bg-rose-900\/30        { background-color:rgba(255,228,230,.6)!important; border-color:rgba(244,63,94,.3)!important; }
        /* Welcome banner on visitor dashboard */
        html:not(.dark) .from-amber-900\/30     { --tw-gradient-from:rgba(254,243,199,.7)!important; }
        html:not(.dark) .to-slate-900           { --tw-gradient-to:#ffffff!important; }
        /* Hover states on cards */
        html:not(.dark) .hover\:border-amber-600\/50:hover { border-color:rgba(217,119,6,.35)!important; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen flex flex-col font-sans">

    @include('partials.public-top-nav')

    {{-- Fil d'Ariane / titre de page --}}
    @hasSection('page-title')
    <div class="page-title-bar border-b border-white/6 bg-white/2 pt-24 sm:pt-28 pb-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <h1 class="text-white text-xl font-bold font-plus">@yield('page-title')</h1>
        </div>
    </div>
    @else
    <div class="pt-20 sm:pt-24"></div>
    @endif

    {{-- Flash messages --}}
    <div class="max-w-5xl mx-auto w-full px-4 sm:px-6 mt-4">
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
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 py-6 pb-14">
        @yield('content')
    </main>

    @include('partials.homepage-footer')

    @stack('scripts')
</body>
</html>
