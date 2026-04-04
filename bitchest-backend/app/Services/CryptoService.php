<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CryptoService
{
    private const MIN_PRICE = 0.00000001;
    private const MAX_CHANGE24H = 200.0;
    private const MIN_CHANGE24H = -99.0;
    
    public function __construct(
        private CoinbaseAPIService $coinbaseAPIService,
        private CryptoDataCompressionService $compressionService,
        private RedisPriceService $redisPriceService
    ) {}

    /**
     * Récupère les prix actuels de toutes les cryptos
     * 
     * @param bool $forceRefresh Forcer le rafraîchissement du cache
     * @return Collection Collection des cryptos avec prix
     */
    public function getCurrentPrices(bool $forceRefresh = false): Collection
    {
        return $this->compressionService->getCompressedCryptoData($forceRefresh);
    }
    
    /**
     * Récupère le prix actuel d'une crypto (avec appel API si nécessaire)
     * 
     * @param string $symbol Symbole de la crypto
     * @return float|null Prix actuel ou null si non trouvé
     */
    public function getCurrentPrice(string $symbol): ?float
    {
        // Essayer Coinbase API d'abord
        $data = $this->coinbaseAPIService->getCryptoData($symbol);
        if ($data && isset($data['price']) && $data['price'] > 0) {
            return (float) $data['price'];
        }
        
        // Fallback vers la base de données
        $crypto = CryptoCurrency::where('symbol', $symbol)->first();
        if (!$crypto) {
            Log::warning("Crypto inconnue: {$symbol}");
            return null;
        }
        
        $price = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->latest('recorded_at')
            ->value('price');
        
        return $price !== null ? (float) $price : null;
    }
    
    /**
     * Récupère le prix actuel depuis Redis/DB uniquement (sans appel API)
     * 
     * @param string $symbol Symbole de la crypto
     * @return float|null Prix actuel ou null
     */
    public function getCachedCurrentPrice(string $symbol): ?float
    {
        // Essayer Redis d'abord
        $cached = $this->redisPriceService->getPrice($symbol);
        if (!empty($cached) && isset($cached['price']) && $cached['price'] > 0) {
            return (float) $cached['price'];
        }
        
        // Fallback vers la base de données
        $crypto = CryptoCurrency::where('symbol', $symbol)->first();
        if (!$crypto) {
            return null;
        }
        
        $price = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->latest('recorded_at')
            ->value('price');
        
        return $price !== null ? (float) $price : null;
    }
    
    /**
     * Récupère l'historique des prix depuis la base de données
     * 
     * @param string $symbol Symbole de la crypto
     * @param int $days Nombre de jours d'historique
     * @param bool $allowExternal Autoriser les appels API externes
     * @return Collection Collection des enregistrements de prix
     */
    public function getHistoricalPrices(string $symbol, int $days = 7, bool $allowExternal = false): Collection
    {
        $crypto = CryptoCurrency::where('symbol', $symbol)->firstOrFail();
        
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Récupérer l'historique depuis la base de données
        $history = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get();
        
        // Si trop peu de points et autorisation, essayer l'API externe
        if ($history->count() < 5 && $allowExternal) {
            $this->enrichHistoryFromAPI($crypto, $startDate, $endDate);
            
            // Recharger l'historique
            $history = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
                ->where('recorded_at', '>=', $startDate)
                ->orderBy('recorded_at')
                ->get();
        }
        
        return $history;
    }
    
    /**
     * Enrichit l'historique depuis l'API externe
     */
    private function enrichHistoryFromAPI(
        CryptoCurrency $crypto,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $apiData = $this->coinbaseAPIService->getHistoricalPrices(
            $crypto->symbol,
            $startDate,
            $endDate
        );
        
        if (empty($apiData)) {
            return;
        }
        
        // Supprimer l'ancien historique dans la fenêtre pour éviter les doublons
        CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->delete();
        
        // Créer les nouveaux enregistrements
        foreach ($apiData as $point) {
            $price = max(self::MIN_PRICE, round((float) ($point['price'] ?? 0), 8));
            $recordedAt = $point['date'] ?? null;
            
            if ($recordedAt) {
                CryptoPriceRecord::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $price,
                    'recorded_at' => $recordedAt,
                ]);
            }
        }
    }
    
    /**
     * Génère des prix initiaux (méthode du cahier des charges)
     * Note: Les prix sont générés progressivement via generateDaily() planifié
     */
    public function generateInitialPrices(): void
    {
        Log::info("generateInitialPrices appelé - les prix sont générés progressivement via generateDaily()");
    }
}
