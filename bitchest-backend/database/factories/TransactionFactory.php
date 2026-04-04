<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['buy', 'sell']);
        $quantity = fake()->randomFloat(8, 0.00000001, 100);
        $price = fake()->randomFloat(8, 0.01, 100000);
        $amount = $quantity * $price;

        return [
            'portfolio_id' => Portfolio::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'price_at_transaction' => $price,
            'euro_amount' => round($amount, 2),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function buy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'buy',
        ]);
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sell',
        ]);
    }
}
