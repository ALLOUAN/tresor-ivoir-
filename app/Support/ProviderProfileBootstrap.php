<?php

namespace App\Support;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use Illuminate\Database\QueryException;
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
        $slugIndex = 1;
        $maxAttempts = 25;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $slug = $slugIndex === 1 ? $slugRoot : $slugRoot.'-'.$slugIndex;

            // Include soft-deleted rows because the DB unique index also blocks those slugs.
            if (Provider::withTrashed()->where('slug', $slug)->exists()) {
                $slugIndex++;
                continue;
            }

            try {
                return Provider::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId,
                    'name' => $baseName,
                    'slug' => $slug,
                    'status' => 'pending',
                    'email' => $user->email,
                    'phone' => $user->phone,
                ]);
            } catch (QueryException $e) {
                // Handle race condition: another process inserted the same slug simultaneously.
                if ((string) $e->getCode() !== '23000' || ! str_contains($e->getMessage(), 'providers_slug_unique')) {
                    throw $e;
                }

                $slugIndex++;
            }
        }

        throw new \RuntimeException('Impossible de générer un slug unique pour ce prestataire.');
    }
}
