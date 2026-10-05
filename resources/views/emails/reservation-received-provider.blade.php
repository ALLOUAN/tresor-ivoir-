<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle réservation</title>
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
                <h2 style="margin:0 0 14px;">Nouvelle réservation confirmée</h2>
                <p>Bonjour,</p>
                <p>Une réservation vient d'être confirmée (acompte payé) pour <strong>{{ $reservation->accommodation_name }}</strong> :</p>
                <ul>
                    <li>Référence : {{ $reservation->reference }}</li>
                    <li>Chambre : {{ $reservation->room_name }}</li>
                    <li>Séjour : {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }} ({{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }})</li>
                    <li>Voyageurs : {{ $reservation->guests_count }} pers. · {{ $reservation->rooms_count }} chambre{{ $reservation->rooms_count > 1 ? 's' : '' }}</li>
                    <li>Montant total : {{ number_format((int) $reservation->total_xof, 0, ',', ' ') }} XOF</li>
                    <li>Acompte payé : {{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</li>
                </ul>

                <p style="margin:20px 0 8px; font-weight:bold;">Client</p>
                <ul>
                    <li>Nom : {{ $reservation->full_name }}</li>
                    <li>E-mail : {{ $reservation->email }}</li>
                    @if($reservation->phone)
                        <li>Téléphone : {{ $reservation->phone }}</li>
                    @endif
                </ul>

                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0;">
                    <tr>
                        <td>
                            <a href="{{ route('provider.reservations.show', $reservation) }}" style="display:inline-block; padding:12px 20px; background:linear-gradient(135deg,#f4c65a,#f2790f,#c25e0a); color:#1b1408; font-weight:bold; text-decoration:none; border-radius:8px; font-size:13px;">
                                Voir la réservation
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size:12px; color:#8a7f6b;">Ces informations sont confidentielles — merci de ne les utiliser que dans le cadre du séjour réservé.</p>
            </td>
        </tr>
    </table>
</body>
</html>
