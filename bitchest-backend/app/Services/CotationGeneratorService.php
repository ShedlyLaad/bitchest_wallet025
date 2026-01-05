<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CotationGeneratorService
{
    private ?CoinbaseAPIService $coinbaseAPIService;

    public function __construct(?CoinbaseAPIService $coinbaseAPIService = null)
    {
        $this->coinbaseAPIService = $coinbaseAPIService;
    }

    /**
     * Génère l'historique de prix pour tous les cryptos sur $days derniers jours.
     * Si $force = true, supprime les anciennes cotations pour chaque crypto avant génération.
     * Si $useCoinPaprika = true, utilise Coinbase API au lieu de la génération aléatoire.
     * 
     * @param int $days Nombre de jours d'historique
     * @param bool $force Supprimer les anciennes données
     * @param bool $useCoinPaprika Utiliser Coinbase API
     * @param Carbon|null $startDate Date de début (optionnel, par défaut: il y a $days jours)
     * @param Carbon|null $endDate Date de fin (optionnel, par défaut: aujourd'hui)
     */
    public function generateHistory(int $days = 30, bool $force = false, bool $useCoinPaprika = false, ?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        $cryptos = CryptoCurrency::all();

        // Définir les dates si non fournies
        $startDate = $startDate ?? Carbon::now()->subDays($days)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        foreach ($cryptos as $crypto) {
            if ($force) {
                CryptoPriceRecord::where('crypto_currency_id', $crypto->id)->delete();
            }

            // Utiliser Coinbase si demandé et disponible
            if ($useCoinPaprika && $this->coinbaseAPIService) {
                $this->generateHistoryFromCoinbase($crypto, $startDate, $endDate);
            } else {
                // Génération aléatoire (méthode originale)
                $this->generateHistoryRandom($crypto, $days);
            }
        }
    }

    /**
     * Génère l'historique depuis Coinbase API
     * Note: Coinbase ne fournit pas d'historique directement, on utilise le fallback local
     */
    private function generateHistoryFromCoinbase(CryptoCurrency $crypto, Carbon $startDate, Carbon $endDate): void
    {
        Log::info("Génération historique depuis Coinbase pour {$crypto->symbol}");

        $historicalData = $this->coinbaseAPIService->getHistoricalPrices(
            $crypto->symbol,
            $startDate,
            $endDate
        );

        if (empty($historicalData)) {
            Log::info("Coinbase ne fournit pas d'historique directement pour {$crypto->symbol}, utilisation de getFirstCotation/getCotationFor (cahier des charges)");
            $this->generateHistoryRandom($crypto, $startDate->diffInDays($endDate), $startDate, $endDate);
            return;
        }

        foreach ($historicalData as $data) {
            CryptoPriceRecord::create([
                'crypto_currency_id' => $crypto->id,
                'price' => max(0.00000001, round($data['price'], 8)),
                'recorded_at' => $data['date'],
            ]);
        }

        Log::info("Historique Coinbase généré pour {$crypto->symbol}: " . count($historicalData) . " entrées");
    }

    /**
     * Génère l'historique avec méthode getFirstCotation/getCotationFor (selon cahier des charges)
     * 
     * @param CryptoCurrency $crypto La crypto-monnaie
     * @param int $days Nombre de jours
     * @param Carbon|null $startDate Date de début (optionnel)
     * @param Carbon|null $endDate Date de fin (optionnel)
     */
    private function generateHistoryRandom(CryptoCurrency $crypto, int $days, ?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        // Définir les dates si non fournies
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        // Détermine un prix de base initial avec getFirstCotation (cahier des charges)
            $base = $this->getFirstCotation($crypto->name ?? $crypto->symbol);

        $currentDate = $startDate->copy();
        $price = $base;
                
        // Génère un point par jour sur la période spécifiée
        while ($currentDate->lte($endDate)) {
            // Applique la variation quotidienne avec getCotationFor (cahier des charges)
                $variation = $this->getCotationFor($crypto->name ?? $crypto->symbol);
                $price = $base + $variation;
                
            // S'assurer que le prix est toujours positif (cahier des charges)
                $price = max(0.00000001, round($price, 8));

                CryptoPriceRecord::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $price,
                'recorded_at' => $currentDate->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                ]);

            // La valeur de base évolue pour la journée suivante
                $base = $price;
            $currentDate->addDay();
        }
    }

    /**
     * Génère une nouvelle cotation "aujourd'hui" pour chaque crypto (exécutable cron)
     */
    public function generateDaily(): void
    {
        $cryptos = CryptoCurrency::all();

        foreach ($cryptos as $crypto) {
            $last = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->first();

            $base = $last ? (float)$last->price : $this->getFirstCotation($crypto->name ?? $crypto->symbol);
            
            // Applique la variation quotidienne
            $variation = $this->getCotationFor($crypto->name ?? $crypto->symbol);
            $price = $base + $variation;
            $price = max(0.00000001, round($price, 8));

            CryptoPriceRecord::create([
                'crypto_currency_id' => $crypto->id,
                'price' => $price,
                'recorded_at' => now(),
            ]);
        }
    }

    /**
     * Renvoie la valeur de mise sur le marché de la crypto monnaie
     * Basé sur le premier caractère du nom de la crypto
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

    /**
     * Retourne l'historique sous forme de collection [timestamp, price]
     */
    public function getHistorical(string $symbol, int $days = 30): Collection
    {
        $crypto = CryptoCurrency::where('symbol', $symbol)->firstOrFail();

        return CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'price']);
    }

    /**
     * Retourne prix courant (dernier enregistrement)
     */
    public function getCurrentPrice(string $symbol): ?float
    {
        $crypto = CryptoCurrency::where('symbol', $symbol)->first();
        if (!$crypto) return null;

        return (float) CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->latest('recorded_at')
            ->value('price');
    }
}
