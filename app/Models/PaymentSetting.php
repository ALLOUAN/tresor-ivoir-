<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PaymentSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Valeur d'un réglage, ou $default s'il n'est pas défini (ou si la table n'existe pas encore). */
    public static function valueOf(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable('payment_settings')) {
            return $default;
        }

        $value = static::where('key', $key)->value('value');

        return $value === null || $value === '' ? $default : (string) $value;
    }

    public static function enabled(string $key, bool $default = false): bool
    {
        return static::valueOf($key, $default ? '1' : '0') === '1';
    }

    /**
     * Mode « inscription gratuite » : les prestataires accèdent à leur espace sans abonnement,
     * la plateforme se rémunère uniquement par commission. Désactivé par défaut.
     */
    public static function providerFreeAccess(): bool
    {
        return static::enabled('provider_free_access');
    }
}
