<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\HomepageBubble;
use App\Models\SiteSetting;
use App\Services\RolePermissionMap;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->enforceProductionDebugSafety();
    }

    public function boot(): void
    {
        $this->registerGates();
        $this->registerSiteBrandingComposer();
        $this->registerHomepageBubblesComposer();
        $this->registerAiModeration();
        $this->registerApiRateLimits();
    }

    /** API mobile : 120 requêtes/min par compte (ou par adresse IP), 10/min pour connexion et inscription. */
    private function registerApiRateLimits(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('api', fn (\Illuminate\Http\Request $request) => \Illuminate\Cache\RateLimiting\Limit::perMinute(120)
            ->by($request->user()?->id ?: $request->ip()));

        \Illuminate\Support\Facades\RateLimiter::for('api-auth', fn (\Illuminate\Http\Request $request) => \Illuminate\Cache\RateLimiting\Limit::perMinute(10)
            ->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()));
    }

    /**
     * Modération assistée : chaque enregistrement d'un article, d'un avis, d'un prestataire ou
     * d'un événement déclenche une analyse qui alimente la file de l'administrateur. Elle ne
     * modifie jamais le contenu ni son statut, et une erreur d'analyse n'empêche jamais la sauvegarde.
     */
    private function registerAiModeration(): void
    {
        foreach ([\App\Models\Article::class, \App\Models\Review::class, \App\Models\Provider::class, \App\Models\Event::class] as $model) {
            $model::saved(function ($m) {
                try {
                    if (config('ai.enabled', true) && config('ai.moderation.auto', true) && Schema::hasTable('moderation_reports')) {
                        app(\App\Services\Ai\Moderation\ModerationService::class)->analyzeModel($m);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Modération assistée : analyse impossible ('.$e->getMessage().')');
                }
            });
        }
    }

    /**
     * Filet de sécurité (audit DEPLOY-01) : APP_ENV=production et APP_DEBUG=true ne
     * doivent jamais coexister — sinon toute exception non interceptée affiche la
     * page de debug complète (trace de pile, requêtes SQL, chemins serveur) à
     * n'importe quel visiteur. On force donc app.debug à false dès que
     * l'environnement se déclare "production", indépendamment de la valeur réelle
     * de la variable .env — une mauvaise configuration ne doit jamais suffire à
     * elle seule à exposer des informations sensibles. La correction du .env
     * lui-même reste à faire côté exploitation, ce filet ne fait que garantir
     * qu'un oubli n'a pas de conséquence visible.
     */
    private function enforceProductionDebugSafety(): void
    {
        if (! $this->app->environment('production') || ! config('app.debug')) {
            return;
        }

        config(['app.debug' => false]);

        Log::critical('APP_DEBUG=true détecté avec APP_ENV=production — désactivé automatiquement au démarrage. Corrigez APP_DEBUG dans la configuration de cet environnement.');
    }

    private function registerSiteBrandingComposer(): void
    {
        View::composer('*', function (\Illuminate\View\View $view): void {
            $view->with('siteBrand', SiteSetting::branding());
        });
    }

    /**
     * Les bulles interactives sont incluses depuis partials.public-top-nav (donc sur
     * toutes les pages publiques) : sur l'accueil on affiche toutes les bulles actives
     * (comportement historique) ; ailleurs, uniquement celles dont la sélection de
     * pages couvre la route courante (voir HomepageBubble::appliesToRoute()), ce qui
     * inclut aussi bien la page de liste d'une section que ses pages internes.
     */
    private function registerHomepageBubblesComposer(): void
    {
        View::composer('partials.homepage-bubbles', function (\Illuminate\View\View $view): void {
            $isHome = request()->routeIs('home');
            $currentRoute = optional(request()->route())->getName();

            $bubbles = Schema::hasTable('homepage_bubbles')
                ? HomepageBubble::query()->with('images')->active()->ordered()->get()
                    ->filter(fn (HomepageBubble $bubble) => $isHome || $bubble->appliesToRoute($currentRoute))
                    ->values()
                : collect();

            $view->with('homeBubbles', $bubbles);
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
