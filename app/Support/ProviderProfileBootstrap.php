<?php

namespace App\Support;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use Illuminate\Support\Str;

class ProviderProfileBootstrap
{
    /**
     * Crée une fiche prestataire minimale (brouillon) si l’utilisateur est prestataire et n’en a pas encore.
     */
    public static function ensure(User $user): ?Provider
    {
        if ($user->role !== 'provider') {
            return null;
        }

        $existing = Provider::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        $categoryId = ProviderCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('id');

        if (! $categoryId) {
            return null;
        }

        $baseName = trim($user->first_name.' '.$user->last_name);
        if ($baseName === '') {
            $baseName = 'Prestataire';
        }

        $slugRoot = Str::slug($baseName) ?: 'prestataire';
        $slug = $slugRoot;
        $i = 2;
        while (Provider::where('slug', $slug)->exists()) {
            $slug = $slugRoot.'-'.$i;
            $i++;
        }

        return Provider::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'name' => $baseName,
            'slug' => $slug,
            'status' => 'pending',
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }
}
