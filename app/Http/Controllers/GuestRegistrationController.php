<?php

namespace App\Http\Controllers;

use App\Models\GuestRegistration;
use App\Models\Reservation;
use App\Services\ClientAccountService;
use App\Support\ImageUploadGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuestRegistrationController extends Controller
{
    /** Poids maximal d'une image (Ko) ; les photos sont de toute façon réduites dans le navigateur avant l'envoi. */
    private const MAX_KB = 8192;

    public function create(Reservation $reservation): View
    {
        $reservation->loadMissing(['accommodation.city', 'guestRegistration', 'user']);

        // Pré-remplissage à partir de la réservation (et du compte s'il existe) : moins de saisie.
        [$first, $last] = ClientAccountService::splitFullName((string) $reservation->full_name);
        $prefill = [
            'first_name' => $reservation->user?->first_name ?: $first,
            'last_name' => $reservation->user?->last_name ?: $last,
            'email' => $reservation->email ?: $reservation->user?->email,
            'phone_number' => preg_replace('/^\+?225\s*/', '', (string) ($reservation->phone ?: $reservation->user?->phone)),
        ];

        return view('reservations.guest-registration', [
            'reservation' => $reservation,
            'registration' => $reservation->guestRegistration,
            'prefill' => $prefill,
        ]);
    }

    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        // Une seule fiche par réservation — un second envoi (double clic, lien réutilisé)
        // n'écrase jamais une fiche déjà soumise, redirige simplement vers le récapitulatif.
        if ($reservation->guestRegistration) {
            return redirect($reservation->guestRegistrationUrl())
                ->with('info', __('Une fiche a déjà été enregistrée pour cette réservation.'));
        }

        // Le fichier de signature s'appelle « signature_image » : « signature » est déjà le paramètre
        // du lien signé (?signature=…), qui masquerait le fichier lors de la validation.
        $this->sanitizeEmptyUploads($request, ['document_scan_front', 'document_scan_back', 'selfie', 'signature_image']);
        $image = ['image', 'mimes:jpeg,jpg,png,webp', 'max:'.self::MAX_KB];

        $validated = $request->validate([
            'travel_type' => ['required', Rule::in(array_keys(GuestRegistration::TRAVEL_TYPE_LABELS))],
            'document_type' => ['required', Rule::in(array_keys(GuestRegistration::DOCUMENT_TYPE_LABELS))],
            'document_number' => ['required', 'string', 'max:100'],
            'document_expiry_date' => ['required', 'date', 'after_or_equal:hotel_check_out_date'],
            'travel_reason' => ['required', Rule::in(array_keys(GuestRegistration::REASON_LABELS))],
            'coming_from' => ['nullable', 'string', 'max:150'],
            'going_to' => ['nullable', 'string', 'max:150'],
            'hotel_check_in_date' => ['required', 'date'],
            'hotel_check_in_time' => ['required', 'date_format:H:i'],
            'hotel_check_out_date' => ['required', 'date', 'after_or_equal:hotel_check_in_date'],
            'hotel_check_out_time' => ['required', 'date_format:H:i'],

            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'nationality' => ['required', 'string', 'max:80'],
            'birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
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

            // Passeport : une seule page d'identité, le verso n'est pas demandé.
            'document_scan_front' => ['required', ...$image],
            'document_scan_back' => [Rule::requiredIf(fn () => $request->input('document_type') !== GuestRegistration::DOCUMENT_TYPE_PASSPORT), 'nullable', ...$image],
            'selfie' => ['required', ...$image],
            'signature_image' => ['required', ...$image],
        ], [
            'data_confirmed.accepted' => __('Vous devez confirmer que les données saisies sont correctes.'),
            'document_expiry_date.after_or_equal' => __('Votre pièce d\'identité doit être valide jusqu\'à la fin de votre séjour.'),
            'document_scan_back.required' => __('Ajoutez le verso de votre pièce d\'identité.'),
            'document_scan_front.required' => __('Ajoutez le recto de votre pièce d\'identité.'),
            'selfie.required' => __('Ajoutez un selfie.'),
            'signature_image.required' => __('Signez la fiche avant de l\'envoyer.'),
            '*.max' => __('Le fichier est trop lourd (8 Mo maximum).'),
            '*.mimes' => __('Format non accepté : utilisez une photo JPG, PNG ou WebP.'),
            '*.image' => __('Ce fichier n\'est pas une image.'),
        ], [
            'document_scan_front' => __('recto'), 'document_scan_back' => __('verso'), 'birth_date' => __('date de naissance'),
        ]);

        // Contenu réel des images (un fichier renommé en .jpg est refusé) : erreur affichée dans le formulaire.
        foreach (['document_scan_front', 'document_scan_back', 'selfie', 'signature_image'] as $field) {
            if ($request->hasFile($field) && ! ImageUploadGuard::isRealImage($request->file($field))) {
                throw ValidationException::withMessages([$field => __('Ce fichier n\'est pas une image valide (JPG, PNG ou WebP).')]);
            }
        }

        $folder = 'guest-registrations/'.$reservation->id;
        $store = fn (string $field, string $prefix) => $request->hasFile($field)
            ? $this->storePrivate($request->file($field), $folder, $prefix)
            : null;

        GuestRegistration::create([
            'reservation_id' => $reservation->id,
            'travel_type' => $validated['travel_type'],
            'document_type' => $validated['document_type'],
            'document_number' => strtoupper(trim($validated['document_number'])),
            'document_expiry_date' => $validated['document_expiry_date'],
            'document_scan_front_url' => $store('document_scan_front', 'recto'),
            'document_scan_back_url' => $store('document_scan_back', 'verso'),
            'travel_reason' => $validated['travel_reason'],
            'coming_from' => $validated['coming_from'] ?? null,
            'going_to' => $validated['going_to'] ?? null,
            'selfie_url' => $store('selfie', 'selfie'),
            'hotel_check_in_date' => $validated['hotel_check_in_date'],
            'hotel_check_in_time' => $validated['hotel_check_in_time'],
            'hotel_check_out_date' => $validated['hotel_check_out_date'],
            'hotel_check_out_time' => $validated['hotel_check_out_time'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'nationality' => $validated['nationality'],
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
            'signature_url' => $store('signature_image', 'signature'),
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return $reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID
            ? redirect($reservation->guestRegistrationUrl())->with('success', __('Merci ! Votre fiche a bien été transmise à l\'établissement.'))
            : redirect($reservation->paymentUrl());
    }

    /**
     * Consultation d'une pièce (lien signé, durée courte) : servie depuis le disque privé,
     * jamais mise en cache ni indexée.
     */
    public function file(GuestRegistration $registration, string $kind): BinaryFileResponse
    {
        abort_unless(array_key_exists($kind, GuestRegistration::FILE_COLUMNS), 404);
        $value = (string) $registration->{GuestRegistration::FILE_COLUMNS[$kind]};
        abort_unless(str_starts_with($value, GuestRegistration::PRIVATE_PREFIX), 404);

        $path = substr($value, strlen(GuestRegistration::PRIVATE_PREFIX));
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Content-Disposition' => 'inline',
        ]);
    }

    // ── HELPERS UPLOAD ───────────────────────────────────────────────────────

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

    /**
     * Rangement sur le disque PRIVÉ (storage/app/private) : pièces d'identité, selfie et signature
     * ne sont jamais accessibles par une adresse publique. Écriture via fopen() plutôt que store() :
     * sous Windows, store() appelle realpath(), qui peut échouer quand les noms courts 8.3 sont désactivés.
     */
    private function storePrivate(UploadedFile $file, string $folder, string $prefix): string
    {
        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;
        if (! is_resource($handle)) {
            throw ValidationException::withMessages(['document_scan_front' => __('Un fichier envoyé est illisible. Sélectionnez-le à nouveau.')]);
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $relativePath = $folder.'/'.$prefix.'_'.Str::random(40).'.'.$extension;

        try {
            $stored = Storage::disk('local')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }
        if (! $stored) {
            throw ValidationException::withMessages(['document_scan_front' => __('L\'enregistrement d\'un fichier a échoué. Réessayez.')]);
        }

        return GuestRegistration::PRIVATE_PREFIX.$relativePath;
    }
}
