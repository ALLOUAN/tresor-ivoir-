<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;

/** QR code PNG (data URI) : billets, menu de table, arrivée à l'hôtel. */
final class Qr
{
    public static function dataUri(string $data, int $size = 260): string
    {
        return (new Builder())->build(
            data: $data,
            size: $size,
            margin: 8,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            foregroundColor: new Color(28, 25, 21),
            backgroundColor: new Color(255, 255, 255),
        )->getDataUri();
    }
}
