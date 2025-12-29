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
            'symbol' => ['required','string','exists:crypto_currencies,symbol'],
            'quantity' => ['required','numeric','gt:0'],
        ];
    }
}
