<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est requis.',
        ];
    }
}
