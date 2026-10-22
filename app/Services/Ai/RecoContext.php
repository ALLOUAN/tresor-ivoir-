<?php

namespace App\Services\Ai;

/** Ce que l'on sait d'un visiteur au moment de recommander. */
final class RecoContext
{
    /**
     * @param array<string,float> $history  fiche => poids (consultations, favoris, réservations)
     * @param string[]            $interests clés de Taxonomy::INTERESTS
     * @param string[]            $types     restreint aux types de fiches (vide = tous)
     * @param string[]            $exclude   fiches à ne pas proposer
     */
    public function __construct(
        public array $history = [],
        public array $interests = [],
        public ?string $budget = null,
        public ?string $travelType = null,
        public ?int $cityId = null,
        public array $types = [],
        public array $exclude = [],
    ) {}

    public static function fromProfile(array $profile, array $history = []): self
    {
        return new self(
            history: $history,
            interests: $profile['interests'] ?? [],
            budget: $profile['budget'] ?? null,
            travelType: $profile['travel_type'] ?? null,
            cityId: $profile['home_city_id'] ?? null,
        );
    }

    public function isCold(): bool
    {
        return ! $this->history && ! $this->interests && ! $this->budget && ! $this->travelType;
    }
}
