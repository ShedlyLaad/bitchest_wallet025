<?php

namespace Database\Factories;

use App\Models\Portfolio;
use App\Models\User;
use App\Models\CryptoCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'crypto_currency_id' => CryptoCurrency::factory(),
            'total_crypto_value' => fake()->randomFloat(8, 0, 100000),
        ];
    }
}
