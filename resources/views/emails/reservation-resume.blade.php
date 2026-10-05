<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Terminez votre réservation</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1c1915; background:#f6f3ed; margin:0; padding:24px 0;">
    <table role="presentation" width="100%" style="max-width:560px; margin:0 auto; background:#ffffff; border:1px solid rgba(0,0,0,.08); border-radius:14px; overflow:hidden;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background:linear-gradient(135deg,#1c1712,#14130f); padding:22px 26px;">
                @if(!empty($branding['logo_url']))
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] }}" style="height:34px; display:block;">
                @else
                    <span style="color:#fff; font-weight:bold; font-size:16px;">{{ $branding['site_name'] }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:26px;">
                <h2 style="margin:0 0 14px;">Votre réservation vous attend</h2>
                <p>Bonjour {{ $reservation->full_name }},</p>
                <p>
                    Vous avez commencé une réservation, mais il reste une dernière étape avant de la finaliser :
                    compléter votre <strong>fiche d'enregistrement voyageur</strong> (obligation réglementaire du
                    Ministère du Tourisme et des Loisirs), puis régler l'acompte.
                </p>
                <ul>
                    <li>Établissement : {{ $reservation->accommodation_name }}</li>
                    <li>Chambre : {{ $reservation->room_name }}</li>
                    <li>Séjour : {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }} ({{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }})</li>
                    <li>Acompte à régler : {{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</li>
                    <li>Référence : {{ $reservation->reference }}</li>
                </ul>

                <p style="background:#fdf6e8; border:1px solid rgba(194,94,10,.2); border-radius:8px; padding:12px 14px; font-size:13px;">
                    Votre chambre est provisoirement retenue pour ces dates. Passé un délai de quelques heures sans
                    finalisation, elle pourra être remise en vente.
                </p>

                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0;">
                    <tr>
                        <td>
                            <a href="{{ $guestRegistrationUrl }}" style="display:inline-block; padding:12px 20px; background:linear-gradient(135deg,#f4c65a,#f2790f,#c25e0a); color:#1b1408; font-weight:bold; text-decoration:none; border-radius:8px; font-size:13px;">
                                Terminer ma réservation
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size:12px; color:#8a7f6b;">Ce lien est personnel — ne le partagez pas.</p>
                <p>Merci pour votre confiance.</p>
            </td>
        </tr>
    </table>
</body>
</html>
