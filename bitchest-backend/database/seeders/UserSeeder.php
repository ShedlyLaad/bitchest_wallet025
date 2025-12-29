<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@bitchest.com',
            'phone' => '+216 48 062 093',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => User::STATUS_ACTIVE,
            'must_change_password' => false,
            'email_verified_at' => now()
        ]);

        // User::factory(10)->create(); // 10 clients
    }
}
