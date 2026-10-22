<?php

namespace App\Services;

use App\Models\ProviderPromoCode;

/** Recherche, contrôle et consommation des codes promo des établissements. */
class ProviderPromoService
{
    /**
     * Vérifie un code pour un établissement et un montant.
     *
     * @return array{promo: ?ProviderPromoCode, discount: int, error: ?string}
     */
    public function check(?int $providerId, ?string $code, int $amountXof = 0): array
    {
        $code = ProviderPromoCode::normalize($code);
        if ($code === '') {
            return ['promo' => null, 'discount' => 0, 'error' => null];
        }
        $promo = $providerId ? ProviderPromoCode::where('provider_id', $providerId)->where('code', $code)->first() : null;
        if (! $promo) {
            return ['promo' => null, 'discount' => 0, 'error' => __('Code promo inconnu pour cet établissement.')];
        }
        if ($problem = $promo->problemFor($amountXof)) {
            return ['promo' => null, 'discount' => 0, 'error' => $problem];
        }

        return ['promo' => $promo, 'discount' => $amountXof > 0 ? $promo->discountFor($amountXof) : 0, 'error' => null];
    }

    /**
     * Consomme une utilisation, à appeler dans la transaction qui crée la réservation.
     * Faux si le code est devenu inutilisable entre-temps (dernière utilisation prise).
     */
    public function consume(ProviderPromoCode $promo): bool
    {
        $locked = ProviderPromoCode::whereKey($promo->id)->lockForUpdate()->first();
        if (! $locked || $locked->problemFor(0) !== null) {
            return false;
        }
        $locked->increment('uses_count');

        return true;
    }
}
