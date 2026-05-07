<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Mot de passe oublié — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
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
    @include('partials.theme-light-bridge')
    <style>
        html:not(.dark) [style*="background:#141410"] { background:#ffffff !important; }
        html:not(.dark) [style*="background:#0d0d0b"] { background:#f8f5ee !important; }
        html:not(.dark) input[style*="background:#0d0d0b"] { background:#ffffff !important; color:#1c1915 !important; border-color:#d6d0c5 !important; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 font-sans" style="background:#0d0d0b">

    <div class="w-full max-w-md py-8">

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

            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gold-500/15 flex items-center justify-center">
                    <i class="fas fa-lock-open text-gold-400"></i>
                </div>
                <div>
                    <h2 class="text-white text-lg font-semibold">Mot de passe oublié</h2>
                    <p class="text-gray-500 text-xs">Recevez un lien de réinitialisation par e-mail</p>
                </div>
            </div>

            {{-- Message succès --}}
            @if(session('status'))
                <div class="mb-5 p-4 rounded-xl border border-green-500/30 bg-green-500/10 text-green-300 text-sm flex items-start gap-2">
                    <i class="fas fa-circle-check mt-0.5 shrink-0 text-green-400"></i>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

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

            @if(!session('status'))
            <p class="text-gray-400 text-sm leading-relaxed mb-6">
                Saisissez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </p>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-5">
                    <label for="email" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Adresse e-mail <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-at absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500/40 text-xs"></i>
                        <input id="email" name="email" type="email" required autofocus
                               value="{{ old('email') }}" placeholder="vous@exemple.ci"
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-white placeholder:text-gray-600 transition focus:outline-none focus:ring-2 focus:ring-yellow-500/30 @error('email') ring-1 ring-red-500/60 @enderror"
                               style="background:#0d0d0b; border: 1px solid rgba(255,255,255,0.1)">
                    </div>
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-xl font-bold text-sm text-black transition-all duration-200 flex items-center justify-center gap-2"
                        style="background: linear-gradient(135deg,#f5b942,#e8a020); box-shadow: 0 4px 20px rgba(232,160,32,0.3)">
                    <i class="fas fa-paper-plane text-xs"></i>
                    Envoyer le lien de réinitialisation
                </button>
            </form>
            @endif

        </div>

        <p class="text-center text-sm text-gray-600 mt-6">
            <a href="{{ route('login') }}" class="text-yellow-400 hover:text-yellow-300 font-medium transition inline-flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-xs"></i>Retour à la connexion
            </a>
        </p>
        <p class="text-center text-gray-700 text-xs mt-3">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</p>
    </div>

</body>
</html>
