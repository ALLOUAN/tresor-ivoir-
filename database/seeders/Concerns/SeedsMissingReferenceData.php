<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ajoute les fiches de référence manquantes sans jamais écraser ce qui existe déjà :
 * une fiche présente (même modifiée depuis l'administration) est conservée telle quelle,
 * on se contente de compléter ses champs vides (sources, et éventuellement région ou coordonnées).
 */
trait SeedsMissingReferenceData
{
    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  list<array<string, mixed>>  $rows  chaque ligne doit contenir un 'slug'
     * @param  list<string>  $fillIfEmpty  champs complétés sur une fiche existante seulement s'ils sont vides
     * @return array{created:int, sourced:int, kept:int}
     */
    protected function seedMissing(string $model, array $rows, array $fillIfEmpty = []): array
    {
        $stats = ['created' => 0, 'sourced' => 0, 'kept' => 0];
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model), true);

        foreach ($rows as $row) {
            $query = $softDeletes ? $model::withTrashed() : $model::query();
            $existing = $query->where('slug', $row['slug'])->first();

            if (! $existing) {
                $model::create($row);
                $stats['created']++;

                continue;
            }

            $fill = [];
            foreach (['sources', ...$fillIfEmpty] as $field) {
                if (blank($existing->{$field}) && ! blank($row[$field] ?? null)) {
                    $fill[$field] = $row[$field];
                }
            }
            if ($fill) {
                $existing->forceFill($fill)->save();
                $stats['sourced']++;
            } else {
                $stats['kept']++;
            }
        }

        return $stats;
    }

    /** @param array{created:int, sourced:int, kept:int} $stats */
    protected function report(string $label, array $stats): void
    {
        $this->command?->info(sprintf('✓ %s : %d ajouté(s), %d complété(s) (champs vides seulement), %d déjà présent(s) inchangé(s).',
            $label, $stats['created'], $stats['sourced'], $stats['kept']));
    }
}
