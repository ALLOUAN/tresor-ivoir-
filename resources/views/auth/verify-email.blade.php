<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Vérifiez votre e-mail — {{ $siteBrand['site_name'] }}</title>
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
        * { box-sizing: border-box; }
        body {
            background:
                radial-gradient(900px 560px at 8% 10%, rgba(232,160,32,0.15), transparent 62%),
                radial-gradient(760px 460px at 92% 88%, rgba(99,102,241,0.11), transparent 64%),
                #0a0907;
        }
        .verify-shell {
            position: relative;
        }
        .verify-shell::before {
            content: "";
            position: absolute;
            inset: -16px;
            border-radius: 30px;
            background: linear-gradient(145deg, rgba(232,160,32,0.2), rgba(255,255,255,0.03), rgba(232,160,32,0.08));
            filter: blur(16px);
            opacity: .45;
            pointer-events: none;
        }
        .verify-card {
            position: relative;
            border: 1px solid rgba(255,255,255,0.12);
            background:
                radial-gradient(110% 130% at 0% 0%, rgba(232,160,32,0.14), rgba(232,160,32,0.04) 45%, rgba(13,11,9,0.92) 100%),
                linear-gradient(145deg, rgba(20,18,14,0.9), rgba(13,11,9,0.94));
            box-shadow: 0 24px 56px rgba(0,0,0,0.42), inset 0 1px 0 rgba(255,255,255,0.05);
            backdrop-filter: blur(8px);
        }
        .verify-badge {
            background: linear-gradient(135deg,#f5b942,#e8a020);
            color: #111827;
            box-shadow: 0 10px 24px rgba(232,160,32,0.34);
        }
        .verify-input {
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(0,0,0,0.26);
            color: #fff;
        }
        .verify-input::placeholder { color: #6b7280; }
        .verify-input:focus {
            outline: none;
            border-color: rgba(232,160,32,0.55);
            box-shadow: 0 0 0 4px rgba(232,160,32,0.14);
        }
        .verify-step {
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
        }
        html:not(.dark) body {
            background:
                radial-gradient(900px 560px at 8% 10%, rgba(232,160,32,0.08), transparent 62%),
                radial-gradient(760px 460px at 92% 88%, rgba(99,102,241,0.06), transparent 64%),
                #f8f5ee;
        }
        html:not(.dark) .verify-card {
            border-color: rgba(0,0,0,0.1);
            background:
                radial-gradient(110% 130% at 0% 0%, rgba(232,160,32,0.08), rgba(232,160,32,0.03) 45%, rgba(255,255,255,0.95) 100%),
                linear-gradient(145deg, rgba(255,255,255,0.98), rgba(247,243,235,0.98));
            box-shadow: 0 16px 36px rgba(0,0,0,0.08);
        }
        html:not(.dark) .verify-step { border-color: rgba(0,0,0,0.08); background: rgba(0,0,0,0.02); }
        html:not(.dark) .verify-input { border-color: rgba(0,0,0,0.18); background: #ffffff; color: #1c1915; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 font-sans">

    <div class="verify-shell w-full max-w-md py-8">

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
        <div class="verify-card rounded-3xl p-8 text-center">

            {{-- Icône --}}
            <div class="verify-badge inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-5">
                <i class="fas fa-envelope-circle-check text-black text-2xl"></i>
            </div>

            <h2 class="text-white text-xl font-semibold mb-2">Vérification du code</h2>
            <p class="text-gray-300 text-sm leading-relaxed mb-6">
                Un code de vérification a été envoyé à <span class="text-white font-medium">{{ auth()->user()->email }}</span>.
                Saisissez ce code pour activer votre compte.
            </p>

            {{-- Message succès renvoi --}}
            @if(session('status'))
                <div class="mb-5 p-3 rounded-xl border border-green-500/30 bg-green-500/10 text-green-300 text-sm flex items-start gap-2 text-left">
                    <i class="fas fa-circle-check mt-0.5 shrink-0 text-green-400"></i>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            {{-- Saisie du code OTP --}}
            <form method="POST" action="{{ route('verification.code') }}" class="mb-5 text-left">
                @csrf
                <label for="verification_code" class="block text-xs font-medium text-gray-300 mb-1.5">
                    Entrez le code à 6 chiffres
                </label>
                <input
                    id="verification_code"
                    name="verification_code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    required
                    value="{{ old('verification_code') }}"
                    placeholder="000000"
                    class="verify-input w-full px-3 py-2.5 rounded-xl tracking-[0.35em] text-center text-lg"
                >
                @error('verification_code')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="w-full py-2.5 rounded-xl mt-3 font-semibold text-sm text-black transition-all duration-200 hover:-translate-y-0.5"
                        style="background: linear-gradient(135deg,#f5b942,#e8a020); box-shadow: 0 4px 20px rgba(232,160,32,0.3)">
                    <i class="fas fa-check mr-1.5 text-xs"></i>
                    Vérifier le code
                </button>
            </form>

            {{-- Étapes --}}
            <div class="verify-step rounded-xl p-4 mb-6 text-left space-y-3">
                <div class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-gold-500/20 text-gold-400 text-xs flex items-center justify-center shrink-0 mt-0.5 font-bold">1</span>
                    <p class="text-gray-300 text-xs leading-relaxed">Ouvrez votre boîte e-mail et cherchez le code envoyé par <span class="text-gray-100">{{ $siteBrand['site_name'] }}</span>.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-gold-500/20 text-gold-400 text-xs flex items-center justify-center shrink-0 mt-0.5 font-bold">2</span>
                    <p class="text-gray-300 text-xs leading-relaxed">Saisissez le code reçu dans le champ ci-dessus.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-gold-500/20 text-gold-400 text-xs flex items-center justify-center shrink-0 mt-0.5 font-bold">3</span>
                    <p class="text-gray-300 text-xs leading-relaxed">Vous serez redirigé vers votre espace personnel.</p>
                </div>
            </div>

            {{-- Renvoyer le code --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl border border-gold-400/70 bg-gold-500/10 text-gold-100 hover:text-white hover:border-gold-300 hover:bg-gold-500/20 transition text-sm font-semibold mb-3">
                    <i class="fas fa-rotate-right mr-1.5 text-xs"></i>
                    Renvoyer le code de vérification
                </button>
            </form>

            {{-- Déconnexion --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-gray-300 hover:text-gray-100 transition">
                    Utiliser un autre compte
                </button>
            </form>

        </div>

        <p class="text-center text-gray-400 text-xs mt-6">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</p>
    </div>

</body>
</html>
