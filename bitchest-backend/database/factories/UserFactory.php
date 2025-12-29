<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $name = fake()->name(),
            'first_name' => explode(' ', $name)[0] ?? $name,
            'last_name' => explode(' ', $name)[1] ?? $name,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => User::STATUS_ACTIVE,
            'must_change_password' => false,
            'email_verified_at' => now(),
        ];
    }
}
