<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Reservation;
use Illuminate\Http\Response;

/**
 * Export iCal des dates occupées d'une chambre (réservations actives + dates fermées), à importer
 * sur Booking.com, Airbnb ou Google Agenda. Lien secret par chambre (jeton HMAC), sans connexion.
 */
class IcalController extends Controller
{
    public static function token(int $accommodationId, string $roomId): string
    {
        return substr(hash_hmac('sha256', 'ical|'.$accommodationId.'|'.$roomId, (string) config('app.key')), 0, 32);
    }

    public static function exportUrl(Accommodation $accommodation, array $room): string
    {
        return route('ical.export', [$accommodation->id, $room['id'], self::token($accommodation->id, (string) $room['id'])]);
    }

    public function export(int $accommodation, string $room, string $token): Response
    {
        abort_unless(hash_equals(self::token($accommodation, $room), $token), 404);
        $a = Accommodation::findOrFail($accommodation);
        $r = $a->findRoomById($room);
        abort_unless($r, 404);

        $ranges = collect(Reservation::blockedRanges($a->id, (string) $r['name'], $r['id'] ?? null, Reservation::roomUnits($r)))
            ->map(fn ($x) => $x + ['summary' => 'Réservé — Trésors d\'Ivoire'])
            // Les dates fermées par l'établissement, mais pas celles importées d'ailleurs (évite les boucles).
            ->merge(collect(Reservation::closedRanges(array_diff_key($r, ['external_blocks' => 1])))->map(fn ($x) => $x + ['summary' => 'Fermé']));

        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'tresorsivoire.com';
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Tresors d Ivoire//Disponibilites//FR', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escape($a->name.' — '.$r['name'])];
        foreach ($ranges as $i => $x) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.md5($a->id.$r['id'].$x['check_in'].$x['check_out'].$i).'@'.$host;
            $lines[] = 'DTSTAMP:'.gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART;VALUE=DATE:'.str_replace('-', '', $x['check_in']);
            $lines[] = 'DTEND;VALUE=DATE:'.str_replace('-', '', $x['check_out']);
            $lines[] = 'SUMMARY:'.$this->escape($x['summary']);
            $lines[] = 'END:VEVENT';
        }
        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="disponibilites.ics"',
            'Cache-Control' => 'no-cache, max-age=0',
        ]);
    }

    private function escape(string $s): string
    {
        return str_replace([',', ';', "\n"], ['\,', '\;', '\n'], $s);
    }
}
