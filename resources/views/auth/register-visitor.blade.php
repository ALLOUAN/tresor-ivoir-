<!DOCTYPE html>
<html lang="fr" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Créer un compte — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amber: { 400: '#fa9a3c', 500: '#f2790f', 600: '#9f4709' }
                    }
                }
            }
        }
    </script>
    @include('partials.theme-light-bridge')
    <style>
        html:not(.dark) .bg-slate-800\/60 { background-color:rgba(0,0,0,0.04) !important; }
        html:not(.dark) .border-slate-600 { border-color:#c2b89e !important; }
        html:not(.dark) .placeholder-slate-500::placeholder { color:#665f52 !important; }
    </style>
</head>
<body class="min-h-screen bg-green-950 flex flex-col">
    @include('partials.page-background')

@include('partials.public-top-nav')

    <div class="flex-1 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="mb-4">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-slate-500 hover:bg-slate-700 hover:text-white">
                <i class="fas fa-arrow-left text-xs"></i>
                Retour à l'accueil
            </a>
        </div>

        <div class="text-center mb-8">
            @if(!empty($siteBrand['logo_url']))
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/5 border border-slate-600 mb-4 overflow-hidden p-1">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-500 mb-4">
                    <i class="fas fa-gem text-white text-2xl"></i>
                </div>
            @endif
            <h1 class="text-3xl font-bold text-orange-400 tracking-wide">{{ $siteBrand['site_name'] }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}</p>
        </div>

        <div class="bg-green-900 border border-slate-700 rounded-2xl shadow-2xl p-8">
            <h2 class="text-white text-xl font-semibold mb-1">Créer mon compte</h2>
            <p class="text-slate-500 text-sm mb-6">Réservez vos hôtels et résidences en quelques clics.</p>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm flex items-start gap-2">
                    <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="visitor">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="first_name" class="block text-slate-300 text-sm font-medium mb-1.5">Prénom</label>
                        <input id="first_name" name="first_name" type="text" required maxlength="80"
                               value="{{ old('first_name') }}" autocomplete="given-name" placeholder="Jean"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('first_name') border-red-500 @enderror">
                    </div>
                    <div>
                        <label for="last_name" class="block text-slate-300 text-sm font-medium mb-1.5">Nom</label>
                        <input id="last_name" name="last_name" type="text" required maxlength="80"
                               value="{{ old('last_name') }}" autocomplete="family-name" placeholder="Kouassi"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('last_name') border-red-500 @enderror">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-slate-300 text-sm font-medium mb-1.5">Adresse e-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500">
                            <i class="fas fa-envelope text-sm"></i>
                        </span>
                        <input id="email" name="email" type="email" required maxlength="255"
                               value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.ci"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('email') border-red-500 @enderror">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-slate-300 text-sm font-medium mb-1.5">Téléphone <span class="text-slate-600">(facultatif)</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500">
                            <i class="fas fa-phone text-sm"></i>
                        </span>
                        <input id="phone" name="phone" type="tel" maxlength="20"
                               value="{{ old('phone') }}" autocomplete="tel" placeholder="+225 07 00 00 00 00"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('phone') border-red-500 @enderror">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-slate-300 text-sm font-medium mb-1.5">Mot de passe</label>
                        <input id="password" name="password" type="password" required
                               autocomplete="new-password" placeholder="8 caractères min."
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('password') border-red-500 @enderror">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-slate-300 text-sm font-medium mb-1.5">Confirmation</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password" placeholder="Répétez"
                               class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>
                </div>

                <div class="flex items-start gap-2.5 pt-1">
                    <input id="terms" name="terms" type="checkbox" value="1" required
                           class="mt-0.5 w-4 h-4 rounded bg-slate-700 border-slate-600 text-orange-500 focus:ring-orange-500 @error('terms') border-red-500 @enderror">
                    <label for="terms" class="text-slate-400 text-xs leading-relaxed">
                        J'accepte les
                        <a href="{{ route('information.show', 'conditions-generales-utilisation') }}" target="_blank" class="text-orange-400 hover:text-orange-300 transition">conditions générales d'utilisation</a>
                        de {{ $siteBrand['site_name'] }}.
                    </label>
                </div>

                <button type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition duration-150 flex items-center justify-center gap-2 text-sm mt-2">
                    <i class="fas fa-user-plus"></i>
                    Créer mon compte
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="text-orange-400 hover:text-orange-300 font-medium transition">Se connecter</a>
        </p>
        <p class="text-center text-slate-600 text-xs mt-3">
            &copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés
        </p>
    </div>
    </div>

@include('partials.homepage-footer')

</body>
</html>
