<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CryptoCurrency;

class CryptoCurrencySeeder extends Seeder
{
    public function run()
    {
        $cryptos = [
            ['name' => 'Bitcoin', 'symbol' => 'BTC'],
            ['name' => 'Ethereum', 'symbol' => 'ETH'],
            ['name' => 'Ripple', 'symbol' => 'XRP'],
            ['name' => 'Bitcoin Cash', 'symbol' => 'BCH'],
            ['name' => 'Cardano', 'symbol' => 'ADA'],
            ['name' => 'Litecoin', 'symbol' => 'LTC'],
            ['name' => 'NEM', 'symbol' => 'XEM'],
            ['name' => 'Stellar', 'symbol' => 'XLM'],
            ['name' => 'IOTA', 'symbol' => 'MIOTA'],
            ['name' => 'Dash', 'symbol' => 'DASH'],
        ];

        foreach ($cryptos as $c) {
            CryptoCurrency::firstOrCreate(['symbol' => $c['symbol']], $c);
        }
    }
}
