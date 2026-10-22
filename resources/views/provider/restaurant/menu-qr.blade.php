<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu QR — {{ $restaurant->name }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, 'Times New Roman', serif; color: #1c1915; background: #f6f3ed; }
        .toolbar { text-align: center; padding: 14px; font-family: Arial, sans-serif; }
        .toolbar button { background: #ef7c2b; color: #fff; border: 0; border-radius: 10px; padding: 10px 18px; font-weight: bold; cursor: pointer; }
        .sheet { display: grid; grid-template-columns: 1fr 1fr; gap: 10mm; max-width: 190mm; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #e3dccd; border-radius: 6mm; padding: 8mm 6mm; text-align: center; page-break-inside: avoid; }
        .card h1 { font-size: 20pt; margin: 0 0 2mm; }
        .card p { font-family: Arial, sans-serif; font-size: 10pt; margin: 0; color: #5c5548; }
        .card img { width: 52mm; height: 52mm; margin: 5mm auto; display: block; }
        .card .scan { font-family: Arial, sans-serif; font-weight: bold; color: #31621c; font-size: 12pt; }
        .card .brand { margin-top: 4mm; font-size: 8pt; color: #8a7f6b; }
        @media print { body { background: #fff; } .toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Imprimer (4 chevalets par page)</button></div>
    <div class="sheet">
        @foreach(range(1, 4) as $i)
            <div class="card">
                <h1>{{ $restaurant->name }}</h1>
                <p>Notre carte sur votre téléphone</p>
                <img src="{{ $qr }}" alt="QR code du menu">
                <p class="scan">Scannez pour voir le menu</p>
                <p class="brand">{{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }} · {{ parse_url($url, PHP_URL_HOST) }}</p>
            </div>
        @endforeach
    </div>
</body>
</html>
