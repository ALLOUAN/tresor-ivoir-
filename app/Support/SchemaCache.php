<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Équivalent de Schema::hasTable() sans interroger information_schema à chaque appel
 * (≈ 10 ms et une requête SQL à chaque fois : plus de 40 par page d'accueil avant cette classe).
 * Mémorisé pendant la requête, puis 10 minutes en cache ; `php artisan optimize:clear` le vide après une migration.
 */
final class SchemaCache
{
    /** @var array<string, bool> */
    private static array $tables = [];

    public static function hasTable(string $table): bool
    {
        return self::$tables[$table] ??= (bool) Cache::remember(
            'schema.has_table.'.$table,
            600,
            fn (): bool => Schema::hasTable($table),
        );
    }
}
