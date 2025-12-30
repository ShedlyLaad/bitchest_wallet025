<?php

namespace Database\Seeders;

use App\Models\CryptoCurrency;
use App\Models\Portfolio;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have a crypto to trade
        $crypto = CryptoCurrency::first() ?? CryptoCurrency::create([
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
        ]);

        // Create a couple of sample clients
        $clients = [
            ['name' => 'Alice Trader', 'email' => 'alice@example.com'],
            ['name' => 'Bob Trader', 'email' => 'bob@example.com'],
        ];

        foreach ($clients as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'first_name' => explode(' ', $c['name'])[0],
                    'last_name' => explode(' ', $c['name'])[1] ?? '',
                    'password' => Hash::make('password'),
                    'role' => 'client',
                    'status' => User::STATUS_ACTIVE,
                    'euro_balance' => 2000,
                    'must_change_password' => false,
                    'email_verified_at' => now(),
                ]
            );

            $portfolio = Portfolio::firstOrCreate([
                'user_id' => $user->id,
                'crypto_currency_id' => $crypto->id,
            ], [
                'total_crypto_value' => 0,
            ]);

            // Buy 0.01 BTC at 30k
            $buyPrice = 30000;
            $buyQty = 0.01;
            Transaction::create([
                'portfolio_id' => $portfolio->id,
                'type' => 'buy',
                'quantity' => $buyQty,
                'price_at_transaction' => $buyPrice,
                'euro_amount' => $buyQty * $buyPrice,
            ]);

            // Sell 0.005 BTC at 32k -> revenue
            $sellPrice = 32000;
            $sellQty = 0.005;
            Transaction::create([
                'portfolio_id' => $portfolio->id,
                'type' => 'sell',
                'quantity' => $sellQty,
                'price_at_transaction' => $sellPrice,
                'euro_amount' => $sellQty * $sellPrice,
            ]);

            // Update user balance to reflect trades (simple demo)
            $user->euro_balance = max(0, ($user->euro_balance ?? 0) - ($buyQty * $buyPrice) + ($sellQty * $sellPrice));
            $user->save();
        }
    }
}

