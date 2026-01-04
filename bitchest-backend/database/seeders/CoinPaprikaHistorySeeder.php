<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CryptoCurrency;
use App\Models\PriceHistory;
use App\Services\CoinbaseAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CoinPaprikaHistorySeeder extends Seeder
{
    public function run(): void
    {
        $coinbaseAPIService = app(CoinbaseAPIService::class);
        
        // Période : 1 décembre 2025 au 30 décembre 2025 (30 jours)
        $startDate = Carbon::parse('2025-12-01')->startOfDay();
        $endDate = Carbon::parse('2025-12-30')->endOfDay();
        
        $cryptos = CryptoCurrency::all();

        if ($cryptos->isEmpty()) {
            Log::warning('Aucune crypto trouvée. Exécutez CryptoCurrencySeeder d\'abord.');
            return;
        }

        Log::info("Début de la génération de l'historique depuis Coinbase pour " . $cryptos->count() . " cryptos");

        foreach ($cryptos as $crypto) {
            Log::info("Récupération historique pour {$crypto->symbol} ({$crypto->name})...");
            
            // Supprimer les anciennes données pour cette crypto
            PriceHistory::where('crypto_currency_id', $crypto->id)->delete();
            
            // Récupérer l'historique depuis Coinbase (fallback vers données locales car Coinbase ne fournit pas d'historique)
            $historicalData = $coinbaseAPIService->getHistoricalPrices(
                $crypto->symbol,
                $startDate,
                $endDate
            );

            if (empty($historicalData)) {
                Log::warning("Aucune donnée historique récupérée pour {$crypto->symbol}. Utilisation de données générées.");
                
                // Fallback: générer des données avec la factory
                $this->generateFallbackData($crypto, $startDate, $endDate);
                continue;
            }

            // Insérer dans la base de données
            $inserted = 0;
            foreach ($historicalData as $data) {
                PriceHistory::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => max(0.00000001, round($data['price'], 8)), // Prix toujours positif
                    'recorded_at' => $data['date'],
                ]);
                $inserted++;
            }

            Log::info("Historique inséré pour {$crypto->symbol}: {$inserted} entrées");
            
            // Petit délai entre chaque crypto pour éviter le rate limit
            usleep(500000); // 0.5 seconde
        }

        Log::info("Seeder CoinPaprikaHistorySeeder terminé.");
    }

    /**
     * Génère des données de fallback en utilisant getFirstCotation et getCotationFor
     * Selon le cahier des charges : utiliser les fonctions de cotation_generator.php
     */
    private function generateFallbackData(CryptoCurrency $crypto, Carbon $startDate, Carbon $endDate): void
    {
        Log::info("Génération de fallback avec getFirstCotation/getCotationFor pour {$crypto->symbol}");
        
        // Utiliser getFirstCotation pour la première cotation (selon cahier des charges)
        $base = $this->getFirstCotation($crypto->name ?? $crypto->symbol);
        
        $currentDate = $startDate->copy();
        $price = $base;
        $inserted = 0;

        while ($currentDate->lte($endDate)) {
            // Utiliser getCotationFor pour la variation quotidienne (selon cahier des charges)
            $variation = $this->getCotationFor($crypto->name ?? $crypto->symbol);
            $price = $price + $variation;
            
            // S'assurer que le prix est toujours positif (cahier des charges)
            $price = max(0.00000001, round($price, 8));

            PriceHistory::create([
                'crypto_currency_id' => $crypto->id,
                'price' => $price,
                'recorded_at' => $currentDate->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
            ]);

            // La valeur de base évolue pour la journée suivante
            $base = $price;
            $currentDate->addDay();
            $inserted++;
        }

        Log::info("Données de fallback générées pour {$crypto->symbol}: {$inserted} entrées");
    }

    /**
     * Renvoie la valeur de mise sur le marché de la crypto monnaie
     * Basé sur le premier caractère du nom de la crypto
     * (Fonction du cahier des charges - cotation_generator.php)
     * 
     * @param string $cryptoname Le nom de la crypto monnaie
     * @return float Prix initial de la crypto
     */
    private function getFirstCotation(string $cryptoname): float
    {
        if (empty($cryptoname)) {
            return rand(1, 100);
        }
        
        return ord(substr($cryptoname, 0, 1)) + rand(0, 10);
    }

    /**
     * Renvoie la variation de cotation de la crypto monnaie sur un jour
     * (Fonction du cahier des charges - cotation_generator.php)
     * 
     * @param string $cryptoname Le nom de la crypto monnaie
     * @return float Variation à appliquer au prix actuel (peut être positive ou négative)
     */
    private function getCotationFor(string $cryptoname): float
    {
        if (empty($cryptoname)) {
            return (rand(0, 1) ? 1 : -1) * rand(1, 10) * 0.01;
        }
        
        $direction = (rand(0, 99) > 40) ? 1 : -1;
        $charValue = (rand(0, 99) > 49) 
            ? ord(substr($cryptoname, 0, 1)) 
            : ord(substr($cryptoname, -1));
        $multiplier = rand(1, 10) * 0.01;
        
        return $direction * $charValue * $multiplier;
    }
}

