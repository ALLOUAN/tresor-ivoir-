<?php

use App\Http\Controllers\Api\V1\Events\EventCategoryController;
use App\Http\Controllers\Api\V1\Events\EventController;
use Illuminate\Support\Facades\Route;

// Événements : liste filtrée, fiche, catégories (consultation publique).
Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('event-categories', [EventCategoryController::class, 'index'])->name('event-categories.index');
