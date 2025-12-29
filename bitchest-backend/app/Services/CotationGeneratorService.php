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

            // Détermine un prix de base raisonnable selon le symbole (ou aléatoire)
            $base = $this->basePriceForSymbol($crypto->symbol);

            // Génère $days points (1 par jour)
            for ($i = $days; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->startOfDay()->addHours(rand(0, 23))->addMinutes(rand(0,59));
                $price = $this->mutatePrice($base, $i);
                // s'assurer prix positif
                $price = max(0.00000001, round($price, 8));

                PriceHistory::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $price,
                    'recorded_at' => $date,
                ]);

                // la valeur de base évolue doucement pour la journée suivante
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

            $base = $last ? (float)$last->price : $this->basePriceForSymbol($crypto->symbol);
            $price = $this->mutatePrice($base, 0);
            $price = max(0.00000001, round($price, 8));

            PriceHistory::create([
                'crypto_currency_id' => $crypto->id,
                'price' => $price,
                'recorded_at' => now(),
            ]);
        }
    }

    /**
     * Retourne un "prix de base" heuristique par symbole (améliorable)
     */
    private function basePriceForSymbol(string $symbol): float
    {
        $map = [
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

        return $map[$symbol] ?? rand(1, 1000);
    }

    /**
     * Applique une mutation aléatoire contrôlée au prix (variation journalière)
     */
    private function mutatePrice(float $base, int $dayIndex): float
    {
        // Variation +/- 0.5% à 8% selon volatilité aléatoire
        $vol = rand(5, 80) / 1000; // 0.005 -> 0.08
        $direction = rand(0, 1) ? 1 : -1;
        $factor = 1 + ($direction * $vol);
        // plus le dayIndex grand (plus ancien), plus on peut lisser
        return (float)$base * $factor;
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
