<?php

namespace App\Console\Commands;

use App\Models\GuestRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Déplace les pièces des fiches d'enregistrement envoyées avant le passage en stockage privé
 * (dossier public « /storage/guest-registrations/… ») vers storage/app/private.
 * Copie, vérifie, met à jour la fiche, puis supprime l'ancien fichier public.
 */
class PrivatizeGuestRegistrationFiles extends Command
{
    protected $signature = 'guest-registrations:privatize {--dry-run : Affiche ce qui serait déplacé sans rien modifier}';

    protected $description = 'Range les pièces d\'identité, selfies et signatures des fiches voyageurs sur le disque privé';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $dry = (bool) $this->option('dry-run');
        $moved = 0;
        $missing = 0;

        GuestRegistration::query()->orderBy('id')->chunkById(100, function ($registrations) use ($public, $private, $dry, &$moved, &$missing) {
            foreach ($registrations as $reg) {
                $changes = [];
                foreach (GuestRegistration::FILE_COLUMNS as $column) {
                    $value = (string) $reg->{$column};
                    if ($value === '' || str_starts_with($value, GuestRegistration::PRIVATE_PREFIX)) {
                        continue;
                    }
                    $path = ltrim(preg_replace('#^/?storage/#', '', $value), '/');
                    if (! $public->exists($path)) {
                        $missing++;
                        $this->warn("Fiche #{$reg->id} : fichier introuvable ({$path}).");

                        continue;
                    }
                    if (! $dry) {
                        $private->put($path, $public->get($path));
                        if (! $private->exists($path) || $private->size($path) !== $public->size($path)) {
                            $this->error("Fiche #{$reg->id} : copie incomplète de {$path}, fichier public conservé.");

                            continue;
                        }
                        $changes[$column] = GuestRegistration::PRIVATE_PREFIX.$path;
                    }
                    $moved++;
                    $this->line("Fiche #{$reg->id} : {$path}");
                }
                if ($changes) {
                    $reg->forceFill($changes)->save();
                    foreach ($changes as $newValue) {
                        $public->delete(substr($newValue, strlen(GuestRegistration::PRIVATE_PREFIX)));
                    }
                }
            }
        });

        $this->info(($dry ? '[Simulation] ' : '')."{$moved} fichier(s) ".($dry ? 'à déplacer' : 'déplacé(s)')." vers le stockage privé, {$missing} introuvable(s).");

        return self::SUCCESS;
    }
}
