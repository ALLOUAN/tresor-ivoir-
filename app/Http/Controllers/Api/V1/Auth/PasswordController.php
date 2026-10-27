<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    /**
     * POST /api/v1/auth/password/forgot { email } : envoie le lien de réinitialisation (page du site).
     * Réponse identique que le compte existe ou non, pour ne pas révéler les adresses inscrites.
     */
    public function forgot(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => __('L\'adresse e-mail est obligatoire.'),
            'email.email' => __('Adresse e-mail invalide.'),
        ]);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => __('Si un compte existe avec cette adresse, un lien de réinitialisation vient d\'être envoyé.'),
        ]);
    }
}
