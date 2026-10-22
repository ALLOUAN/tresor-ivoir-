<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Votre avis</title></head>
<body style="font-family: Arial, sans-serif; color: #1c1915; background:#f6f3ed; margin:0; padding:24px 0;">
    <table role="presentation" width="100%" style="max-width:560px; margin:0 auto; background:#ffffff; border:1px solid rgba(0,0,0,.08); border-radius:14px; overflow:hidden;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background:#31621c; padding:22px 26px;">
                @if(!empty($branding['logo_url']))
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] }}" style="height:34px; display:block;">
                @else
                    <span style="color:#fff; font-weight:bold; font-size:16px;">{{ $branding['site_name'] }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:26px;">
                <h2 style="margin:0 0 14px;">Comment s'est passé votre moment ?</h2>
                <p>Bonjour {{ $clientName }},</p>
                <p>Vous êtes passé(e) chez <strong>{{ $placeName }}</strong> {{ $when }}. Votre avis aide les autres voyageurs à choisir, et l'établissement à s'améliorer.</p>
                <p style="text-align:center; margin:26px 0;">
                    @foreach([1, 2, 3, 4, 5] as $star)
                        <a href="{{ $reviewUrl }}&note={{ $star }}#laisser-un-avis" style="font-size:30px; color:#febf48; text-decoration:none; padding:0 3px;">★</a>
                    @endforeach
                </p>
                <p style="text-align:center;">
                    <a href="{{ $reviewUrl }}#laisser-un-avis" style="display:inline-block; background:#ef7c2b; color:#ffffff; text-decoration:none; font-weight:bold; padding:12px 24px; border-radius:10px;">Laisser mon avis</a>
                </p>
                <p style="color:#8a7f6b; font-size:13px; margin-top:22px;">Cela prend moins d'une minute. Votre avis portera le badge « Séjour vérifié ».</p>
            </td>
        </tr>
    </table>
</body>
</html>
