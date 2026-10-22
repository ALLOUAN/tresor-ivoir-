<?php

namespace App\Console\Commands;

use App\Models\BookingRequest;
use App\Models\Conversation;
use App\Models\Provider;
use Illuminate\Console\Command;

/**
 * Délai de réponse médian (demandes de réservation + messages clients, 90 derniers jours)
 * et taux de réponse de chaque établissement : alimente le badge « Répond en moins d'1 h ».
 */
class ComputeProviderResponseStats extends Command
{
    protected $signature = 'providers:response-stats';

    protected $description = 'Calcule le délai de réponse médian de chaque établissement';

    /** En dessous, l'échantillon n'est pas représentatif : pas de badge. */
    public const MIN_SAMPLES = 3;

    /** Une demande sans réponse après ce délai compte comme « pas de réponse ». */
    public const UNANSWERED_AFTER_HOURS = 48;

    public function handle(): int
    {
        $since = now()->subDays(90);
        $n = 0;

        Provider::query()->where('status', 'active')->select(['id', 'user_id'])->chunkById(200, function ($providers) use ($since, &$n) {
            foreach ($providers as $provider) {
                [$delays, $missed] = $this->samples($provider, $since);
                $total = count($delays) + $missed;
                $provider->forceFill([
                    'response_minutes' => count($delays) >= self::MIN_SAMPLES ? self::median($delays) : null,
                    'response_rate' => $total >= self::MIN_SAMPLES ? (int) round(count($delays) * 100 / $total) : null,
                    'response_stats_at' => now(),
                ])->saveQuietly();
                $n++;
            }
        });

        $this->info("$n établissement(s) mis à jour.");

        return self::SUCCESS;
    }

    /** @return array{0: list<int>, 1: int} délais en minutes, nombre de demandes restées sans réponse */
    private function samples(Provider $provider, $since): array
    {
        $delays = [];
        $missed = 0;
        $cutoff = now()->subHours(self::UNANSWERED_AFTER_HOURS);

        BookingRequest::where('provider_id', $provider->id)->where('created_at', '>=', $since)
            ->get(['created_at', 'answered_at', 'status'])
            ->each(function ($b) use (&$delays, &$missed, $cutoff) {
                if ($b->answered_at) {
                    $delays[] = max(0, (int) $b->created_at->diffInMinutes($b->answered_at));
                } elseif ($b->status === BookingRequest::STATUS_NEW && $b->created_at->lt($cutoff)) {
                    $missed++;
                }
            });

        // Messages : délai entre le premier message d'un tour du client et la réponse suivante de l'établissement.
        Conversation::where('provider_id', $provider->id)->where('updated_at', '>=', $since)
            ->latest('updated_at')->limit(100)->get(['id', 'client_id'])
            ->each(function ($c) use (&$delays, &$missed, $cutoff, $since) {
                $waitingSince = null;
                foreach ($c->messages()->where('created_at', '>=', $since)->oldest('id')->get(['sender_id', 'created_at']) as $m) {
                    $fromClient = (int) $m->sender_id === (int) $c->client_id;
                    if ($fromClient && ! $waitingSince) {
                        $waitingSince = $m->created_at;
                    } elseif (! $fromClient && $waitingSince) {
                        $delays[] = max(0, (int) $waitingSince->diffInMinutes($m->created_at));
                        $waitingSince = null;
                    }
                }
                if ($waitingSince && $waitingSince->lt($cutoff)) {
                    $missed++;
                }
            });

        return [$delays, $missed];
    }

    /** @param list<int> $values */
    public static function median(array $values): int
    {
        sort($values);
        $mid = intdiv(count($values), 2);

        return count($values) % 2 ? $values[$mid] : (int) round(($values[$mid - 1] + $values[$mid]) / 2);
    }
}
