<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Code de connexion</title></head>
<body style="font-family: Arial, sans-serif; color: #0E3B2A; background:#F3F8F5; margin:0; padding:24px 0;">
    <table role="presentation" width="100%" style="max-width:520px; margin:0 auto; background:#ffffff; border:1px solid rgba(14,59,42,.08); border-radius:14px; overflow:hidden;" cellpadding="0" cellspacing="0">
        <tr><td style="background:#00874F; padding:20px 26px; color:#fff; font-weight:bold;">{{ $branding['site_name'] }} — Administration</td></tr>
        <tr>
            <td style="padding:26px;">
                <p>Bonjour {{ $user->first_name }},</p>
                <p>Voici votre code de connexion à l'administration :</p>
                <p style="font-size:32px; font-weight:bold; letter-spacing:8px; text-align:center; background:#F3F8F5; border-radius:10px; padding:16px 0; margin:18px 0;">{{ $code }}</p>
                <p>Il est valable {{ $minutes }} minutes. Ne le communiquez à personne.</p>
                <p style="color:#557466; font-size:13px;">Si vous n'êtes pas à l'origine de cette connexion, changez immédiatement votre mot de passe : quelqu'un le connaît.</p>
            </td>
        </tr>
    </table>
</body>
</html>
