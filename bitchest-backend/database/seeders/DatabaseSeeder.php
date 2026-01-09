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
            CryptoAndPricesSeeder::class, // Génère l'historique de 30 jours jour par jour depuis le début
            TransactionSeeder::class,
        ]);
    }
}
