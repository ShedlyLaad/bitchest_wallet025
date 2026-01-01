<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\PriceHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CotationGeneratorService
{
    /**
     * Génère l'historique de prix pour tous les cryptos sur $days derniers jours.
     * Si $force = true, supprime les anciennes cotations pour chaque crypto avant génération.
     */
    public function generateHistory(int $days = 30, bool $force = false): void
    {
        $cryptos = CryptoCurrency::all();

        foreach ($cryptos as $crypto) {
            if ($force) {
                PriceHistory::where('crypto_currency_id', $crypto->id)->delete();
            }

            // Détermine un prix de base initial avec getFirstCotation
            $base = $this->getFirstCotation($crypto->name ?? $crypto->symbol);

            // Génère $days points (1 par jour)
            for ($i = $days; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->startOfDay()->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                
                // Applique la variation quotidienne
                $variation = $this->getCotationFor($crypto->name ?? $crypto->symbol);
                $price = $base + $variation;
                
                // s'assurer prix positif
                $price = max(0.00000001, round($price, 8));

                PriceHistory::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $price,
                    'recorded_at' => $date,
                ]);

                // la valeur de base évolue pour la journée suivante
                $base = $price;
            }
        }
    }

    /**
     * Génère une nouvelle cotation "aujourd'hui" pour chaque crypto (exécutable cron)
     */
    public function generateDaily(): void
    {
        $cryptos = CryptoCurrency::all();

        foreach ($cryptos as $crypto) {
            $last = PriceHistory::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->first();

            $base = $last ? (float)$last->price : $this->getFirstCotation($crypto->name ?? $crypto->symbol);
            
            // Applique la variation quotidienne
            $variation = $this->getCotationFor($crypto->name ?? $crypto->symbol);
            $price = $base + $variation;
            $price = max(0.00000001, round($price, 8));

            PriceHistory::create([
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

        return PriceHistory::where('crypto_currency_id', $crypto->id)
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

        return (float) PriceHistory::where('crypto_currency_id', $crypto->id)
            ->latest('recorded_at')
            ->value('price');
    }
}
