<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CryptoCurrency;

class CryptoAndPricesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Keep exactly the 10 cryptos used everywhere (admin + client + history)
        $cryptos = [
            ['name' => 'Bitcoin',       'symbol' => 'BTC',   'price' => 43000.00],
            ['name' => 'Ethereum',      'symbol' => 'ETH',   'price' => 2300.00],
            ['name' => 'Ripple',        'symbol' => 'XRP',   'price' => 0.50],
            ['name' => 'Bitcoin Cash',  'symbol' => 'BCH',   'price' => 250.00],
            ['name' => 'Cardano',       'symbol' => 'ADA',   'price' => 0.80],
            ['name' => 'Litecoin',      'symbol' => 'LTC',   'price' => 80.00],
            ['name' => 'NEM',           'symbol' => 'XEM',   'price' => 0.10],
            ['name' => 'Stellar',       'symbol' => 'XLM',   'price' => 0.10],
            ['name' => 'IOTA',          'symbol' => 'MIOTA', 'price' => 0.30],
            ['name' => 'Dash',          'symbol' => 'DASH',  'price' => 60.00],
        ];

        foreach ($cryptos as $crypto) {
            // Create or get the crypto currency
            $cryptoModel = CryptoCurrency::firstOrCreate(
                ['symbol' => $crypto['symbol']],
                [
                    'name'       => $crypto['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Upsert an initial price
            DB::table('crypto_prices')->updateOrInsert(
                ['crypto_currency_id' => $cryptoModel->id],
                [
                    'price'       => $crypto['price'],
                    'recorded_at' => $now,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }
    }
}
