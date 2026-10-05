<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Acompte confirmé</title>
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
                <h2 style="margin:0 0 14px;">Votre acompte est confirmé</h2>
                <p>Bonjour {{ $reservation->full_name }},</p>
                <p>Nous avons bien reçu le paiement de votre acompte pour la réservation suivante :</p>
                <ul>
                    <li>Établissement : {{ $reservation->accommodation_name }}</li>
                    <li>Chambre : {{ $reservation->room_name }}</li>
                    <li>Séjour : {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }} ({{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }})</li>
                    <li>Voyageurs : {{ $reservation->guests_count }} pers. · {{ $reservation->rooms_count }} chambre{{ $reservation->rooms_count > 1 ? 's' : '' }}</li>
                    <li>Montant total : {{ number_format((int) $reservation->total_xof, 0, ',', ' ') }} XOF</li>
                    <li>Acompte payé : {{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</li>
                    @php $balanceDue = max(0, (int) $reservation->total_xof - (int) $reservation->deposit_amount_xof); @endphp
                    @if($balanceDue > 0)
                        <li>Solde restant à régler sur place : {{ number_format($balanceDue, 0, ',', ' ') }} XOF</li>
                    @endif
                    <li>Référence : {{ $reservation->reference }}</li>
                </ul>

                <p>
                    Votre <strong>reçu de réservation</strong> est joint à cet e-mail au format PDF. La localisation exacte de l'établissement (adresse précise et itinéraire) est également désormais disponible sur votre page de confirmation.
                </p>

                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0;">
                    <tr>
                        <td style="padding-right:10px; padding-bottom:10px;">
                            <a href="{{ $confirmationUrl }}" style="display:inline-block; padding:12px 20px; background:linear-gradient(135deg,#f4c65a,#f2790f,#c25e0a); color:#1b1408; font-weight:bold; text-decoration:none; border-radius:8px; font-size:13px;">
                                Voir ma confirmation
                            </a>
                        </td>
                        <td style="padding-bottom:10px;">
                            <a href="{{ $receiptUrl }}" style="display:inline-block; padding:12px 20px; background:#ffffff; color:#1c1915; font-weight:bold; text-decoration:none; border-radius:8px; border:1px solid rgba(0,0,0,.15); font-size:13px;">
                                Voir mon reçu en ligne
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <a href="{{ $guestRegistrationUrl }}" style="display:inline-block; padding:12px 20px; background:#c25e0a; color:#ffffff; font-weight:bold; text-decoration:none; border-radius:8px; font-size:13px;">
                                Voir ma fiche d'enregistrement
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size:12px; color:#8a7f6b;">Ces liens sont personnels — ne les partagez pas.</p>
                <p>Merci pour votre confiance.</p>
            </td>
        </tr>
    </table>
</body>
</html>
