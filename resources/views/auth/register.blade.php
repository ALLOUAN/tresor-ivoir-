<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Créer un compte — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        sans:  ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        gold: { 400: '#f5b942', 500: '#e8a020', 600: '#c4811a' },
                        dark: { 800: '#141410', 900: '#0d0d0b' },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        .role-card input[type="radio"]:checked + label {
            border-color: rgba(232,160,32,0.6);
            background: rgba(232,160,32,0.08);
            color: #fde68a;
        }
        .role-card input[type="radio"]:checked + label .role-icon {
            background: rgba(232,160,32,0.15);
            color: #f5b942;
        }
    </style>
</head>
<body class="min-h-screen bg-dark-900 flex items-center justify-center p-4 font-sans" style="background:#0d0d0b">

    <div class="w-full max-w-lg py-8">

        {{-- Logo --}}
        <div class="text-center mb-8">
            @if(!empty($siteBrand['logo_url']))
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/5 border border-white/10 mb-4 overflow-hidden p-1">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-400 to-yellow-600 mb-4">
                    <i class="fas fa-gem text-black text-2xl"></i>
                </div>
            @endif
            <h1 class="text-2xl font-bold font-serif" style="color:#f5b942">{{ $siteBrand['site_name'] }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}</p>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-white/10 p-8 shadow-2xl" style="background:#141410">
            <h2 class="text-white text-xl font-semibold mb-1">Créer votre compte</h2>
            <p class="text-gray-500 text-sm mb-6">Rejoignez la communauté Trésor Ivoire.</p>

            @if(session('info'))
                <div class="mb-5 p-3 rounded-lg border border-sky-500/30 bg-sky-500/10 text-sky-200 text-sm">{{ session('info') }}</div>
            @endif

            {{-- Erreurs globales --}}
            @if ($errors->any())
                <div class="mb-5 p-3 rounded-lg border border-red-500/30 bg-red-500/10 text-red-300 text-sm flex items-start gap-2">
                    <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}{{ request('plan') ? '?plan='.request('plan') : '' }}" class="space-y-5">
                @csrf

                {{-- Choix du type de compte --}}
                <div>
                    <p class="text-xs font-medium text-gray-400 mb-3">Type de compte <span class="text-red-400">*</span></p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach([
                            ['value' => 'visitor',  'icon' => 'fa-user',  'label' => 'Visiteur',     'desc' => 'Parcourez et commentez'],
                            ['value' => 'provider', 'icon' => 'fa-store', 'label' => 'Prestataire',  'desc' => 'Référencez votre activité'],
                        ] as $opt)
                        <div class="role-card">
                            <input type="radio" name="role" id="role_{{ $opt['value'] }}" value="{{ $opt['value'] }}"
                                   {{ old('role', request('role', 'visitor')) === $opt['value'] ? 'checked' : '' }} class="sr-only">
                            <label for="role_{{ $opt['value'] }}"
                                   class="flex flex-col items-center gap-2 p-4 rounded-xl border border-white/10 cursor-pointer transition-all duration-200 text-gray-400 hover:border-white/20">
                                <div class="role-icon w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center transition-all">
                                    <i class="fas {{ $opt['icon'] }} text-sm"></i>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-semibold">{{ $opt['label'] }}</p>
                                    <p class="text-xs text-gray-600 mt-0.5">{{ $opt['desc'] }}</p>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('role')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Prénom + Nom --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs font-medium text-gray-400 mb-1.5">Prénom <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                            <input id="first_name" name="first_name" type="text" required maxlength="80"
                                   value="{{ old('first_name') }}" autocomplete="given-name" placeholder="Jean"
                                   class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-yellow-500/30 @error('first_name') border-red-500/60 @enderror"
                                   style="background:#0d0d0b; border: 1px solid rgba(255,255,255,0.1)">
                        </div>
                        @error('first_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-medium text-gray-400 mb-1.5">Nom <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                            <input id="last_name" name="last_name" type="text" required maxlength="80"
                                   value="{{ old('last_name') }}" autocomplete="family-name" placeholder="Kouassi"
                                   class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-yellow-500/30 @error('last_name') border-red-500/60 @enderror"
                                   style="background:#0d0d0b; border: 1px solid rgba(255,255,255,0.1)">
                        </div>
                        @error('last_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- E-mail --}}
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-400 mb-1.5">Adresse e-mail <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-at absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="email" name="email" type="email" required maxlength="255"
                               value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.ci"
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-yellow-500/30 @error('email') border-red-500/60 @enderror"
                               style="background:#0d0d0b; border: 1px solid rgba(255,255,255,0.1)">
                    </div>
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Téléphone (facultatif visiteur, recommandé prestataire) --}}
                <div>
                    <label for="phone" class="block text-xs font-medium text-gray-400 mb-1.5">Téléphone <span class="text-gray-600">(facultatif)</span></label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="phone" name="phone" type="tel" maxlength="20"
                               value="{{ old('phone') }}" autocomplete="tel" placeholder="+225 07 00 00 00 00"
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-yellow-500/30 @error('phone') border-red-500/60 @enderror"
                               style="background:#0d0d0b; border: 1px solid rgba(255,255,255,0.1)">
                    </div>
                    @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Mot de passe --}}
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-400 mb-1.5">Mot de passe <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="password" name="password" type="password" required
                               autocomplete="new-password" placeholder="8 caractères minimum"
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-yellow-500/30 @error('password') border-red-500/60 @enderror"
                               style="background:#0d0d0b; border: 1px solid rgba(255,255,255,0.1)">
                    </div>
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-gray-400 mb-1.5">Confirmer le mot de passe <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-lock-keyhole absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password" placeholder="Répétez le mot de passe"
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-yellow-500/30"
                               style="background:#0d0d0b; border: 1px solid rgba(255,255,255,0.1)">
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-3 rounded-xl font-bold text-sm text-black transition-all duration-200 flex items-center justify-center gap-2 mt-2"
                        style="background: linear-gradient(135deg,#f5b942,#e8a020); box-shadow: 0 4px 20px rgba(232,160,32,0.3)">
                    <i class="fas fa-user-plus text-xs"></i>
                    Créer mon compte
                </button>
            </form>
        </div>

        {{-- Lien connexion --}}
        <p class="text-center text-sm text-gray-600 mt-6">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="text-yellow-400 hover:text-yellow-300 font-medium transition">Se connecter</a>
        </p>
        <p class="text-center text-gray-700 text-xs mt-3">
            &copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var params = new URLSearchParams(window.location.search);
            if (params.get('role') === 'provider') {
                var r = document.getElementById('role_provider');
                var v = document.getElementById('role_visitor');
                if (r && v) { r.checked = true; v.checked = false; }
            }
        });
    </script>
</body>
</html>
