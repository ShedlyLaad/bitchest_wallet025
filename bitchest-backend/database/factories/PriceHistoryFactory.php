<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PriceHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'crypto_currency_id' => 1,
            'price' => fake()->randomFloat(8, 1, 50000),
            'recorded_at' => now(),
        ];
    }
}
