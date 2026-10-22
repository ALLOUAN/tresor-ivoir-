<?php

namespace App\Services\Maps;

use App\Models\ProviderHour;
use App\Models\TouristSite;
use App\Services\Ai\CatalogItem;
use Carbon\CarbonImmutable;

/**
 * « Ouvert maintenant » pour un élément du catalogue, à l'heure d'Abidjan :
 *  - sites touristiques : horaires saisis sur la fiche (schedules, jours en français) ;
 *  - établissements rattachés à un prestataire : horaires du prestataire (provider_hours) ;
 *  - hébergements : réception supposée ouverte en continu ;
 *  - événements : en cours entre leur début et leur fin.
 * Sans horaires connus, le statut reste « inconnu » (jamais affiché comme fermé à tort).
 */
class OpenNow
{
    public const OPEN = 'open';

    public const CLOSED = 'closed';

    public const UNKNOWN = 'unknown';

    private const FR_DAYS = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

    /** @var array<int, array<int, array{open: ?string, close: ?string, closed: bool}>> heures par prestataire et par jour */
    private array $providerHours = [];

    /** @var array<int, array> horaires par site */
    private array $siteSchedules = [];

    private CarbonImmutable $now;

    public function __construct(?CarbonImmutable $now = null)
    {
        $this->now = $now ?? CarbonImmutable::now('Africa/Abidjan');
    }

    /** Précharge les horaires des éléments affichés (une requête par source). */
    public function preload(array $items): void
    {
        $providerIds = array_values(array_unique(array_filter(array_map(fn (CatalogItem $i) => $i->providerId, $items))));
        if ($providerIds) {
            foreach (ProviderHour::whereIn('provider_id', $providerIds)->get() as $h) {
                $this->providerHours[$h->provider_id][(int) $h->day_of_week] = [
                    'open' => $h->open_time ? substr((string) $h->open_time, 0, 5) : null,
                    'close' => $h->close_time ? substr((string) $h->close_time, 0, 5) : null,
                    'closed' => (bool) $h->is_closed,
                ];
            }
        }
        $siteIds = array_map(fn (CatalogItem $i) => $i->id, array_filter($items, fn (CatalogItem $i) => $i->type === 'site'));
        if ($siteIds) {
            foreach (TouristSite::whereIn('id', $siteIds)->get(['id', 'schedules']) as $s) {
                $this->siteSchedules[$s->id] = is_array($s->schedules) ? $s->schedules : [];
            }
        }
    }

    /** @return array{status: string, until: ?string} until = heure de fermeture (si ouvert) ou d'ouverture (si fermé aujourd'hui) */
    public function status(CatalogItem $item): array
    {
        if ($item->type === 'event') {
            $t = $this->now->getTimestamp();
            if ($item->startsAt && $item->startsAt <= $t && (! $item->endsAt || $item->endsAt >= $t)) return ['status' => self::OPEN, 'until' => null];

            return ['status' => self::CLOSED, 'until' => null];
        }
        if ($item->type === 'accommodation') {
            return ['status' => self::OPEN, 'until' => null];
        }

        $day = $this->todayHours($item);
        if ($day === null) return ['status' => self::UNKNOWN, 'until' => null];
        if ($day['closed'] || ! $day['open'] || ! $day['close']) return ['status' => self::CLOSED, 'until' => null];

        $hm = $this->now->format('H:i');
        // Horaire de nuit (ex. 18:00 → 02:00) : ouvert si après l'ouverture OU avant la fermeture.
        $overnight = $day['close'] <= $day['open'];
        $open = $overnight ? ($hm >= $day['open'] || $hm < $day['close']) : ($hm >= $day['open'] && $hm < $day['close']);

        if ($open) return ['status' => self::OPEN, 'until' => $day['close']];

        return ['status' => self::CLOSED, 'until' => $hm < $day['open'] ? $day['open'] : null];
    }

    /** @return array{open: ?string, close: ?string, closed: bool}|null */
    private function todayHours(CatalogItem $item): ?array
    {
        $dow = (int) $this->now->dayOfWeek; // 0 = dimanche

        if ($item->type === 'site' && isset($this->siteSchedules[$item->id])) {
            foreach ($this->siteSchedules[$item->id] as $row) {
                if (mb_strtolower(trim((string) ($row['day'] ?? ''))) === self::FR_DAYS[$dow]) {
                    return [
                        'open' => $row['opens'] ?? null,
                        'close' => $row['closes'] ?? null,
                        'closed' => (bool) ($row['closed'] ?? false),
                    ];
                }
            }

            return null;
        }

        if ($item->providerId && isset($this->providerHours[$item->providerId])) {
            return $this->providerHours[$item->providerId][$dow] ?? null;
        }

        return null;
    }
}
