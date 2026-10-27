<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            // Nom de l'appareil (ex. « Pixel 8 de Awa ») : affiché dans la liste des sessions, un jeton par appareil.
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('L\'adresse e-mail est obligatoire.'),
            'email.email' => __('Adresse e-mail invalide.'),
            'password.required' => __('Le mot de passe est obligatoire.'),
        ];
    }
}
