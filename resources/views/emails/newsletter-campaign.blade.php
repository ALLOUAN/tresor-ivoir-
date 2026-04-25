<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#0f172a;font-family:system-ui,-apple-system,sans-serif;line-height:1.6;color:#e2e8f0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" style="max-width:560px;background:#1e293b;border-radius:12px;overflow:hidden;border:1px solid #334155;">
                <tr>
                    <td style="padding:24px 28px;">
                        <div style="font-size:15px;color:#cbd5e1;">
                            {!! $bodyHtml !!}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px 24px;border-top:1px solid #334155;font-size:11px;color:#64748b;">
                        <p style="margin:0 0 8px;">Vous recevez ce message car vous êtes inscrit·e à la newsletter.</p>
                        <a href="{{ $unsubscribeUrl }}" style="color:#fbbf24;text-decoration:underline;">Se désinscrire</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
