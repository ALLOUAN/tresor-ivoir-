<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

// Compte : connexion par jeton (Sanctum), inscription voyageur, vérification de l'e-mail.
Route::prefix('auth')->name('auth.')->group(function () {
    Route::middleware('throttle:api-auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('password/forgot', [PasswordController::class, 'forgot'])->name('password.forgot');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('email/resend', [EmailVerificationController::class, 'resend'])->name('email.resend')->middleware('throttle:api-auth');
        Route::post('email/verify', [EmailVerificationController::class, 'verify'])->name('email.verify')->middleware('throttle:api-auth');
    });
});
