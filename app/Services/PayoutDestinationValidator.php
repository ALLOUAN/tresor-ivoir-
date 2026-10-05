<?php

namespace App\Services;

use App\Models\PayoutRequest;

class PayoutDestinationValidator
{
    /**
     * Numéro mobile ivoirien (+225 ou 0 initial) — Orange (07/09/01), MTN (05/06), Moov (01/02),
     * Wave utilise indifféremment ces mêmes numéros. Volontairement permissif sur les préfixes
     * (les plans de numérotation évoluent) mais strict sur la structure générale.
     */
    private const MOBILE_MONEY_PATTERN = '/^(\+225|00225|0)\d{8,10}$/';

    /** Compte bancaire / IBAN — structure large (alphanumérique, 10 à 34 caractères) plutôt qu'un
     * checksum IBAN complet, pour ne pas rejeter des formats de compte locaux non-IBAN. */
    private const BANK_ACCOUNT_PATTERN = '/^[A-Z0-9]{10,34}$/i';

    public function isValidFormat(string $method, string $destination): bool
    {
        $normalized = preg_replace('/[\s.\-]/', '', trim($destination)) ?? '';

        if (in_array($method, PayoutRequest::MOBILE_MONEY_METHODS, true)) {
            return (bool) preg_match(self::MOBILE_MONEY_PATTERN, $normalized);
        }

        if ($method === PayoutRequest::METHOD_BANK_TRANSFER) {
            return (bool) preg_match(self::BANK_ACCOUNT_PATTERN, $normalized);
        }

        return false;
    }
}
