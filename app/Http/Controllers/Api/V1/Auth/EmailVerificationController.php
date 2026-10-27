<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Vérification de l'adresse e-mail par code à 6 chiffres (même code et même règle que le site). */
class EmailVerificationController extends Controller
{
    public function __construct(private EmailVerificationCodeService $codes) {}

    /** POST /api/v1/auth/email/resend */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('Votre adresse e-mail est déjà vérifiée.')]);
        }

        return $this->codes->send($user)
            ? response()->json(['message' => __('Un nouveau code a été envoyé à votre adresse e-mail.')])
            : response()->json(['message' => __('L\'e-mail n\'a pas pu être envoyé pour le moment. Réessayez dans quelques minutes.')], 503);
    }

    /** POST /api/v1/auth/email/verify { code } → { message, user } */
    public function verify(Request $request): JsonResponse
    {
        $request->merge(['code' => preg_replace('/\D/', '', (string) $request->input('code'))]);
        $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => __('Veuillez saisir le code de vérification.'),
            'code.digits' => __('Le code de vérification doit contenir 6 chiffres.'),
        ]);

        $user = $request->user();
        if (! $user->hasVerifiedEmail()) {
            $result = $this->codes->verify($user, $request->string('code'));
            if ($result === EmailVerificationCodeService::EXPIRED) {
                throw ValidationException::withMessages(['code' => __('Le code a expiré. Demandez-en un nouveau.')]);
            }
            if ($result === EmailVerificationCodeService::INVALID) {
                throw ValidationException::withMessages(['code' => __('Le code saisi est incorrect.')]);
            }
        }

        return response()->json([
            'message' => __('Votre adresse e-mail est vérifiée.'),
            'user' => UserResource::make($user->fresh())->resolve(),
        ]);
    }
}
