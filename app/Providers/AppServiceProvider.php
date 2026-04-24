<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\SiteSetting;
use App\Services\RolePermissionMap;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->registerGates();
        $this->registerSiteBrandingComposer();
    }

    private function registerSiteBrandingComposer(): void
    {
        View::composer('*', function (\Illuminate\View\View $view): void {
            $view->with('siteBrand', SiteSetting::branding());
        });
    }

    private function registerGates(): void
    {
        // Super-gate : l'admin passe toujours
        Gate::before(function ($user, $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });

        // Un gate par permission
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, function ($user) use ($permission) {
                return RolePermissionMap::roleHas($user->role, $permission->value);
            });
        }

        // Gates contextuels (own vs any)
        Gate::define('update-article', function ($user, $article) {
            if ($user->role === 'admin') {
                return true;
            }
            if (in_array($user->role, ['editor'])) {
                return $article->author_id === $user->id
                    ? Gate::check(Permission::ArticlesEditOwn->value)
                    : Gate::check(Permission::ArticlesEditAny->value);
            }

            return false;
        });

        Gate::define('delete-article', function ($user, $article) {
            if ($user->role === 'admin') {
                return true;
            }
            if ($user->role === 'editor' && $article->author_id === $user->id) {
                return Gate::check(Permission::ArticlesDeleteOwn->value);
            }

            return false;
        });

        Gate::define('update-provider', function ($user, $provider) {
            if ($user->role === 'admin') {
                return true;
            }
            if ($user->role === 'provider' && $provider->user_id === $user->id) {
                return Gate::check(Permission::ProvidersEditOwn->value);
            }

            return false;
        });

        Gate::define('delete-review', function ($user, $review) {
            if ($user->role === 'admin') {
                return true;
            }
            if ($review->user_id === $user->id) {
                return Gate::check(Permission::ReviewsDeleteOwn->value);
            }

            return false;
        });
    }
}
