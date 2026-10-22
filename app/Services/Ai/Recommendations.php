<?php

namespace App\Services\Ai;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Point d'entrée unique du module 1 pour les contrôleurs et les vues. */
class Recommendations
{
    public function __construct(
        private readonly Catalog $catalog,
        private readonly InteractionStore $interactions,
        private readonly ProfileStore $profiles,
    ) {}

    public static function enabled(): bool
    {
        return (bool) config('ai.enabled', true);
    }

    /** Matrice acteur × fiche, gardée 60 s pour ne pas relire trois tables à chaque page. */
    public function matrix(): array
    {
        return Cache::remember('ai.matrix', 60, fn () => $this->interactions->matrix());
    }

    public function context(Request $request, array $types = []): RecoContext
    {
        $actor = $this->interactions->actorKey($request);
        $history = $actor ? ($this->matrix()[$actor] ?? []) : [];
        $ctx = RecoContext::fromProfile($this->profiles->get($request), $history);
        $ctx->types = $types;

        return $ctx;
    }

    public function recommender(): HybridRecommender
    {
        return new HybridRecommender($this->catalog, $this->matrix());
    }

    /** @return array{recommendations:array, alpha:float, cold:bool, profile:array} */
    public function forRequest(Request $request, int $k = 8, array $types = []): array
    {
        $ctx = $this->context($request, $types);
        $reco = $this->recommender();

        return [
            'recommendations' => $reco->recommend($ctx, $k),
            'alpha' => $reco->alpha($ctx),
            'cold' => $ctx->isCold(),
            'profile' => $this->profiles->get($request),
        ];
    }

    public function catalog(): Catalog
    {
        return $this->catalog;
    }
}
