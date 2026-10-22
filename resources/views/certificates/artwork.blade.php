<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat d'authenticité {{ $order->certificateNumber() }}</title>
    <style>
        @page { margin: 14mm; }
        body { font-family: DejaVu Serif, Georgia, serif; color: #1c1915; margin: 0; }
        .frame { border: 3px double #b8892b; padding: 14mm 16mm; height: 150mm; position: relative; }
        .kicker { font-family: DejaVu Sans, Arial, sans-serif; letter-spacing: 4px; font-size: 10pt; color: #b8892b; text-transform: uppercase; text-align: center; }
        h1 { text-align: center; font-size: 26pt; margin: 4mm 0 8mm; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .label { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8.5pt; color: #8a7f6b; text-transform: uppercase; letter-spacing: 1px; }
        .value { font-size: 13pt; margin-bottom: 5mm; }
        .title { font-size: 18pt; font-weight: bold; margin-bottom: 5mm; }
        .foot { position: absolute; left: 16mm; right: 16mm; bottom: 10mm; font-family: DejaVu Sans, Arial, sans-serif; font-size: 8.5pt; color: #5c5548; }
        .sign { border-top: 1px solid #1c1915; width: 60mm; padding-top: 2mm; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; text-align: center; }
    </style>
</head>
<body>
<div class="frame">
    <p class="kicker">Trésors d'Ivoire · Art &amp; Créations</p>
    <h1>Certificat d'authenticité</h1>
    <table>
        <tr>
            <td style="width: 68%; padding-right: 8mm;">
                <p class="label">Œuvre</p>
                <p class="title">{{ $order->artwork?->title ?? 'Œuvre' }}</p>
                @if($order->edition_label)<p class="value">Édition limitée « {{ $order->edition_label }} » — exemplaire n° {{ $order->edition_number }} / {{ $order->edition_size }}</p>@endif
                <p class="label">Artiste</p>
                <p class="value">{{ $order->provider?->name }}</p>
                <table>
                    <tr>
                        <td><p class="label">Technique</p><p class="value">{{ $order->artwork?->medium ?: '—' }}</p></td>
                        <td><p class="label">Dimensions</p><p class="value">{{ $order->edition_label ? ($order->edition?->width_cm ? (float) $order->edition->width_cm.' × '.(float) $order->edition->height_cm.' cm' : $order->edition_label) : ($order->artwork?->width_cm ? (float) $order->artwork->width_cm.' × '.(float) $order->artwork->height_cm.' cm' : ($order->artwork?->dimensions ?: '—')) }}</p></td>
                        <td><p class="label">Année</p><p class="value">{{ $order->artwork?->year_created ?: '—' }}</p></td>
                    </tr>
                </table>
                <p class="label">Acquéreur</p>
                <p class="value">{{ $order->buyer_name }} — acquise le {{ ($order->paid_at ?? $order->created_at)->format('d/m/Y') }}</p>
            </td>
            <td style="text-align: center;">
                <img src="{{ $qr }}" alt="QR de vérification" style="width: 46mm; height: 46mm;">
                <p class="label" style="margin-top: 2mm;">N° {{ $order->certificateNumber() }}</p>
                <p class="label">Code {{ $order->certificateCode() }}</p>
            </td>
        </tr>
    </table>
    <div class="foot">
        <table><tr>
            <td>Ce certificat atteste que l'œuvre décrite ci-dessus a été vendue par l'artiste via la plateforme Trésors d'Ivoire. Son authenticité peut être vérifiée à tout moment en scannant le QR code.</td>
            <td style="width: 65mm; text-align: right;"><div class="sign" style="margin-left: auto;">{{ $order->provider?->name }}</div></td>
        </tr></table>
    </div>
</div>
</body>
</html>
