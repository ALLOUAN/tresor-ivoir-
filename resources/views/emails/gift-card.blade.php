<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Bon cadeau</title></head>
<body style="font-family: Arial, sans-serif; color: #0E3B2A; background:#F3F8F5; margin:0; padding:24px 0;">
    <table role="presentation" width="100%" style="max-width:560px; margin:0 auto; background:#ffffff; border:1px solid rgba(14,59,42,.08); border-radius:14px; overflow:hidden;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background:#00874F; padding:22px 26px; color:#fff; font-weight:bold;">{{ $branding['site_name'] }} — Bon cadeau</td>
        </tr>
        <tr>
            <td style="padding:26px;">
                @if($audience === 'recipient')
                    <h2 style="margin:0 0 12px;">Bonjour {{ $card->recipient_name }}, un cadeau pour vous ! 🎁</h2>
                    <p><strong>{{ $card->buyer_name }}</strong> vous offre un bon de <strong>{{ number_format($card->amount_xof, 0, ',', ' ') }} FCFA</strong> à utiliser chez <strong>{{ $card->provider?->name }}</strong>.</p>
                    @if($card->message)
                        <p style="padding:12px 14px; background:#F3F8F5; border-radius:8px; font-style:italic;">« {{ $card->message }} »</p>
                    @endif
                @else
                    <h2 style="margin:0 0 12px;">Votre bon cadeau a été envoyé</h2>
                    <p>Merci {{ $card->buyer_name }} ! {{ $card->recipient_name }} a reçu par e-mail son bon de <strong>{{ number_format($card->amount_xof, 0, ',', ' ') }} FCFA</strong> chez <strong>{{ $card->provider?->name }}</strong>.</p>
                @endif

                <div style="text-align:center; border:2px dashed #EB6E00; border-radius:12px; padding:18px; margin:20px 0;">
                    <img src="{{ $qr }}" alt="QR du bon" width="160" height="160" style="display:block; margin:0 auto 10px;">
                    <p style="font-size:22px; font-weight:bold; letter-spacing:3px; margin:0;">{{ $card->formattedCode() }}</p>
                    <p style="color:#557466; font-size:13px; margin:6px 0 0;">Valable jusqu'au {{ $card->expires_at?->format('d/m/Y') }}</p>
                </div>

                @if($audience === 'recipient')
                    <p>Présentez ce code (ou ce QR) à l'établissement lors de votre visite. Pensez à réserver : la fiche de l'établissement est sur notre site.</p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
