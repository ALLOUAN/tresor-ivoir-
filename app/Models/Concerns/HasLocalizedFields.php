<?php

namespace App\Models\Concerns;

/**
 * Champs bilingues {champ}_fr / {champ}_en : renvoie la version de la langue courante,
 * avec repli sur le français quand la traduction anglaise n'est pas renseignée.
 */
trait HasLocalizedFields
{
    public function localized(string $field): ?string
    {
        if (app()->getLocale() === 'en') {
            $en = $this->getAttribute($field.'_en');
            if (is_string($en) && trim($en) !== '') {
                return $en;
            }
        }

        return $this->getAttribute($field.'_fr') ?? $this->getAttribute($field);
    }
}
