<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\ReservationReceiptService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReservationReceiptController extends Controller
{
    public function show(Reservation $reservation, ReservationReceiptService $receipts): View
    {
        abort_unless($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID, 404);

        $reservation->loadMissing(['accommodation', 'provider']);

        return view('reservations.receipt', [
            'reservation' => $reservation,
            'branding' => \App\Models\SiteSetting::branding(),
            'qrCodeDataUri' => $receipts->qrCodeDataUri($reservation->receiptUrl()),
            'forPdf' => false,
        ]);
    }

    public function pdf(Reservation $reservation, ReservationReceiptService $receipts): Response
    {
        abort_unless($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID, 404);

        return $receipts->generatePdf($reservation)->download($receipts->pdfFilename($reservation));
    }
}
