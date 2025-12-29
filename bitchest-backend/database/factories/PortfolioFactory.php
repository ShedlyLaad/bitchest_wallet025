<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'crypto_currency_id' => \App\Models\CryptoCurrency::factory(),
            'euro_balance' => fake()->randomFloat(2, 0, 10000),
            'total_crypto_value' => fake()->randomFloat(8, 0, 100000),
        ];
    }
}
