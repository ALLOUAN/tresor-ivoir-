<!DOCTYPE html>
<html lang="fr" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Réinitialiser le mot de passe — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { 400: '#fa9a3c', 500: '#f2790f', 600: '#d4630a' },
                        dark: { 800: '#e9e5d9', 900: '#e9e5d9' },
                    }
                }
            }
        }
    </script>
    @include('partials.theme-light-bridge')
    <style>
        html:not(.dark) [style*="background:#e9e5d9"] { background:#e9e5d9 !important; }
        html:not(.dark) [style*="background:#e9e5d9"] { background:#e9e5d9 !important; }
        html:not(.dark) input[style*="background:#e9e5d9"] { background:#e9e5d9 !important; color:#1c1915 !important; border-color:#c2b89e !important; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 font-sans" style="background:#e9e5d9">
    @include('partials.page-background')

    <div class="w-full max-w-md py-8">

        {{-- Logo --}}
        <div class="text-center mb-8">
            @if(!empty($siteBrand['logo_url']))
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/5 border border-white/10 mb-4 overflow-hidden p-1">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 mb-4">
                    <i class="fas fa-gem text-black text-2xl"></i>
                </div>
            @endif
            <h1 class="text-2xl font-bold font-serif" style="color:#fa9a3c">{{ $siteBrand['site_name'] }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}</p>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-white/10 p-8 shadow-2xl" style="background:#e9e5d9">

            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gold-500/15 flex items-center justify-center">
                    <i class="fas fa-key text-gold-400"></i>
                </div>
                <div>
                    <h2 class="text-white text-lg font-semibold">Nouveau mot de passe</h2>
                    <p class="text-gray-500 text-xs">Choisissez un mot de passe sécurisé</p>
                </div>
            </div>

            {{-- Erreurs --}}
            @if($errors->any())
                <div class="mb-5 p-3 rounded-lg border border-red-500/30 bg-red-500/10 text-red-300 text-sm flex items-start gap-2">
                    <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <ul class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                {{-- E-mail --}}
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Adresse e-mail <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-at absolute left-3 top-1/2 -translate-y-1/2 text-orange-500/40 text-xs"></i>
                        <input id="email" name="email" type="email" required
                               value="{{ old('email', request('email')) }}" placeholder="vous@exemple.ci"
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-orange-500/30 @error('email') ring-1 ring-red-500/60 @enderror"
                               style="background:#e9e5d9; border: 1px solid rgba(255,255,255,0.1)">
                    </div>
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Nouveau mot de passe --}}
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Nouveau mot de passe <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-orange-500/40 text-xs"></i>
                        <input id="password" name="password" type="password" required
                               placeholder="8 caractères minimum"
                               class="w-full pl-9 pr-10 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-orange-500/30 @error('password') ring-1 ring-red-500/60 @enderror"
                               style="background:#e9e5d9; border: 1px solid rgba(255,255,255,0.1)">
                        <button type="button" onclick="togglePwd('password', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-400 transition text-xs">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Confirmer le mot de passe <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock-keyhole absolute left-3 top-1/2 -translate-y-1/2 text-orange-500/40 text-xs"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               placeholder="Répétez le mot de passe"
                               class="w-full pl-9 pr-10 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-orange-500/30"
                               style="background:#e9e5d9; border: 1px solid rgba(255,255,255,0.1)">
                        <button type="button" onclick="togglePwd('password_confirmation', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-400 transition text-xs">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-xl font-bold text-sm text-black transition-all duration-200 flex items-center justify-center gap-2 mt-2"
                        style="background: #f2790f; box-shadow: 0 4px 20px rgba(242, 121, 15,0.3)">
                    <i class="fas fa-check text-xs"></i>
                    Réinitialiser mon mot de passe
                </button>
            </form>

        </div>

        <p class="text-center text-sm text-gray-600 mt-6">
            <a href="{{ route('login') }}" class="text-orange-400 hover:text-orange-300 font-medium transition inline-flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-xs"></i>Retour à la connexion
            </a>
        </p>
        <p class="text-center text-gray-700 text-xs mt-3">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</p>
    </div>

    <script>
        function togglePwd(id, btn) {
            const input = document.getElementById(id);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>
</html>
