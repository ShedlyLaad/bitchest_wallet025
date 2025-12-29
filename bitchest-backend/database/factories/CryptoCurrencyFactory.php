<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoCurrencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'symbol' => strtoupper(fake()->lexify('???')),
        ];
    }
}
