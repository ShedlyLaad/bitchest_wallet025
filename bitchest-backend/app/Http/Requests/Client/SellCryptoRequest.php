<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class SellCryptoRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'symbol' => ['required','string','exists:crypto_currencies,symbol'],
            'quantity' => ['required','numeric','gt:0'],
        ];
    }
}
