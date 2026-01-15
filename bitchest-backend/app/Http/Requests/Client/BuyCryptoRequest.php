<?php
namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CryptoCurrency;

class BuyCryptoRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'symbol' => ['required', 'string', 'max:10', 'exists:crypto_currencies,symbol'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999999'],
        ];
    }

    public function messages()
    {
        return [
            'symbol.required' => 'Le symbole de la cryptomonnaie est requis.',
            'symbol.exists' => 'Cette cryptomonnaie n\'existe pas ou n\'est pas disponible.',
            'quantity.required' => 'La quantité est requise.',
            'quantity.numeric' => 'La quantité doit être un nombre.',
            'quantity.gt' => 'La quantité doit être supérieure à 0.',
            'quantity.max' => 'La quantité est trop élevée.',
        ];
    }
}
