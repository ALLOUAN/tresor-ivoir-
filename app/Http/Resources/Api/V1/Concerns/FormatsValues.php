<?php

namespace App\Http\Resources\Api\V1\Concerns;

use DateTimeInterface;

/** Mises en forme communes des ressources de l'API (URL absolues, dates ISO 8601, nombres). */
trait FormatsValues
{
    /** « /storage/… » → « https://site/storage/… » : l'application n'a pas à connaître l'adresse du site. */
    protected function absoluteUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        return preg_match('#^https?://#i', $url) ? $url : url($url);
    }

    protected function isoDate(?DateTimeInterface $date): ?string
    {
        return $date?->format(DATE_ATOM);
    }

    protected function decimal(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
