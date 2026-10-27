<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de l'application mobile — /api/v1/…
|--------------------------------------------------------------------------
| Un fichier de routes par module dans routes/api/v1/. Une modification qui casserait les
| applications déjà installées se fait dans une v2, la v1 restant en service.
| Noms des routes : api.v1.<module>.<action>. Documentation : docs/api/README.md.
*/

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {
    foreach (glob(__DIR__.'/api/v1/*.php') as $moduleRoutes) {
        require $moduleRoutes;
    }
});
