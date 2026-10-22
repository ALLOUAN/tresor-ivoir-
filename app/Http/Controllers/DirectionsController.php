<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\LeisureVenue;
use App\Models\Provider;
use App\Models\ProviderAnalytic;
use App\Models\Restaurant;
use App\Models\TouristExperience;
use App\Models\TouristSite;
use App\Models\TransportCompany;
use App\Models\TravelAgency;
use App\Services\GoogleRoutesService;
use App\Support\GoogleMaps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Bouton « Itinéraire » : compte le clic (clicks_direction du prestataire),
 * puis redirige vers Google Maps en mode trajet.
 *
 * Les hébergements sont volontairement absents : leur adresse exacte reste
 * masquée tant que la réservation n'est pas payée.
 */
class DirectionsController extends Controller
{
    /** type d'URL => [modèle, restriction de publication] */
    private function sources(): array
    {
        return [
            'annuaire' => [Provider::class, fn (Builder $q) => $q->where('status', 'active')],
            'restaurant' => [Restaurant::class, fn (Builder $q) => $q->active()],
            'agence' => [TravelAgency::class, fn (Builder $q) => $q->active()],
            'transport' => [TransportCompany::class, fn (Builder $q) => $q->active()],
            'experience' => [TouristExperience::class, fn (Builder $q) => $q->active()],
            'loisir' => [LeisureVenue::class, fn (Builder $q) => $q->active()],
            'site' => [TouristSite::class, fn (Builder $q) => $q->active()],
            'evenement' => [Event::class, fn (Builder $q) => $q->where('status', 'published')],
        ];
    }

    public function go(Request $request, string $type, string $slug): RedirectResponse
    {
        $place = $this->findPlace($type, $slug);

        $providerId = $place instanceof Provider ? $place->id : $place->getAttribute('provider_id');
        if ($providerId) {
            $this->countDirectionClick((int) $providerId);
        }

        return redirect()->away(GoogleMaps::directionsUrl(
            $place->latitude,
            $place->longitude,
            (string) $request->query('mode', 'driving'),
        ));
    }

    /**
     * Trajet depuis la position du visiteur (géolocalisation du navigateur),
     * tracé ensuite sur la carte Google Maps de la fiche.
     */
    public function route(Request $request, string $type, string $slug, GoogleRoutesService $routes): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'mode' => ['nullable', 'in:driving,walking,bicycling,two_wheeler'],
            'count' => ['nullable', 'boolean'],
        ]);

        $place = $this->findPlace($type, $slug);

        // Le bouton « Itinéraire » de la page ouvre la carte du site : le clic est compté ici (et non plus à la redirection vers Google).
        if (! empty($data['count'])) {
            $providerId = $place instanceof Provider ? $place->id : $place->getAttribute('provider_id');
            if ($providerId) {
                $this->countDirectionClick((int) $providerId);
            }
        }

        $route = $routes->computeRoute(
            [(float) $data['lat'], (float) $data['lng']],
            [(float) $place->latitude, (float) $place->longitude],
            $data['mode'] ?? 'driving',
        );

        if ($route === null) {
            return response()->json(['message' => 'Aucun trajet trouvé depuis votre position.'], 422);
        }

        return response()->json($route + [
            'google_maps_url' => route('directions.go', [$type, $slug, 'mode' => $data['mode'] ?? 'driving']),
        ]);
    }

    private function findPlace(string $type, string $slug): Model
    {
        $source = $this->sources()[$type] ?? abort(404);
        [$modelClass, $published] = $source;

        return $published($modelClass::query())
            ->where('slug', $slug)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->firstOrFail();
    }

    private function countDirectionClick(int $providerId): void
    {
        $row = ProviderAnalytic::firstOrCreate(
            ['provider_id' => $providerId, 'date' => now()->toDateString()],
        );
        $row->increment('clicks_direction');
    }
}
