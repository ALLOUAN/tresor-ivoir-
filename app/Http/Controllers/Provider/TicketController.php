<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TouristVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôle des billets à l'entrée d'un site : scan du QR (caméra du téléphone) ou saisie du code,
 * vérification (payé, bon site, bonne date, pas déjà utilisé) puis validation de l'entrée.
 */
class TicketController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');

        return $provider;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $provider = $this->provider();
        if ($code = strtoupper(trim((string) $request->query('code')))) {
            // Accepte le code seul ou l'adresse complète lue dans le QR.
            $code = preg_match('#/billets/([A-Z0-9]{6,16})#i', $code, $m) ? strtoupper($m[1]) : preg_replace('/[^A-Z0-9]/', '', $code);

            return redirect()->route('provider.tickets.show', $code);
        }

        $today = TouristVisit::where('provider_id', $provider->id)->whereIn('status', [TouristVisit::STATUS_PAID, TouristVisit::STATUS_BOOKED])
            ->where(fn ($q) => $q->whereDate('session_date', now()->toDateString())->orWhereDate('desired_date', now()->toDateString()))
            ->orderBy('full_name')->get();

        return view('provider.tickets.index', compact('today'));
    }

    public function show(string $code): View
    {
        $provider = $this->provider();
        $visit = TouristVisit::where('ticket_code', strtoupper($code))->first();
        if (! $visit && ($ride = \App\Models\TransportBooking::where('ticket_code', strtoupper($code))->first())) {
            return view('provider.tickets.show', [
                'visit' => null, 'code' => strtoupper($code), 'check' => $this->checkTransport($ride, $provider),
                'ride' => (int) $ride->provider_id === (int) $provider->id ? $ride : null,
            ]);
        }
        if (! $visit && ($booking = \App\Models\ActivityBooking::where('ticket_code', strtoupper($code))->first())) {
            $check = $this->checkActivity($booking, $provider);

            return view('provider.tickets.show', [
                'visit' => null, 'code' => strtoupper($code), 'check' => $check,
                'booking' => (int) $booking->provider_id === (int) $provider->id ? $booking : null,
            ]);
        }
        $check = $this->check($visit, $provider);
        // Billet d'un autre site : aucune information sur le visiteur n'est affichée.
        if ($visit && (int) $visit->provider_id !== (int) $provider->id) {
            $visit = null;
        }

        return view('provider.tickets.show', ['visit' => $visit, 'code' => strtoupper($code), 'check' => $check]);
    }

    public function checkIn(string $code): RedirectResponse
    {
        $provider = $this->provider();

        $result = DB::transaction(function () use ($code, $provider) {
            $visit = TouristVisit::where('ticket_code', strtoupper($code))->lockForUpdate()->first();
            if (! $visit && ($ride = \App\Models\TransportBooking::where('ticket_code', strtoupper($code))->lockForUpdate()->first())) {
                $check = $this->checkTransport($ride, $provider);
                if (! $check['ok']) {
                    return $check['message'];
                }
                $ride->update(['checked_in_at' => now(), 'trip_status' => 'in_progress']);

                return null;
            }
            if (! $visit && ($booking = \App\Models\ActivityBooking::where('ticket_code', strtoupper($code))->lockForUpdate()->first())) {
                $check = $this->checkActivity($booking, $provider);
                if (! $check['ok']) {
                    return $check['message'];
                }
                $booking->update(['checked_in_at' => now(), 'checked_in_by' => Auth::id()]);

                return null;
            }
            $check = $this->check($visit, $provider);
            if (! $check['ok']) {
                return $check['message'];
            }
            $changes = ['checked_in_at' => now(), 'checked_in_by' => Auth::id()];
            // Réservée « payer sur place » : le visiteur règle au guichet, l'entrée vaut encaissement.
            // L'argent ne transite pas par la plateforme : aucune commission prélevée.
            if ($visit->status === TouristVisit::STATUS_BOOKED) {
                $changes += ['status' => TouristVisit::STATUS_PAID, 'paid_at' => now(), 'gateway' => 'on_site',
                    'commission_amount_xof' => 0, 'provider_net_amount_xof' => (int) $visit->amount_total_xof];
            }
            $visit->update($changes);

            return null;
        });

        return redirect()->route('provider.tickets.show', strtoupper($code))
            ->with($result ? 'error' : 'success', $result ?? 'Entrée validée. Bonne visite !');
    }

    /** Billet de ligne : confirmé, bonne compagnie, jour du départ, pas encore embarqué. */
    private function checkTransport(\App\Models\TransportBooking $b, Provider $provider): array
    {
        if ((int) $b->provider_id !== (int) $provider->id) {
            return ['ok' => false, 'level' => 'error', 'message' => 'Billet inconnu pour votre compagnie.'];
        }
        if ($b->status !== \App\Models\TransportBooking::STATUS_PAID) {
            return ['ok' => false, 'level' => 'error', 'message' => 'Billet non valable (statut : '.$b->statusLabel().').'];
        }
        if ($b->checked_in_at) {
            return ['ok' => false, 'level' => 'warning', 'message' => 'Passager déjà embarqué le '.$b->checked_in_at->format('d/m/Y à H:i').'.'];
        }
        $day = $b->starts_at->timezone('Africa/Abidjan');
        if (! $day->isSameDay(now('Africa/Abidjan'))) {
            return $day->isFuture()
                ? ['ok' => false, 'level' => 'warning', 'message' => 'Billet pour le départ du '.$day->format('d/m/Y à H:i').' : pas encore valable.']
                : ['ok' => false, 'level' => 'error', 'message' => 'Billet d\'un départ passé ('.$day->format('d/m/Y à H:i').').'];
        }

        return ['ok' => true, 'level' => 'success', 'message' => 'Billet valable — départ de '.$day->format('H:i').', siège(s) '.implode(', ', $b->seats ?? []).'.'];
    }

    /** Billet d'activité : confirmé, bon lieu, bon jour, pas encore utilisé ; décharge signée et solde à encaisser signalés. */
    private function checkActivity(\App\Models\ActivityBooking $b, Provider $provider): array
    {
        if ((int) $b->provider_id !== (int) $provider->id) {
            return ['ok' => false, 'level' => 'error', 'message' => 'Billet inconnu pour votre lieu.'];
        }
        if ($b->status !== \App\Models\ActivityBooking::STATUS_PAID) {
            return ['ok' => false, 'level' => 'error', 'message' => 'Billet non valable (statut : '.$b->statusLabel().').'];
        }
        if ($b->checked_in_at) {
            return ['ok' => false, 'level' => 'warning', 'message' => 'Billet déjà utilisé le '.$b->checked_in_at->format('d/m/Y à H:i').'.'];
        }
        if (! $b->booking_date->isToday()) {
            return $b->booking_date->isFuture()
                ? ['ok' => false, 'level' => 'warning', 'message' => 'Billet prévu pour le '.$b->booking_date->format('d/m/Y').' : pas encore valable.']
                : ['ok' => true, 'level' => 'warning', 'message' => 'Billet du '.$b->booking_date->format('d/m/Y').' (date passée) : entrée à votre appréciation.'];
        }
        $extra = ($b->booking_time ? 'Séance de '.str_replace(':', ' h ', $b->booking_time).'. ' : '')
            .($b->balanceOnSite() > 0 ? 'À encaisser : '.number_format($b->balanceOnSite(), 0, ',', ' ').' FCFA. ' : '')
            .($b->needsWaiver() ? 'Décharge NON signée : à faire signer. ' : '');

        return ['ok' => true, 'level' => $b->balanceOnSite() > 0 || $b->needsWaiver() ? 'warning' : 'success', 'message' => trim('Billet valable. '.$extra)];
    }

    /** @return array{ok: bool, level: string, message: string} */
    private function check(?TouristVisit $visit, Provider $provider): array
    {
        if (! $visit || (int) $visit->provider_id !== (int) $provider->id) {
            return ['ok' => false, 'level' => 'error', 'message' => 'Billet inconnu pour votre site.'];
        }
        if (! $visit->hasValidTicket()) {
            return ['ok' => false, 'level' => 'error', 'message' => 'Billet non valable (statut : '.$visit->labelForStatus().').'];
        }
        if ($visit->checked_in_at) {
            return ['ok' => false, 'level' => 'warning', 'message' => 'Billet déjà utilisé le '.$visit->checked_in_at->format('d/m/Y à H:i').'.'];
        }
        $date = $visit->session_date ?? $visit->desired_date;
        if ($date && ! $date->isToday()) {
            // Visite prévue plus tard : refusée. Date passée : acceptée, avec un avertissement.
            return $date->isFuture()
                ? ['ok' => false, 'level' => 'warning', 'message' => 'Billet prévu pour le '.$date->format('d/m/Y').' : pas encore valable.']
                : ['ok' => true, 'level' => 'warning', 'message' => 'Billet prévu pour le '.$date->format('d/m/Y').' (date passée) : entrée possible à votre appréciation.'];
        }

        if ($visit->visit_time && $visit->desired_date?->isToday()) {
            $slot = 'Créneau de '.str_replace(':', ' h ', $visit->visit_time).'.';
        }
        if ($visit->status === TouristVisit::STATUS_BOOKED) {
            return ['ok' => true, 'level' => 'warning', 'message' => trim('Billet valable — à encaisser : '.number_format((int) $visit->amount_total_xof, 0, ',', ' ').' FCFA. '.($slot ?? ''))];
        }

        return ['ok' => true, 'level' => 'success', 'message' => trim('Billet valable. '.($slot ?? ''))];
    }
}
