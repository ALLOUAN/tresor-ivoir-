<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Services\ClientAccountService;
use Illuminate\Foundation\Http\FormRequest;

/** Inscription voyageur depuis l'application (mêmes règles que le site). Les prestataires s'inscrivent sur le site. */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ClientAccountService::uniqueEmailRule(),
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', ClientAccountService::passwordRule()],
            'terms' => ['accepted'],
            'newsletter_opt_in' => ['nullable', 'boolean'],
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.uncompromised' => __('Ce mot de passe figure dans une fuite de données connue : choisissez-en un autre.'),
            'first_name.required' => __('Le prénom est obligatoire.'),
            'last_name.required' => __('Le nom est obligatoire.'),
            'email.required' => __('L\'adresse e-mail est obligatoire.'),
            'email.unique' => __('Cette adresse e-mail est déjà utilisée.'),
            'password.required' => __('Le mot de passe est obligatoire.'),
            'password.confirmed' => __('Les mots de passe ne correspondent pas.'),
            'password.min' => __('Le mot de passe doit contenir au moins 8 caractères.'),
            'terms.accepted' => __('Vous devez accepter les conditions générales d\'utilisation.'),
        ];
    }
}
