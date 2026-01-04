<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CryptoCurrencySeeder::class,  // Créer les 10 cryptos d'abord
            CoinPaprikaHistorySeeder::class, // Récupérer l'historique 30 jours depuis Coinbase (fallback local)
            CryptoAndPricesSeeder::class, // Ensuite récupérer les prix actuels depuis Coinbase API
            // PriceHistorySeeder::class, // Désactivé car remplacé par CoinPaprikaHistorySeeder
            TransactionSeeder::class,
        ]);
    }
}
