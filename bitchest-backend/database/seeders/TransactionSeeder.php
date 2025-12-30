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

        // No sample clients added here; keep seeder minimal to avoid unwanted data.
    }
}

