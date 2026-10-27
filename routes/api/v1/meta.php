<?php

use App\Http\Controllers\Api\V1\MetaController;
use Illuminate\Support\Facades\Route;

// Informations de démarrage de l'application : version minimale exigée, contacts, liens légaux.
Route::get('meta', MetaController::class)->name('meta');
