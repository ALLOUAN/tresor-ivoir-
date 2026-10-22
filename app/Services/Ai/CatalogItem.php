<?php

namespace App\Services\Ai;

/** Fiche normalisée (hébergement, restaurant, site, événement, prestataire…) vue par les modules IA. */
final class CatalogItem
{
    public function __construct(
        public string $key,            // « type:id »
        public string $type,           // accommodation|restaurant|site|event|leisure|experience|agency|transport|provider
        public int $id,
        public string $title,
        public string $typeLabel,
        public string $category,
        public ?string $city,
        public ?int $cityId,
        public ?float $lat,
        public ?float $lng,
        public ?int $price,            // XOF : nuit (hébergement), entrée, billet, repas moyen…
        public float $rating,
        public int $ratingCount,
        public int $views,
        public bool $featured,
        public bool $verified,
        public float $planLevel,       // 0 sans forfait actif, 0.35 bronze, 0.65 silver, 1 gold
        public ?int $startsAt,         // événements : timestamps
        public ?int $endsAt,
        public ?int $createdAt,
        public array $tags,            // centres d'intérêt détectés
        public array $tf,              // fréquences de termes (avant TF-IDF)
        public string $url,
        public ?string $image,
        public string $excerpt = '',
        public ?int $providerId = null,
        public array $extra = [],      // ex. chambres, durée de visite
    ) {}

    public function hasGeo(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }
}
