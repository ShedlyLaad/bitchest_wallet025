<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\TemporaryPasswordMailable;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function createClient(string $name, string $email, float $initialEuro = 500.00): array
    {
        $password = Str::random(12);
        [$firstName, $lastName] = explode(' ', $name . ' ', 2);
        $user = User::create([
            'name' => $name,
            'first_name' => trim($firstName),
            'last_name' => trim($lastName),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'client',
            'status' => User::STATUS_PENDING,
            'must_change_password' => true,
            'euro_balance' => $initialEuro
        ]);

        // send temporary password
        Mail::to($email)->send(new TemporaryPasswordMailable($password, $name));

        return ['user' => $user, 'password' => $password];
    }
}
