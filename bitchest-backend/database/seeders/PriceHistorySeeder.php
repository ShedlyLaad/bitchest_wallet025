<?php

namespace Database\Seeders;

use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use Illuminate\Database\Seeder;

class PriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Prix de base réalistes pour chaque crypto
        $basePrices = [
            'BTC' => 30000,
            'ETH' => 2000,
            'XRP' => 0.5,
            'BCH' => 250,
            'ADA' => 0.8,
            'LTC' => 80,
            'XEM' => 0.1,
            'XLM' => 0.1,
            'MIOTA' => 0.3,
            'DASH' => 60,
        ];

        foreach (CryptoCurrency::all() as $crypto) {
            // Utiliser le prix de base pour cette crypto ou un prix aléatoire par défaut
            $basePrice = $basePrices[$crypto->symbol] ?? rand(10, 50000);
            
            // Générer 30 jours d'historique avec variations réalistes
            for ($i = 30; $i >= 0; $i--) {
                // Variation de ±5% par jour
                $variation = (rand(-500, 500) / 10000); // -5% à +5%
                $price = $basePrice * (1 + $variation);
                
                // S'assurer que le prix reste positif et réaliste
                $price = max(0.00000001, round($price, 8));
                
                CryptoPriceRecord::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $price,
                    'recorded_at' => now()->subDays($i)->startOfDay()->addHours(rand(0, 23)),
                ]);
                
                // Le prix de base pour le jour suivant est le prix actuel
                $basePrice = $price;
            }
        }
    }
}
