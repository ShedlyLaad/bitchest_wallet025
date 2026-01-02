<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\PriceHistory;
use App\Models\CryptoPrice;
use Carbon\Carbon;

class CryptoService
{
    private CoinGeckoService $coinGeckoService;

    public function __construct(CoinGeckoService $coinGeckoService)
    {
        $this->coinGeckoService = $coinGeckoService;
    }

    /**
     * Retourne les prix actuels des 10 cryptos avec données live depuis CoinGecko
     */
    public function getCurrentPrices()
    {
        $cryptos = CryptoCurrency::all();
        $symbols = $cryptos->pluck('symbol')->toArray();

        // Récupérer toutes les données depuis CoinGecko en une seule requête
        $liveData = $this->coinGeckoService->getMultipleCryptoData($symbols);

        return $cryptos->map(function ($crypto) use ($liveData) {
            $symbol = $crypto->symbol;
            $upperSymbol = strtoupper($symbol);
            
            // Chercher les données live pour ce symbole (essayer le symbole exact et sa version uppercase)
            $data = $liveData[$upperSymbol] ?? $liveData[$symbol] ?? null;

            if ($data) {
                // Données live depuis CoinGecko
                return [
                    'id' => $crypto->id,
                    'symbol' => $crypto->symbol,
                    'name' => $crypto->name,
                    'price' => $data['price'],
                    'change24h' => $data['change24h'],
                    'marketCap' => $data['marketCap'],
                    'volume24h' => $data['volume24h'],
                ];
            }

            // Fallback: utiliser les données de la base de données si CoinGecko échoue
            $price = CryptoPrice::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->value('price');

            if ($price === null) {
                $price = PriceHistory::where('crypto_currency_id', $crypto->id)
                    ->latest('recorded_at')
                    ->value('price');
            }

            return [
                'id' => $crypto->id,
                'symbol' => $crypto->symbol,
                'name' => $crypto->name,
                'price' => $price !== null ? (float) $price : 0.0,
                'change24h' => 0.0,
                'marketCap' => 0.0,
                'volume24h' => 0.0,
            ];
        });
    }

    /**
     * Retourne le prix actuel d'une crypto avec données live
     */
    public function getCurrentPrice(string $symbol)
    {
        // Essayer CoinGecko d'abord
        $data = $this->coinGeckoService->getCryptoData($symbol);
        if ($data && isset($data['price'])) {
            return $data['price'];
        }

        // Fallback vers la base de données
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
     * Génère des prix pour simuler le marché
     * (utilisé par Admin) - Délègue à CotationGeneratorService
     */
    public function generateInitialPrices()
    {
        $cotationService = app(\App\Services\CotationGeneratorService::class);
        $cotationService->generateHistory(30, true);
    }
}
