<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Connexion — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    @include('partials.theme-light-bridge')
    <style>
        html:not(.dark) .bg-slate-800\/60 { background-color:rgba(0,0,0,0.04) !important; }
        html:not(.dark) .border-slate-600 { border-color:#d6d0c5 !important; }
        html:not(.dark) .placeholder-slate-500::placeholder { color:#9e9b90 !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Retour accueil --}}
        <div class="mb-4">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-slate-500 hover:bg-slate-700 hover:text-white">
                <i class="fas fa-arrow-left text-xs"></i>
                Retour à l'accueil
            </a>
        </div>

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            @if(!empty($siteBrand['logo_url']))
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/5 border border-slate-600 mb-4 overflow-hidden p-1">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-500 mb-4">
                    <i class="fas fa-gem text-white text-2xl"></i>
                </div>
            @endif
            <h1 class="text-3xl font-bold text-amber-400 tracking-wide">{{ $siteBrand['site_name'] }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}</p>
        </div>

        {{-- Card --}}
        <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-8">
            <h2 class="text-white text-xl font-semibold mb-6">Connexion à votre espace</h2>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm flex items-start gap-2">
                    <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded-lg text-green-300 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-slate-300 text-sm font-medium mb-1.5">
                        Adresse e-mail
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500">
                            <i class="fas fa-envelope text-sm"></i>
                        </span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="vous@exemple.ci"
                            class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-slate-300 text-sm font-medium">
                            Mot de passe
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs text-amber-400 hover:text-amber-300 transition">
                            Mot de passe oublié ?
                        </a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                        >
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox"
                        class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-amber-500 focus:ring-amber-500">
                    <label for="remember" class="text-slate-400 text-sm">Se souvenir de moi</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2.5 rounded-lg transition duration-150 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            &copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés
        </p>
    </div>

</body>
</html>
