<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Vérification — {{ $siteBrand['site_name'] ?? 'Administration' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
               background: #F3F8F5; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: #0E3B2A; }
        .card { width: 100%; max-width: 420px; background: #fff; border: 1px solid #D8E9E0; border-radius: 18px; padding: 32px 28px;
                box-shadow: 0 20px 50px rgb(14 59 42 / .10); }
        .icon { width: 52px; height: 52px; border-radius: 14px; background: #00874F; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        h1 { font-size: 22px; margin: 18px 0 6px; }
        p { margin: 0; color: #557466; font-size: 14px; line-height: 1.55; }
        .code { width: 100%; margin-top: 22px; padding: 14px; font-size: 28px; letter-spacing: 10px; text-align: center; font-weight: 700;
                border: 1px solid #D8E9E0; border-radius: 12px; outline: none; }
        .code:focus { border-color: #00874F; box-shadow: 0 0 0 3px rgb(0 135 79 / .15); }
        .btn { width: 100%; margin-top: 14px; padding: 14px; border: 0; border-radius: 12px; background: #EB6E00; color: #fff; font-weight: 700; font-size: 15px; cursor: pointer; }
        .btn:hover { background: #EB6E00; }
        .link { background: none; border: 0; color: #00874F; font-weight: 600; cursor: pointer; font-size: 13px; padding: 0; }
        .error { margin-top: 14px; padding: 10px 12px; border-radius: 10px; background: #fdecec; color: #a12b2b; font-size: 13px; }
        .ok { margin-top: 14px; padding: 10px 12px; border-radius: 10px; background: #F3F8F5; color: #00874F; font-size: 13px; }
        .row { display: flex; justify-content: space-between; align-items: center; margin-top: 18px; gap: 12px; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fas fa-shield-halved"></i></div>
        <h1>Vérification en deux étapes</h1>
        <p>Nous avons envoyé un code à 6 chiffres à <strong>{{ $email }}</strong>. Saisissez-le pour accéder à l'administration.</p>

        @if(session('status'))<div class="ok">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('admin.two-factor.verify') }}">
            @csrf
            <input class="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="\d{6}" required autofocus placeholder="••••••">
            <button class="btn" type="submit">Valider</button>
        </form>

        <div class="row">
            <form method="POST" action="{{ route('admin.two-factor.resend') }}">
                @csrf
                <button class="link" type="submit"><i class="fas fa-rotate-right"></i> Renvoyer un code</button>
            </form>
            <a class="link" href="{{ route('login') }}" style="text-decoration:none;">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>
