<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation annulée</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1c1915; background:#f6f3ed; margin:0; padding:24px 0;">
    <table role="presentation" width="100%" style="max-width:560px; margin:0 auto; background:#ffffff; border:1px solid rgba(0,0,0,.08); border-radius:14px; overflow:hidden;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background:#181511; padding:22px 26px;">
                @if(!empty($branding['logo_url']))
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] }}" style="height:34px; display:block;">
                @else
                    <span style="color:#fff; font-weight:bold; font-size:16px;">{{ $branding['site_name'] }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:26px;">
                <h2 style="margin:0 0 14px;">Votre réservation a été annulée</h2>
                <p>Bonjour {{ $reservation->full_name }},</p>
                <p>L'établissement <strong>{{ $reservation->accommodation_name }}</strong> a annulé la réservation suivante :</p>
                <ul>
                    <li>Chambre : {{ $reservation->room_name }}</li>
                    <li>Séjour : {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }}</li>
                    <li>Référence : {{ $reservation->reference }}</li>
                </ul>

                @if($depositPaid)
                    <p style="padding:12px 14px; background:#fff3e3; border-radius:8px;">
                        Votre acompte de <strong>{{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</strong> vous sera remboursé.
                        Notre équipe a été prévenue et vous contactera pour le remboursement. Le montant n'est pas versé à l'établissement.
                    </p>
                @endif

                <p>Nous vous prions de nous excuser pour ce désagrément. Vous pouvez choisir un autre hébergement sur notre site.</p>
                <p>Pour toute question, répondez simplement à cet e-mail en indiquant la référence {{ $reservation->reference }}.</p>
            </td>
        </tr>
    </table>
</body>
</html>
