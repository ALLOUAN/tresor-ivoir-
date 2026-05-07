<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>@yield('title', 'Finaliser le paiement') — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            900: '#0d0d0b',
                            800: '#12110f',
                        },
                        gold: {
                            500: '#e8a020',
                            400: '#f3b84a',
                            300: '#f7cf7a',
                            200: '#fde3a7',
                        },
                    },
                },
            },
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background-color:#0d0d0b; color:#fff; }
        html:not(.dark) body            { background-color:#f8f5ee!important; color:#1c1915!important; }
        html:not(.dark) .text-white     { color:#1c1915!important; }
        html:not(.dark) .text-gray-400  { color:#6b6860!important; }
        html:not(.dark) .text-gray-300  { color:#44413a!important; }
        html:not(.dark) .bg-dark-900    { background-color:#f8f5ee!important; }
        html:not(.dark) .bg-dark-800\/70 { background-color:rgba(240,236,228,0.7)!important; }
        html:not(.dark) .border-white\/10 { border-color:rgba(0,0,0,0.08)!important; }
        html:not(.dark) .bg-white\/5    { background-color:rgba(0,0,0,0.04)!important; }
        html:not(.dark) .hover\:bg-white\/10:hover { background-color:rgba(0,0,0,0.07)!important; }
        html:not(.dark) .hover\:border-white\/20:hover { border-color:rgba(0,0,0,0.12)!important; }
        html:not(.dark) .hover\:text-white:hover { color:#1c1915!important; }
        html:not(.dark) .bg-slate-900   { background-color:#ffffff!important; }
        html:not(.dark) .bg-slate-800   { background-color:#f4f0e8!important; }
        html:not(.dark) .border-slate-800 { border-color:#e8e3da!important; }
        html:not(.dark) .text-slate-400 { color:#6b6860!important; }
        html:not(.dark) .text-slate-300 { color:#44413a!important; }
    </style>
</head>
<body class="min-h-screen bg-dark-900 text-white">
    @include('partials.public-top-nav')

    <section class="border-b border-white/10 bg-dark-800/70">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/10 hover:text-white">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span class="hidden sm:inline">Accueil</span>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white">Finaliser votre abonnement</h1>
                    <p class="text-gray-400 text-sm">Dernière étape avant d'accéder à tous les contenus premium</p>
                </div>
            </div>
            <span class="hidden sm:inline-flex items-center gap-2 rounded-full border border-gold-500/35 bg-gold-500/10 px-3 py-1 text-xs font-semibold text-gold-200">
                <i class="fas fa-shield-halved text-[11px] text-gold-400"></i>
                Paiement 100% sécurisé
            </span>
        </div>
    </section>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        @if(session('status'))
            <div class="mb-4 rounded-lg border border-sky-500/40 bg-sky-500/10 px-4 py-3 text-sky-200 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-red-200 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-emerald-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.homepage-footer')

    @stack('scripts')
</body>
</html>
