<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class SellCryptoRequest extends FormRequest
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
            'symbol.required' => 'Cryptocurrency symbol is required.',
            'symbol.exists' => 'This cryptocurrency does not exist or is not available.',
            'quantity.required' => 'Quantity is required.',
            'quantity.numeric' => 'Quantity must be a number.',
            'quantity.gt' => 'Quantity must be greater than 0.',
            'quantity.max' => 'Quantity is too high.',
        ];
    }
}
