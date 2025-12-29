<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(8, 1, 50000);
        $qty = fake()->randomFloat(8, 0.0001, 2);

        return [
            'portfolio_id' => \App\Models\Portfolio::factory(),
            'type' => fake()->randomElement(['buy', 'sell']),
            'quantity' => $qty,
            'price_at_transaction' => $price,
            'euro_amount' => $qty * $price,
        ];
    }
}
