<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    private const BRAND_CACHE_KEY = 'site_settings.brand';

    protected $fillable = [
        'site_name',
        'site_slogan',
        'site_description',
        'logo_url',
        'favicon_url',
        'primary_color',
        'secondary_color',
        'timezone',
        'default_language',
        'maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
        ];
    }

    public static function singleton(): self
    {
        $model = static::query()->firstOrCreate(
            ['id' => 1],
            [
                'site_name' => config('app.name'),
                'timezone' => config('app.timezone', 'UTC'),
                'default_language' => 'fr',
                'primary_color' => '#7c3aed',
                'secondary_color' => '#0ea5e9',
                'maintenance_mode' => false,
            ]
        );

        if ($model->wasRecentlyCreated) {
            static::forgetBrandCache();
        }

        return $model;
    }

    /**
     * @return array{
     *     site_name: string,
     *     site_slogan: ?string,
     *     site_description: ?string,
     *     logo_url: ?string,
     *     favicon_url: ?string,
     *     contact: array<string, ?string>,
     *     social: array<string, ?string>
     * }
     */
    public static function branding(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return static::defaultBranding();
        }

        return Cache::remember(self::BRAND_CACHE_KEY, 3600, function (): array {
            $s = static::query()->find(1);

            return array_merge([
                'site_name' => $s?->site_name ?: config('app.name'),
                'site_slogan' => $s?->site_slogan,
                'site_description' => $s?->site_description,
                'logo_url' => $s?->logo_url,
                'favicon_url' => $s?->favicon_url,
            ], static::contactAndSocialPayload());
        });
    }

    public static function forgetBrandCache(): void
    {
        Cache::forget(self::BRAND_CACHE_KEY);
    }

    /**
     * @return array{
     *     site_name: string,
     *     site_slogan: ?string,
     *     site_description: ?string,
     *     logo_url: ?string,
     *     favicon_url: ?string,
     *     contact: array<string, ?string>,
     *     social: array<string, ?string>
     * }
     */
    private static function defaultBranding(): array
    {
        return array_merge([
            'site_name' => config('app.name'),
            'site_slogan' => null,
            'site_description' => null,
            'logo_url' => null,
            'favicon_url' => null,
        ], static::contactAndSocialPayload());
    }

    /**
     * @return array{contact: array<string, ?string>, social: array<string, ?string>}
     */
    private static function contactAndSocialPayload(): array
    {
        $c = Schema::hasTable('site_contact_settings')
            ? SiteContactSetting::query()->find(1)
            : null;
        $o = Schema::hasTable('site_social_settings')
            ? SiteSocialSetting::query()->find(1)
            : null;

        return [
            'contact' => [
                'phone_1' => $c?->phone_1,
                'phone_2' => $c?->phone_2,
                'email_primary' => $c?->email_primary,
                'email_secondary' => $c?->email_secondary,
                'contact_form_email' => $c?->contact_form_email,
                'opening_hours' => $c?->opening_hours,
                'address' => $c?->address,
                'latitude' => $c?->latitude,
                'longitude' => $c?->longitude,
            ],
            'social' => [
                'facebook_url' => $o?->facebook_url,
                'twitter_url' => $o?->twitter_url,
                'linkedin_url' => $o?->linkedin_url,
                'instagram_url' => $o?->instagram_url,
                'youtube_url' => $o?->youtube_url,
                'whatsapp_phone' => $o?->whatsapp_phone,
            ],
        ];
    }

    public static function whatsappHref(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) < 8) {
            return null;
        }

        return 'https://wa.me/'.$digits;
    }
}
