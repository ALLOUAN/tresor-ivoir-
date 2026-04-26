<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Vérifiez votre e-mail — {{ $siteBrand['site_name'] }}</title>
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
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-white/10 p-8 shadow-2xl text-center" style="background:#141410">

            {{-- Icône --}}
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gold-500/10 border border-gold-500/20 mb-5">
                <i class="fas fa-envelope-circle-check text-gold-400 text-2xl"></i>
            </div>

            <h2 class="text-white text-xl font-semibold mb-2">Vérifiez votre e-mail</h2>
            <p class="text-gray-400 text-sm leading-relaxed mb-6">
                Un lien de vérification a été envoyé à <span class="text-white font-medium">{{ auth()->user()->email }}</span>.
                Cliquez sur ce lien pour activer votre compte.
            </p>

            {{-- Message succès renvoi --}}
            @if(session('status'))
                <div class="mb-5 p-3 rounded-xl border border-green-500/30 bg-green-500/10 text-green-300 text-sm flex items-start gap-2 text-left">
                    <i class="fas fa-circle-check mt-0.5 shrink-0 text-green-400"></i>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            {{-- Étapes --}}
            <div class="bg-white/3 rounded-xl p-4 mb-6 text-left space-y-3">
                <div class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-gold-500/20 text-gold-400 text-xs flex items-center justify-center shrink-0 mt-0.5 font-bold">1</span>
                    <p class="text-gray-400 text-xs leading-relaxed">Ouvrez votre boîte e-mail et cherchez un message de <span class="text-gray-300">{{ $siteBrand['site_name'] }}</span>.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-gold-500/20 text-gold-400 text-xs flex items-center justify-center shrink-0 mt-0.5 font-bold">2</span>
                    <p class="text-gray-400 text-xs leading-relaxed">Cliquez sur le bouton <span class="text-gray-300">« Vérifier mon e-mail »</span> dans le message.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-gold-500/20 text-gold-400 text-xs flex items-center justify-center shrink-0 mt-0.5 font-bold">3</span>
                    <p class="text-gray-400 text-xs leading-relaxed">Vous serez redirigé vers votre espace personnel.</p>
                </div>
            </div>

            {{-- Renvoyer le lien --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl border border-gold-500/30 text-gold-300 hover:text-gold-200 hover:border-gold-400/60 hover:bg-gold-500/5 transition text-sm font-medium mb-3">
                    <i class="fas fa-rotate-right mr-1.5 text-xs"></i>
                    Renvoyer le lien de vérification
                </button>
            </form>

            {{-- Déconnexion --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-gray-600 hover:text-gray-400 transition">
                    Utiliser un autre compte
                </button>
            </form>

        </div>

        <p class="text-center text-gray-700 text-xs mt-6">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</p>
    </div>

</body>
</html>
