<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;

class ReservationReceiptService
{
    /**
     * Génère un QR code (PNG, encodé en data URI) pointant vers l'URL donnée —
     * embarquable directement dans du HTML/PDF sans fichier temporaire.
     */
    public function qrCodeDataUri(string $url): string
    {
        $result = (new Builder())->build(
            data: $url,
            size: 260,
            margin: 8,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            foregroundColor: new Color(28, 25, 21),
            backgroundColor: new Color(255, 255, 255),
        );

        return $result->getDataUri();
    }

    /**
     * Rendu HTML du reçu, partagé entre l'affichage web et la génération PDF.
     */
    public function renderHtml(Reservation $reservation, bool $forPdf = false): string
    {
        $reservation->loadMissing(['accommodation', 'provider']);

        $branding = SiteSetting::branding();
        if ($forPdf) {
            $branding['logo_url'] = $this->logoAsDataUriOrNull($branding['logo_url'] ?? null);
        }

        return view('reservations.receipt', [
            'reservation' => $reservation,
            'branding' => $branding,
            'qrCodeDataUri' => $this->qrCodeDataUri($reservation->receiptUrl()),
            'forPdf' => $forPdf,
        ])->render();
    }

    /**
     * Dompdf ne fait pas de requêtes réseau (enable_remote désactivé) : le logo, servi
     * en chemin relatif /storage/..., doit être embarqué en data URI pour apparaître dans le PDF.
     */
    private function logoAsDataUriOrNull(?string $logoUrl): ?string
    {
        if (empty($logoUrl)) {
            return null;
        }

        $path = public_path(ltrim(parse_url($logoUrl, PHP_URL_PATH) ?: '', '/'));
        if (! is_file($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => null,
        };

        if (! $mime) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    public function generatePdf(Reservation $reservation)
    {
        return Pdf::loadHTML($this->renderHtml($reservation, true))
            ->setPaper('a4', 'portrait');
    }

    public function pdfFilename(Reservation $reservation): string
    {
        return 'recu-'.strtolower($reservation->reference).'.pdf';
    }
}
