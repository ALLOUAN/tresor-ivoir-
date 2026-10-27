<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InformationPage;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

/**
 * GET /api/v1/meta — appelé au démarrage de l'application :
 * version minimale (mise à jour obligatoire en dessous), maintenance, contacts, réseaux, pages légales.
 */
class MetaController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $brand = SiteSetting::branding();

        $pages = Schema::hasTable('information_pages')
            ? InformationPage::query()->orderBy('sort_order')->orderBy('id')->get()
                ->map(fn (InformationPage $p) => [
                    'slug' => $p->slug,
                    'title' => $p->localized('title'),
                    'url' => route('information.show', $p),
                ])->values()
            : [];

        return response()->json(['data' => [
            'api_version' => 'v1',
            'app' => [
                'android' => config('mobile.android'),
            ],
            'site' => [
                'name' => $brand['site_name'] ?? config('app.name'),
                'url' => url('/'),
                'maintenance' => (bool) ($brand['maintenance_mode'] ?? false),
                'contact' => $brand['contact'] ?? [],
                'social' => $brand['social'] ?? [],
            ],
            'pages' => $pages,
            'locales' => \App\Http\Middleware\Api\SetApiLocale::SUPPORTED,
            'currency' => 'XOF',
        ]]);
    }
}
