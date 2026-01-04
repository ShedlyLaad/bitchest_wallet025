<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CryptoCurrency;
use App\Models\CryptoPrice;
use App\Services\CoinbaseAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CryptoAndPricesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $coinbaseAPIService = app(CoinbaseAPIService::class);

        // Récupérer toutes les cryptos actives de la base de données
        // (créées par CryptoCurrencySeeder)
        $cryptos = CryptoCurrency::where('is_active', true)->get();

        if ($cryptos->isEmpty()) {
            Log::warning('Aucune crypto active trouvée. Assurez-vous que CryptoCurrencySeeder a été exécuté.');
            return;
        }

        // Récupérer tous les prix depuis Coinbase
        $symbols = $cryptos->pluck('symbol')->toArray();
        $liveData = $coinbaseAPIService->getMultipleCryptoData($symbols);

        foreach ($cryptos as $crypto) {
            // Récupérer le prix depuis l'API Coinbase uniquement
            $symbol = strtoupper($crypto->symbol);
            $apiData = $liveData[$symbol] ?? null;

            if ($apiData && isset($apiData['price']) && $apiData['price'] > 0) {
                // Utiliser le prix de l'API Coinbase
                $price = $apiData['price'];
                Log::info("Prix récupéré depuis Coinbase pour {$crypto->symbol}: {$price} EUR");
                
                // Insérer le prix dans crypto_prices
                CryptoPrice::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $price,
                    'recorded_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                // Si l'API échoue ou la crypto n'est pas supportée, utiliser le dernier prix de l'historique
                $lastPrice = \App\Models\PriceHistory::where('crypto_currency_id', $crypto->id)
                    ->latest('recorded_at')
                    ->value('price');
                
                if ($lastPrice) {
                    // Vérifier si la crypto est supportée par Coinbase pour le message de log
                    $isSupported = $coinbaseAPIService->isSupported($crypto->symbol);
                    if (!$isSupported) {
                        Log::info("Crypto {$crypto->symbol} non supportée par Coinbase. Utilisation du prix local: {$lastPrice} EUR");
                    } else {
                        Log::info("Coinbase API indisponible pour {$crypto->symbol}. Utilisation du prix local: {$lastPrice} EUR");
                    }
                    
                    CryptoPrice::create([
                        'crypto_currency_id' => $crypto->id,
                        'price' => $lastPrice,
                        'recorded_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    // Si aucun prix n'existe, générer un prix initial avec getFirstCotation
                    $basePrice = $this->getFirstCotation($crypto->name ?? $crypto->symbol);
                    $basePrice = max(0.00000001, round($basePrice, 8));
                    
                    Log::info("Aucun prix historique pour {$crypto->symbol}. Génération d'un prix initial: {$basePrice} EUR");
                    
                    CryptoPrice::create([
                        'crypto_currency_id' => $crypto->id,
                        'price' => $basePrice,
                        'recorded_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        }
        }
    }

    /**
     * Génère un prix initial pour une crypto (méthode du cahier des charges)
     */
    private function getFirstCotation(string $cryptoname): float
    {
        if (empty($cryptoname)) {
            return rand(1, 100);
        }
        
        return ord(substr($cryptoname, 0, 1)) + rand(0, 10);
    }
}
