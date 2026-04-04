<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\Client\BuyCryptoRequest;
use App\Http\Requests\Client\SellCryptoRequest;
use App\Http\Requests\LoginRequest;
use App\Models\CryptoCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

/**
 * Tests unitaires pour les Form Requests
 * 
 * Compétences validées :
 * - C3 : Développer des composants d'accès aux données (validation)
 * - C5 : Développer la partie back-end (sécurité)
 */
class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test : Validation BuyCryptoRequest - succès
     */
    public function test_buy_crypto_request_valid(): void
    {
        // Arrange
        $crypto = CryptoCurrency::factory()->create(['symbol' => 'BTC']);
        $data = [
            'symbol' => 'BTC',
            'quantity' => 0.5,
        ];

        // Act
        $request = new BuyCryptoRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Test : Validation BuyCryptoRequest - symbole manquant
     */
    public function test_buy_crypto_request_missing_symbol(): void
    {
        // Arrange
        $data = [
            'quantity' => 0.5,
        ];

        // Act
        $request = new BuyCryptoRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('symbol', $validator->errors()->toArray());
    }

    /**
     * Test : Validation BuyCryptoRequest - symbole inexistant
     */
    public function test_buy_crypto_request_invalid_symbol(): void
    {
        // Arrange
        $data = [
            'symbol' => 'INVALID',
            'quantity' => 0.5,
        ];

        // Act
        $request = new BuyCryptoRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('symbol', $validator->errors()->toArray());
    }

    /**
     * Test : Validation BuyCryptoRequest - quantité invalide
     */
    public function test_buy_crypto_request_invalid_quantity(): void
    {
        // Arrange
        $crypto = CryptoCurrency::factory()->create(['symbol' => 'BTC']);
        $data = [
            'symbol' => 'BTC',
            'quantity' => -1,
        ];

        // Act
        $request = new BuyCryptoRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('quantity', $validator->errors()->toArray());
    }

    /**
     * Test : Validation SellCryptoRequest - succès
     */
    public function test_sell_crypto_request_valid(): void
    {
        // Arrange
        $crypto = CryptoCurrency::factory()->create(['symbol' => 'ETH']);
        $data = [
            'symbol' => 'ETH',
            'quantity' => 1.0,
        ];

        // Act
        $request = new SellCryptoRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Test : Messages d'erreur personnalisés
     */
    public function test_buy_crypto_request_custom_messages(): void
    {
        // Arrange
        $data = [
            'symbol' => '',
            'quantity' => -1,
        ];

        // Act
        $request = new BuyCryptoRequest();
        $rules = $request->rules();
        $messages = $request->messages();
        $validator = Validator::make($data, $rules, $messages);

        // Assert
        $this->assertFalse($validator->passes());
        $errors = $validator->errors();
        $this->assertStringContainsString('requis', $errors->first('symbol'));
        $this->assertStringContainsString('supérieure à 0', $errors->first('quantity'));
    }
}
