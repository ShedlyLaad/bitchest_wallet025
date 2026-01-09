<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use App\Services\CoinbaseAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CryptoAndPricesSeeder extends Seeder
{
    public function run(): void
    {
        $coinbaseAPIService = app(CoinbaseAPIService::class);
        $now = Carbon::now();

        // Récupérer toutes les cryptos actives de la base de données
        // (créées par CryptoCurrencySeeder)
        $cryptos = CryptoCurrency::where('is_active', true)->get();

        if ($cryptos->isEmpty()) {
            Log::warning('Aucune crypto active trouvée. Assurez-vous que CryptoCurrencySeeder a été exécuté.');
            return;
        }

        Log::info("🚀 Initialisation des prix actuels pour " . $cryptos->count() . " cryptos...");

        // Récupérer tous les prix actuels depuis Coinbase
        $symbols = $cryptos->pluck('symbol')->toArray();
        $liveData = $coinbaseAPIService->getMultipleCryptoData($symbols);

        foreach ($cryptos as $crypto) {
            Log::info("📊 Initialisation du prix pour {$crypto->symbol} ({$crypto->name})...");
            
            // Déterminer le prix actuel
            $currentPrice = $this->getCurrentPrice($crypto, $liveData);
            
            // Créer uniquement le prix d'aujourd'hui
            CryptoPriceRecord::create([
                'crypto_currency_id' => $crypto->id,
                'price' => $currentPrice,
                'recorded_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            Log::info("✅ Prix initialisé pour {$crypto->symbol}: {$currentPrice} EUR");
        }

        Log::info("🎉 Prix actuels initialisés avec succès pour toutes les cryptos!");
    }

    /**
     * Détermine le prix actuel pour une crypto
     * Essaie Coinbase d'abord, sinon utilise getFirstCotation
     */
    private function getCurrentPrice(CryptoCurrency $crypto, array $liveData): float
    {
        $symbol = strtoupper($crypto->symbol);
        $apiData = $liveData[$symbol] ?? null;

        if ($apiData && isset($apiData['price']) && $apiData['price'] > 0) {
            // Utiliser le prix Coinbase directement
            $currentPrice = (float) $apiData['price'];
            $currentPrice = max(0.00000001, round($currentPrice, 8));
            
            Log::info("  Prix récupéré depuis Coinbase pour {$crypto->symbol}: {$currentPrice} EUR");
            return $currentPrice;
        }

        // Fallback: utiliser getFirstCotation
        $currentPrice = $this->getFirstCotation($crypto->name ?? $crypto->symbol);
        $currentPrice = max(0.00000001, round($currentPrice, 8));
        
        $isSupported = app(CoinbaseAPIService::class)->isSupported($crypto->symbol);
        if (!$isSupported) {
            Log::info("  Crypto {$crypto->symbol} non supportée par Coinbase. Prix généré: {$currentPrice} EUR");
        } else {
            Log::info("  Coinbase API indisponible pour {$crypto->symbol}. Prix généré: {$currentPrice} EUR");
        }
        
        return $currentPrice;
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
