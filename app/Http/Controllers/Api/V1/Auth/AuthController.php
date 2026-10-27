<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AccountSecurityEvent;
use App\Models\User;
use App\Services\AccountSecurityEventLogger;
use App\Services\ClientAccountService;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Connexion par jeton (Sanctum) pour l'application mobile. Mêmes règles que le site :
 * compte actif obligatoire ; les administrateurs (double authentification) se connectent sur le site.
 */
class AuthController extends Controller
{
    /** POST /api/v1/auth/login → { token, token_type, expires_at, user } */
    public function login(LoginRequest $request): JsonResponse
    {
        $guard = Auth::guard('web');
        if (! $guard->validate($request->only('email', 'password'))) {
            throw ValidationException::withMessages(['email' => __('Email ou mot de passe incorrect.')]);
        }

        /** @var User $user */
        $user = $guard->getLastAttempted();

        if (! $user->is_active) {
            return response()->json(['message' => __('Ce compte est désactivé. Contactez-nous si vous pensez qu\'il s\'agit d\'une erreur.')], 403);
        }
        if ($user->role === 'admin') {
            return response()->json(['message' => __('Les comptes administrateurs se connectent sur le site web.')], 403);
        }

        $user->update(['last_login_at' => now()]);
        AccountSecurityEventLogger::log($user, AccountSecurityEvent::TYPE_LOGIN, $request);

        return response()->json($this->tokenPayload($user, $request->string('device_name')));
    }

    /** POST /api/v1/auth/register → 201 { token, …, user } ; un code de vérification part par e-mail. */
    public function register(RegisterRequest $request, ClientAccountService $accounts, EmailVerificationCodeService $codes): JsonResponse
    {
        $data = $request->validated();
        $user = $accounts->createVisitor($data);

        if ($request->boolean('newsletter_opt_in')) {
            $accounts->subscribeNewsletter($user, 'app');
        }

        // Une panne du serveur d'e-mails ne bloque pas l'inscription : le code se renvoie depuis l'application.
        $sent = $codes->send($user);

        return response()->json([
            ...$this->tokenPayload($user, $data['device_name']),
            'message' => $sent
                ? __('Bienvenue ! Saisissez le code à 6 chiffres envoyé à votre adresse e-mail pour activer votre compte.')
                : __('Votre compte est créé. L\'e-mail de vérification n\'a pas pu être envoyé : réessayez dans quelques minutes.'),
        ], 201);
    }

    /** GET /api/v1/auth/me */
    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }

    /** POST /api/v1/auth/logout : supprime le jeton de cet appareil uniquement. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => __('Vous êtes déconnecté.')]);
    }

    private function tokenPayload(User $user, string $deviceName): array
    {
        $ttlDays = config('mobile.token_ttl_days');
        $expiresAt = $ttlDays ? now()->addDays((int) $ttlDays) : null;
        $token = $user->createToken(mb_substr($deviceName, 0, 100), ['*'], $expiresAt);

        return [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt?->format(DATE_ATOM),
            'user' => UserResource::make($user->fresh())->resolve(),
        ];
    }
}
