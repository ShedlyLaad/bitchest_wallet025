<?php

namespace Database\Factories;

use App\Models\CryptoCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoCurrencyFactory extends Factory
{
    protected $model = CryptoCurrency::class;

    public function definition(): array
    {
        $symbols = ['BTC', 'ETH', 'BNB', 'ADA', 'SOL', 'DOT', 'DOGE', 'XRP'];
        $symbol = fake()->unique()->randomElement($symbols);
        
        return [
            'name' => $this->getCryptoName($symbol),
            'symbol' => $symbol,
        ];
    }

    private function getCryptoName(string $symbol): string
    {
        $names = [
            'BTC' => 'Bitcoin',
            'ETH' => 'Ethereum',
            'BNB' => 'Binance Coin',
            'ADA' => 'Cardano',
            'SOL' => 'Solana',
            'DOT' => 'Polkadot',
            'DOGE' => 'Dogecoin',
            'XRP' => 'Ripple',
        ];

        return $names[$symbol] ?? $symbol;
    }
}
