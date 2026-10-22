<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Demande de réservation</title></head>
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
                @if($event === 'received')
                    <h2 style="margin:0 0 14px;">Nouvelle demande de réservation</h2>
                    <p><strong>{{ $booking->full_name }}</strong> souhaite réserver chez <strong>{{ $booking->bookable_name }}</strong>.</p>
                @elseif($event === 'confirmed')
                    <h2 style="margin:0 0 14px;">Votre réservation est confirmée</h2>
                    <p>Bonjour {{ $booking->full_name }}, <strong>{{ $booking->bookable_name }}</strong> confirme votre réservation.</p>
                @elseif($event === 'declined')
                    <h2 style="margin:0 0 14px;">Demande non acceptée</h2>
                    <p>Bonjour {{ $booking->full_name }}, <strong>{{ $booking->bookable_name }}</strong> ne peut malheureusement pas accepter votre demande.</p>
                @else
                    <h2 style="margin:0 0 14px;">Demande annulée</h2>
                    <p>{{ $booking->full_name }} a annulé sa demande de réservation.</p>
                @endif

                <ul>
                    <li>Type : {{ $booking->typeLabel() }}@if($booking->item_name) — {{ $booking->item_name }}@endif</li>
                    <li>Date : {{ $booking->booking_date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}@if($booking->booking_time) à {{ $booking->booking_time }}@endif</li>
                    @if($booking->pickup_location)
                        <li>Trajet : {{ $booking->pickup_location }} → {{ $booking->dropoff_location }}</li>
                    @endif
                    <li>{{ ucfirst($booking->unitLabel()) }} : {{ $booking->party_size }}</li>
                    <li>Référence : {{ $booking->reference }}</li>
                    @if(in_array($event, ['received', 'cancelled'], true))
                        <li>Contact : {{ $booking->email }}@if($booking->phone) · {{ $booking->phone }}@endif</li>
                    @endif
                </ul>

                @if($event === 'received' && $booking->message)
                    <p style="padding:12px 14px; background:#f6f3ed; border-radius:8px;">« {{ $booking->message }} »</p>
                @endif
                @if($booking->provider_note && in_array($event, ['confirmed', 'declined'], true))
                    <p style="padding:12px 14px; background:#fff3e3; border-radius:8px;">Message de l'établissement : {{ $booking->provider_note }}</p>
                @endif

                @if($event === 'received')
                    <p>Répondez depuis votre espace prestataire, rubrique « Demandes de réservation ».</p>
                @elseif($event === 'confirmed')
                    <p>Aucun paiement n'a été demandé en ligne : le règlement se fait sur place. Vous pouvez annuler depuis votre espace « Mes réservations ».</p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
