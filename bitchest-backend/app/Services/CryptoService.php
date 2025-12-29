<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\PriceHistory;
use App\Models\CryptoPrice;
use Carbon\Carbon;

class CryptoService
{
    /**
     * Retourne les prix actuels des 10 cryptos
     */
    public function getCurrentPrices()
    {
        $cryptos = CryptoCurrency::all();

        return $cryptos->map(function ($crypto) {
            // Try CryptoPrice table first, then PriceHistory
            $price = CryptoPrice::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->value('price');

            if ($price === null) {
                $price = PriceHistory::where('crypto_currency_id', $crypto->id)
                    ->latest('recorded_at')
                    ->value('price');
            }

            return [
                'symbol' => $crypto->symbol,
                'name' => $crypto->name,
                'price' => $price !== null ? (float) $price : 0.0,
            ];
        });
    }

    /**
     * Retourne le prix actuel d'une crypto
     */
    public function getCurrentPrice(string $symbol)
    {
        $crypto = CryptoCurrency::where('symbol', $symbol)->first();

        if (!$crypto) {
            throw new \Exception("Crypto inconnue ($symbol)");
        }

        $price = CryptoPrice::where('crypto_currency_id', $crypto->id)
            ->latest('recorded_at')
            ->value('price');

        if ($price === null) {
            $price = PriceHistory::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->value('price');
        }

        return $price !== null ? (float) $price : null;
    }

    /**
     * Récupère un historique des prix (ex : 30 jours)
     */
    public function getHistoricalPrices(string $symbol, int $days)
    {
        $crypto = CryptoCurrency::where('symbol', $symbol)->firstOrFail();

        return PriceHistory::where('crypto_currency_id', $crypto->id)
            ->where('recorded_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('recorded_at')
            ->get();
    }

    /**
     * Génère des prix aléatoires pour simuler le marché
     * (utilisé par Admin)
     */
    public function generateInitialPrices()
    {
        $cryptos = CryptoCurrency::all();

        foreach ($cryptos as $crypto) {

            $base = rand(10, 30000); // prix initial aléatoire

            for ($i = 30; $i >= 0; $i--) {

                $price = round($base * (1 + rand(-5, 5) / 100), 2);

                PriceHistory::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $price,
                    'recorded_at' => Carbon::now()->subDays($i),
                ]);

                $base = $price;
            }
        }
    }
}
