<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer l'admin s'il n'existe pas
        User::firstOrCreate(
            ['email' => 'admin@bitchest.com'],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '+216 48 062 093',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => User::STATUS_ACTIVE,
                'must_change_password' => false,
                'euro_balance' => 0, // Admin n'a pas de balance
                'email_verified_at' => now()
            ]
        );

    
        User::where('role', 'client')->update(['euro_balance' => 500.0]);
    }
}
