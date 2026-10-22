<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Import iCal (Booking.com, Airbnb…) : les dates réservées sur l'autre plateforme deviennent
 * indisponibles ici (champ external_blocks de la chambre, remplacé à chaque synchronisation).
 * En cas d'erreur de téléchargement, les blocages précédents sont conservés (prudence).
 */
class SyncIcalCalendars extends Command
{
    protected $signature = 'ical:sync';

    protected $description = 'Importe les calendriers iCal externes des chambres pour éviter les doubles réservations';

    public function handle(): int
    {
        $rooms = $synced = 0;
        foreach (Accommodation::where('is_active', true)->whereNotNull('room_types')->get() as $a) {
            $types = $a->room_types ?? [];
            $changed = false;
            foreach ($types as $k => $room) {
                $url = $room['ical_url'] ?? null;
                if (! $url || ! preg_match('#^https?://#i', $url)) continue;
                $rooms++;
                try {
                    $res = Http::timeout(20)->withHeaders(['User-Agent' => 'TresorsIvoire-iCal/1.0'])->get($url);
                } catch (\Throwable $e) {
                    Log::warning('iCal injoignable', ['accommodation' => $a->id, 'room' => $room['id'] ?? null, 'error' => $e->getMessage()]);
                    continue;
                }
                if (! $res->successful() || ! str_contains($res->body(), 'BEGIN:VCALENDAR')) {
                    Log::warning('iCal invalide', ['accommodation' => $a->id, 'room' => $room['id'] ?? null, 'status' => $res->status()]);
                    continue;
                }
                $types[$k]['external_blocks'] = self::parse($res->body());
                $types[$k]['ical_synced_at'] = now()->toIso8601String();
                $changed = true;
                $synced++;
            }
            if ($changed) {
                $a->forceFill(['room_types' => $types])->saveQuietly();
            }
        }
        $this->info("Calendriers synchronisés : {$synced}/{$rooms}");

        return self::SUCCESS;
    }

    /**
     * Événements du calendrier → périodes [from, to[ (dates), futures seulement.
     *
     * @return array<int, array{from: string, to: string}>
     */
    public static function parse(string $ics): array
    {
        $ics = preg_replace("/\r?\n[ \t]/", '', $ics); // lignes repliées (RFC 5545)
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $ics, $events);
        $today = now()->toDateString();
        $out = [];
        foreach ($events[1] as $ev) {
            if (! preg_match('/^DTSTART[^:\r\n]*:(\d{8})/m', $ev, $s)) continue;
            $start = substr($s[1], 0, 4).'-'.substr($s[1], 4, 2).'-'.substr($s[1], 6, 2);
            $end = preg_match('/^DTEND[^:\r\n]*:(\d{8})/m', $ev, $e)
                ? substr($e[1], 0, 4).'-'.substr($e[1], 4, 2).'-'.substr($e[1], 6, 2)
                : date('Y-m-d', strtotime($start.' +1 day'));
            if ($end <= $start) $end = date('Y-m-d', strtotime($start.' +1 day'));
            if ($end < $today) continue;
            $out[] = ['from' => $start, 'to' => $end];
        }
        usort($out, fn ($a, $b) => strcmp($a['from'], $b['from']));

        return array_slice($out, 0, 500);
    }
}
