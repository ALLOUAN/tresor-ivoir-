<?php

namespace App\Http\Controllers;

use App\Models\ArtworkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Certificat d'authenticité d'une œuvre vendue sur la plateforme : PDF pour l'acheteur, et page
 * publique de vérification (lien du QR imprimé sur le certificat). Accès par code non devinable.
 */
class ArtCertificateController extends Controller
{
    private function order(int $order, string $code): ArtworkOrder
    {
        $o = ArtworkOrder::with(['artwork', 'provider'])->findOrFail($order);
        abort_unless(hash_equals($o->certificateCode(), strtoupper($code)) && $o->hasCertificate(), 404);

        return $o;
    }

    public function pdf(int $order, string $code): Response
    {
        $o = $this->order($order, $code);
        $pdf = Pdf::loadView('certificates.artwork', [
            'order' => $o,
            'qr' => \App\Support\Qr::dataUri($o->certificateVerifyUrl(), 240),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('certificat-'.$o->certificateNumber().'.pdf');
    }

    public function verify(int $order, string $code): View
    {
        return view('certificates.verify', ['order' => $this->order($order, $code)]);
    }
}
