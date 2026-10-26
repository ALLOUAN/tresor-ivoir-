<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Supprime définitivement les comptes clients et prestataires dont l'adresse e-mail n'a jamais été
 * confirmée, X jours après leur création (30 par défaut). Minimisation des données : un compte jamais
 * activé n'a pas à être conservé indéfiniment.
 *
 * Par prudence, un compte qui a laissé la moindre trace d'activité (réservation, achat, paiement,
 * avis, message, contenu, fiche d'établissement, portefeuille…) n'est jamais supprimé : il est signalé
 * pour traitement manuel. Les administrateurs, éditeurs et membres d'équipe invités (créés déjà
 * vérifiés) ne sont jamais concernés.
 *
 *   php artisan accounts:purge-unverified --dry-run   (aperçu, rien n'est supprimé)
 */
class PurgeUnverifiedAccounts extends Command
{
    protected $signature = 'accounts:purge-unverified {--days=30 : Ancienneté minimale du compte, en jours} {--dry-run : Affiche les comptes concernés sans rien supprimer}';

    protected $description = 'Supprime les comptes clients et prestataires non vérifiés après 30 jours (sans activité)';

    /** Tables dont une ligne liée au compte (colonne utilisateur) empêche la suppression. */
    private const USER_ACTIVITY = [
        'reservations' => 'user_id', 'activity_bookings' => 'user_id', 'booking_requests' => 'user_id',
        'tour_bookings' => 'user_id', 'tourist_visits' => 'user_id', 'transport_bookings' => 'user_id',
        'leisure_pass_purchases' => 'user_id', 'media_purchases' => 'user_id', 'artwork_orders' => 'buyer_user_id',
        'gift_cards' => 'buyer_user_id', 'disputes' => 'user_id', 'reviews' => 'user_id',
        'article_comments' => 'user_id', 'art_commissions' => 'user_id', 'artwork_offers' => 'user_id',
        'conversations' => 'client_id', 'wallets' => 'user_id', 'wallet_topups' => 'user_id',
        'payout_requests' => 'user_id', 'articles' => 'author_id', 'events' => 'created_by',
        'media' => 'uploaded_by', 'newsletter_campaigns' => 'created_by', 'review_replies' => 'replied_by',
        'videos' => 'submitted_by', 'site_media_items' => 'uploaded_by', 'article_uploader' => 'user_id',
        'provider_members' => 'user_id',
    ];

    /** Tables dont une ligne liée à l'établissement du prestataire empêche la suppression. */
    private const PROVIDER_ACTIVITY = [
        'payments', 'invoices', 'payout_requests', 'provider_debts', 'wallets', 'artwork_orders', 'review_replies',
        'reservations', 'activity_bookings', 'tour_bookings', 'transport_bookings', 'tourist_visits',
        'booking_requests', 'leisure_pass_purchases', 'disputes', 'reviews', 'conversations',
        'accommodations', 'restaurants', 'activities', 'artworks', 'events', 'tour_packages',
        'leisure_venues', 'leisure_packages', 'leisure_passes', 'transport_companies', 'transport_offers',
        'travel_agencies', 'tourist_experiences', 'menu_items', 'bundles', 'gift_cards', 'videos',
        'provider_documents', 'subscriptions',
    ];

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $deleted = [];
        $kept = [];

        User::withTrashed()
            ->whereIn('role', ['visitor', 'provider'])
            ->whereNull('email_verified_at')
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($dryRun, &$deleted, &$kept) {
                foreach ($users as $user) {
                    $providerIds = Provider::withTrashed()->where('user_id', $user->id)->pluck('id')->all();
                    $blocker = $this->activityBlocker($user, $providerIds);

                    if ($blocker !== null) {
                        $kept[] = "#{$user->id} {$user->email} ({$blocker})";

                        continue;
                    }

                    $deleted[] = "#{$user->id} {$user->email} ({$user->role}, créé le {$user->created_at?->format('d/m/Y')})";

                    if (! $dryRun) {
                        DB::transaction(function () use ($user, $providerIds) {
                            Provider::withTrashed()->whereIn('id', $providerIds)->get()->each->forceDelete();
                            $user->forceDelete();
                        });
                    }
                }
            });

        $verb = $dryRun ? 'à supprimer (aperçu)' : 'supprimé(s)';
        $this->info(count($deleted)." compte(s) non vérifié(s) depuis plus de {$days} jours {$verb}.");
        foreach ($deleted as $line) {
            $this->line("  - {$line}");
        }
        if ($kept !== []) {
            $this->warn(count($kept).' compte(s) non vérifié(s) conservé(s) car ils ont une activité (à traiter manuellement) :');
            foreach ($kept as $line) {
                $this->line("  - {$line}");
            }
        }

        if (! $dryRun && ($deleted !== [] || $kept !== [])) {
            Log::info('accounts:purge-unverified', ['days' => $days, 'deleted' => $deleted, 'kept' => $kept]);
        }

        return self::SUCCESS;
    }

    /** Première trace d'activité trouvée (nom de la table), ou null si le compte n'a rien laissé. */
    private function activityBlocker(User $user, array $providerIds): ?string
    {
        foreach (self::USER_ACTIVITY as $table => $column) {
            if ($this->hasRows($table, $column, [$user->id])) {
                return $table;
            }
        }

        if ($providerIds !== []) {
            foreach (self::PROVIDER_ACTIVITY as $table) {
                if ($this->hasRows($table, 'provider_id', $providerIds)) {
                    return $table;
                }
            }
            // Équipe : d'autres personnes rattachées à l'établissement.
            if ($this->hasRows('provider_members', 'provider_id', $providerIds)) {
                return 'provider_members';
            }
        }

        return null;
    }

    private function hasRows(string $table, string $column, array $ids): bool
    {
        static $columns = [];
        $key = "{$table}.{$column}";
        $columns[$key] ??= Schema::hasTable($table) && Schema::hasColumn($table, $column);

        return $columns[$key] && DB::table($table)->whereIn($column, $ids)->exists();
    }
}
