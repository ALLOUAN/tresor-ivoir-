<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $reservation->reference }} — {{ $branding['site_name'] }}</title>
    @unless($forPdf)
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @endunless
    <style>
        @page { margin: 22px 26px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            color: #1c1915;
            background: #f6f3ed;
            font-size: 13px;
            line-height: 1.5;
        }
        .sheet {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.08);
        }
        .toolbar {
            max-width: 760px;
            margin: 0 auto 14px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            font-family: Helvetica, Arial, sans-serif;
        }
        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid rgba(0,0,0,0.12);
            background: #ffffff;
            color: #1c1915;
        }
        .toolbar .primary {
            background: #f2790f;
            border: none;
            color: #1b1408;
        }
        .header { width: 100%; border-collapse: collapse; background: #1c1712; color: #ffffff; }
        .header td { padding: 16px 28px; vertical-align: middle; }
        .header-brand { width: 100%; border-collapse: collapse; }
        .header-brand td { vertical-align: middle; padding: 0; }
        .header-brand img { height: 34px; max-width: 150px; object-fit: contain; }
        .header-brand .name { font-size: 16px; font-weight: bold; letter-spacing: .02em; }
        .header-brand .slogan { font-size: 9px; color: #d8cdb8; font-family: Helvetica, Arial, sans-serif; }
        .header-doc { text-align: right; font-family: Helvetica, Arial, sans-serif; }
        .header-doc .label { font-size: 8.5px; letter-spacing: .18em; text-transform: uppercase; color: #f4c65a; }
        .header-doc .ref { font-size: 17px; font-weight: bold; margin-top: 2px; }

        .band { width: 100%; border-collapse: collapse; background: #fdf7ec; border-bottom: 1px solid rgba(0,0,0,0.06); font-family: Helvetica, Arial, sans-serif; }
        .band td { padding: 9px 28px; vertical-align: middle; }
        .band .status { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 10.5px; font-weight: bold; background: #dcf7e6; color: #067647; }
        .band .status.pending { background: #fef3c7; color: #92400e; }
        .band .date-issued { font-size: 10.5px; color: #6b6355; }

        .content { padding: 16px 28px 6px; }
        .section { margin-bottom: 13px; }
        .section-title {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: #c25e0a;
            font-weight: bold;
            border-bottom: 1.5px solid #f2790f33;
            padding-bottom: 4px;
            margin-bottom: 7px;
        }
        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 3px 0; vertical-align: top; font-size: 12px; }
        table.kv td.label { color: #6b6355; width: 42%; font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; }
        table.kv td.value { font-weight: bold; }

        .grid2 { width: 100%; border-collapse: collapse; }
        .grid2 td { width: 50%; vertical-align: top; padding-right: 24px; }

        table.pricing { width: 100%; border-collapse: collapse; margin-top: 2px; }
        table.pricing td { padding: 4px 0; font-size: 12px; border-bottom: 1px solid rgba(0,0,0,0.06); }
        table.pricing td:last-child { text-align: right; font-weight: bold; }
        table.pricing tr.total td { border-bottom: none; border-top: 2px solid #1c1915; padding-top: 6px; font-size: 14px; }
        table.pricing tr.paid td { color: #067647; }
        table.pricing tr.balance td { color: #92400e; }

        .conditions { background: #fdf7ec; border: 1px solid #f2790f22; border-radius: 8px; padding: 10px 14px; font-size: 11px; color: #4a4436; white-space: pre-line; }

        .footer { width: 100%; border-collapse: collapse; border-top: 1px solid rgba(0,0,0,0.08); font-family: Helvetica, Arial, sans-serif; }
        .footer td { padding: 12px 28px; vertical-align: middle; }
        .footer .legal { font-size: 9px; color: #8a7f6b; max-width: 420px; line-height: 1.5; }
        .footer .qr { text-align: center; width: 90px; }
        .footer .qr img { width: 66px; height: 66px; }
        .footer .qr p { font-size: 7.5px; color: #8a7f6b; margin: 3px 0 0; }

        @media print {
            body { background: #ffffff; }
            .toolbar { display: none; }
            .sheet { border: none; }
        }
    </style>
</head>
<body>

    @unless($forPdf)
    <div class="toolbar">
        <a href="{{ $reservation->receiptPdfUrl() }}" class="primary"><i class="fas fa-download"></i> Télécharger le PDF</a>
        <button type="button" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
        <a href="{{ $reservation->confirmationUrl() }}"><i class="fas fa-arrow-left"></i> Retour à ma confirmation</a>
    </div>
    @endunless

    <div class="sheet">
        <table class="header"><tr>
            <td>
                <table class="header-brand"><tr>
                    @if(!empty($branding['logo_url']))
                    <td style="width:44px;"><img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] }}"></td>
                    @endif
                    <td>
                        <div class="name">{{ $branding['site_name'] }}</div>
                        @if(!empty($branding['site_slogan']))
                            <div class="slogan">{{ $branding['site_slogan'] }}</div>
                        @endif
                    </td>
                </tr></table>
            </td>
            <td class="header-doc">
                <div class="label">Reçu de réservation</div>
                <div class="ref">{{ $reservation->reference }}</div>
            </td>
        </tr></table>

        <table class="band"><tr>
            <td>
                <span class="status {{ $reservation->status === \App\Models\Reservation::STATUS_CONFIRMED ? '' : 'pending' }}">
                    {{ $reservation->labelForStatus() }} · {{ $reservation->labelForPaymentStatus() }}
                </span>
            </td>
            <td class="date-issued" style="text-align:right;">Émis le {{ now()->format('d/m/Y à H:i') }}</td>
        </tr></table>

        <div class="content">

            <table class="grid2"><tr>
                <td>
                    <div class="section">
                        <p class="section-title">Établissement</p>
                        <table class="kv">
                            <tr><td class="label">Nom</td><td class="value">{{ $reservation->accommodation_name }}</td></tr>
                            <tr><td class="label">Type</td><td class="value">{{ $reservation->accommodation?->type_label }}</td></tr>
                            <tr><td class="label">Ville</td><td class="value">{{ $reservation->accommodation?->city?->name ?: '—' }}</td></tr>
                            <tr><td class="label">Région</td><td class="value">{{ $reservation->accommodation?->city?->region_administrative ?: '—' }}</td></tr>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="section">
                        <p class="section-title">Client</p>
                        <table class="kv">
                            <tr><td class="label">Nom</td><td class="value">{{ $reservation->full_name }}</td></tr>
                            <tr><td class="label">Email</td><td class="value">{{ $reservation->email }}</td></tr>
                            <tr><td class="label">Téléphone</td><td class="value">{{ $reservation->phone ?: '—' }}</td></tr>
                            <tr><td class="label">Réservé le</td><td class="value">{{ $reservation->created_at?->format('d/m/Y à H:i') }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr></table>

            <div class="section">
                <p class="section-title">Séjour</p>
                <table class="kv">
                    <tr><td class="label">Chambre / Logement</td><td class="value">{{ $reservation->room_name }}</td></tr>
                    <tr><td class="label">Arrivée</td><td class="value">{{ $reservation->check_in?->format('d/m/Y') }}</td></tr>
                    <tr><td class="label">Départ</td><td class="value">{{ $reservation->check_out?->format('d/m/Y') }}</td></tr>
                    <tr><td class="label">Durée</td><td class="value">{{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }} · {{ $reservation->rooms_count }} chambre{{ $reservation->rooms_count > 1 ? 's' : '' }}</td></tr>
                    <tr><td class="label">Voyageurs</td><td class="value">{{ $reservation->guests_count }} personne{{ $reservation->guests_count > 1 ? 's' : '' }}</td></tr>
                </table>
            </div>

            @php
                $roomSubtotal = (int) $reservation->room_price_xof * (int) $reservation->nights * (int) $reservation->rooms_count;
                $fees = max(0, (int) $reservation->total_xof - $roomSubtotal);
                $balance = max(0, (int) $reservation->total_xof - (int) $reservation->deposit_amount_xof);
            @endphp
            <div class="section">
                <p class="section-title">Tarification</p>
                <table class="pricing">
                    <tr><td>Tarif par nuit</td><td>{{ number_format((int) $reservation->room_price_xof, 0, ',', ' ') }} XOF</td></tr>
                    <tr><td>{{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }} × {{ $reservation->rooms_count }} chambre{{ $reservation->rooms_count > 1 ? 's' : '' }}</td><td>{{ number_format($roomSubtotal, 0, ',', ' ') }} XOF</td></tr>
                    @if($fees > 0)
                    <tr><td>Frais de service</td><td>{{ number_format($fees, 0, ',', ' ') }} XOF</td></tr>
                    @endif
                    <tr class="total"><td>Montant total</td><td>{{ number_format((int) $reservation->total_xof, 0, ',', ' ') }} XOF</td></tr>
                    <tr class="paid"><td>Acompte payé — CinetPay (Mobile Money / Carte bancaire)</td><td>{{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</td></tr>
                    <tr class="balance"><td>Solde à régler sur place</td><td>{{ number_format($balance, 0, ',', ' ') }} XOF</td></tr>
                </table>
            </div>

            <div class="section">
                <p class="section-title">Conditions essentielles</p>
                <div class="conditions">{{ $reservation->accommodation?->cancellation_policy ?: "Le solde restant est à régler directement auprès de l'établissement lors de votre arrivée. Pour toute condition d'annulation spécifique, veuillez contacter directement l'établissement." }}</div>
            </div>
        </div>

        <table class="footer"><tr>
            <td class="legal">
                Document généré automatiquement par {{ $branding['site_name'] }} — ce reçu fait foi de paiement de l'acompte de réservation.
                @if(!empty($branding['contact']['email_primary']))
                    Contact : {{ $branding['contact']['email_primary'] }}
                @endif
                @if(!empty($branding['contact']['phone_1']))
                    · {{ $branding['contact']['phone_1'] }}
                @endif
            </td>
            <td class="qr">
                <img src="{{ $qrCodeDataUri }}" alt="QR code de vérification">
                <p>Scanner pour vérifier</p>
            </td>
        </tr></table>
    </div>
</body>
</html>
