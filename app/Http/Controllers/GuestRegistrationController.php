<?php

namespace App\Http\Controllers;

use App\Models\GuestRegistration;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestRegistrationController extends Controller
{
    public function create(Reservation $reservation): View
    {
        $reservation->loadMissing(['accommodation', 'guestRegistration']);

        return view('reservations.guest-registration', [
            'reservation' => $reservation,
            'registration' => $reservation->guestRegistration,
        ]);
    }

    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        // Une seule fiche par réservation — un second envoi (double clic, lien réutilisé)
        // n'écrase jamais une fiche déjà soumise, redirige simplement vers le récapitulatif.
        if ($reservation->guestRegistration) {
            return redirect($reservation->guestRegistrationUrl())
                ->with('info', 'Une fiche a déjà été enregistrée pour cette réservation.');
        }

        $this->sanitizeEmptyUploads($request, ['document_scan_front', 'document_scan_back', 'selfie', 'signature']);

        $validated = $request->validate([
            'travel_type' => ['required', 'in:'.implode(',', array_keys(GuestRegistration::TRAVEL_TYPE_LABELS))],
            'document_type' => ['required', 'in:'.implode(',', array_keys(GuestRegistration::DOCUMENT_TYPE_LABELS))],
            'document_number' => ['required', 'string', 'max:100'],
            'travel_reason' => ['required', 'in:'.implode(',', array_keys(GuestRegistration::REASON_LABELS))],
            'hotel_check_in_date' => ['required', 'date'],
            'hotel_check_in_time' => ['required', 'date_format:H:i'],
            'hotel_check_out_date' => ['required', 'date', 'after_or_equal:hotel_check_in_date'],
            'hotel_check_out_time' => ['required', 'date_format:H:i'],

            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['required', 'string', 'max:150'],
            'father_name' => ['required', 'string', 'max:150'],
            'mother_name' => ['required', 'string', 'max:150'],
            'profession' => ['required', 'string', 'max:150'],
            'home_address' => ['required', 'string', 'max:255'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],

            'phone_country_code' => ['required', 'string', 'max:6'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'data_confirmed' => ['accepted'],
        ], [
            'data_confirmed.accepted' => 'Vous devez confirmer que les données saisies sont correctes.',
        ]);

        foreach (['document_scan_front', 'document_scan_back', 'selfie', 'signature'] as $field) {
            if (! $this->hasUsableUploadedFile($request, $field)) {
                return back()->withErrors([$field => 'Ce document est requis.'])->withInput();
            }
            $this->assertValidImage($request->file($field), $field, 8192);
        }

        $folder = 'guest-registrations/'.$reservation->id;

        GuestRegistration::create([
            'reservation_id' => $reservation->id,
            'travel_type' => $validated['travel_type'],
            'document_type' => $validated['document_type'],
            'document_number' => $validated['document_number'],
            'document_scan_front_url' => $this->storeUploadedFile($request->file('document_scan_front'), $folder, 'recto'),
            'document_scan_back_url' => $this->storeUploadedFile($request->file('document_scan_back'), $folder, 'verso'),
            'travel_reason' => $validated['travel_reason'],
            'selfie_url' => $this->storeUploadedFile($request->file('selfie'), $folder, 'selfie'),
            'hotel_check_in_date' => $validated['hotel_check_in_date'],
            'hotel_check_in_time' => $validated['hotel_check_in_time'],
            'hotel_check_out_date' => $validated['hotel_check_out_date'],
            'hotel_check_out_time' => $validated['hotel_check_out_time'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'],
            'birth_place' => $validated['birth_place'],
            'father_name' => $validated['father_name'],
            'mother_name' => $validated['mother_name'],
            'profession' => $validated['profession'],
            'home_address' => $validated['home_address'],
            'children_count' => $validated['children_count'] ?? 0,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'phone_country_code' => $validated['phone_country_code'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'] ?? null,
            'data_confirmed' => true,
            'signature_url' => $this->storeUploadedFile($request->file('signature'), $folder, 'signature'),
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return redirect($reservation->paymentUrl());
    }

    // ── HELPERS UPLOAD (mêmes garde-fous que VisitorProfileController) ───────

    private function sanitizeEmptyUploads(Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            $file = $request->file($field);
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $pathname = '';
            try {
                $pathname = (string) $file->getPathname();
            } catch (\ValueError) {
                $pathname = '';
            }
            if ($file->getError() === \UPLOAD_ERR_NO_FILE || $pathname === '') {
                $request->files->remove($field);
            }
        }
    }

    private function hasUsableUploadedFile(Request $request, string $field): bool
    {
        $file = $request->file($field);

        return $file instanceof UploadedFile && $this->isUsableUploadedFile($file);
    }

    private function isUsableUploadedFile(UploadedFile $file): bool
    {
        try {
            return $file->isValid() && $file->getError() === \UPLOAD_ERR_OK;
        } catch (\ValueError) {
            return false;
        }
    }

    private function assertValidImage(UploadedFile $file, string $field, int $maxKb): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['jpeg', 'jpg', 'png', 'webp'], true)) {
            abort(422, "Format image non autorisé pour {$field} (jpeg, jpg, png, webp).");
        }
        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0 || $size > ($maxKb * 1024)) {
            abort(422, "Le fichier {$field} dépasse la taille autorisée.");
        }
    }

    /**
     * Stockage via fopen() plutôt que UploadedFile::store() : sous Windows, store()
     * appelle realpath() en interne, qui peut échouer (ValueError) quand les noms
     * courts 8.3 sont désactivés sur le volume.
     */
    private function storeUploadedFile(UploadedFile $file, string $folder, string $prefix): string
    {
        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;

        if (! is_resource($handle)) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';
        $relativePath = $folder.'/'.$prefix.'_'.Str::random(40).'.'.$extension;

        try {
            $stored = Storage::disk('public')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        if (! $stored) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        return '/storage/'.$relativePath;
    }
}
