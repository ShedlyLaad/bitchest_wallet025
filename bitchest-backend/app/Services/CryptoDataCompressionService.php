<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service de compression et cache des données crypto pour optimiser les performances
 * Utilisé par les endpoints user et admin pour faciliter l'affichage dynamique
 */
class CryptoDataCompressionService
{
    private CoinbaseAPIService $coinbaseAPIService;
    
    // Durée du cache en secondes
    private const CACHE_DURATION = 60; // 1 minute pour les données live
    private const CACHE_KEY_PREFIX = 'crypto_data_compressed_';
    private const CACHE_KEY_ALL = 'crypto_data_all_compressed';
    
    public function __construct(CoinbaseAPIService $coinbaseAPIService)
    {
        $this->coinbaseAPIService = $coinbaseAPIService;
    }
    
    /**
     * Récupère les données compressées pour toutes les cryptos (user et admin)
     * Utilise le cache pour éviter les appels API répétés
     * 
     * @param bool $forceRefresh Forcer le rafraîchissement du cache
     * @return \Illuminate\Support\Collection Collection de données crypto compressées
     */
    public function getCompressedCryptoData(bool $forceRefresh = false): \Illuminate\Support\Collection
    {
        $cacheKey = self::CACHE_KEY_ALL;
        
        // Si forceRefresh, supprimer le cache
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            return $this->fetchAndCompressData();
        });
    }
    
    /**
     * Récupère les données compressées pour une crypto spécifique
     * 
     * @param string $symbol Symbole de la crypto
     * @param bool $forceRefresh Forcer le rafraîchissement
     * @return array|null Données compressées ou null si non trouvé
     */
    public function getCompressedCryptoDataBySymbol(string $symbol, bool $forceRefresh = false): ?array
    {
        $upperSymbol = strtoupper($symbol);
        $cacheKey = self::CACHE_KEY_PREFIX . $upperSymbol;
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($upperSymbol) {
            $crypto = CryptoCurrency::where('symbol', $upperSymbol)->first();
            
            if (!$crypto) {
                return null;
            }
            
            return $this->compressSingleCrypto($crypto);
        });
    }
    
    /**
     * Récupère et compresse les données depuis Coinbase API
     * 
     * @return \Illuminate\Support\Collection
     */
    private function fetchAndCompressData(): \Illuminate\Support\Collection
    {
        $cryptos = CryptoCurrency::where('is_active', true)->get();
        
        if ($cryptos->isEmpty()) {
            return collect([]);
        }
        
        // Récupérer toutes les données depuis Coinbase en une seule fois
        $symbols = $cryptos->pluck('symbol')->toArray();
        $liveData = $this->coinbaseAPIService->getMultipleCryptoData($symbols);
        
        return $cryptos->map(function ($crypto) use ($liveData) {
            return $this->compressSingleCrypto($crypto, $liveData);
        })->filter(function ($data) {
            return $data !== null;
        });
    }
    
    /**
     * Compresse les données d'une crypto pour faciliter l'affichage
     * 
     * @param CryptoCurrency $crypto La crypto
     * @param array|null $liveData Données live de Coinbase (optionnel)
     * @return array|null Données compressées
     */
    private function compressSingleCrypto(CryptoCurrency $crypto, ?array $liveData = null): ?array
    {
        $symbol = strtoupper($crypto->symbol);
        
        // Si liveData n'est pas fourni, le récupérer
        if ($liveData === null) {
            $liveData = $this->coinbaseAPIService->getMultipleCryptoData([$crypto->symbol]);
        }
        
        $apiData = $liveData[$symbol] ?? null;
        
        // Déterminer le prix actuel (lecture seule - ne crée pas d'enregistrements)
        $currentPrice = null;
        if ($apiData && isset($apiData['price']) && $apiData['price'] > 0) {
            // Utiliser le prix de l'API Coinbase (ne pas créer d'enregistrement ici)
            // Les enregistrements sont créés uniquement par UpdateCryptoPricesCommand
            $currentPrice = (float) $apiData['price'];
        } else {
            // Fallback: utiliser le dernier prix de la base de données
            $currentPrice = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->value('price');
            
            if ($currentPrice === null) {
                // Si aucun prix n'existe, utiliser un prix généré (ne pas créer d'enregistrement)
                $currentPrice = $this->generateInitialPrice($crypto->symbol, $crypto->name);
            }
        }
        
        // Calculer change24h depuis l'historique
        $change24h = $this->calculateChange24h($crypto->id, $currentPrice);
        
        // Format compressé optimisé pour l'affichage
        return [
            'id' => $crypto->id,
            'symbol' => $crypto->symbol,
            'name' => $crypto->name,
            'price' => round((float) $currentPrice, 8),
            'change24h' => round($change24h, 2),
            'marketCap' => isset($apiData['marketCap']) ? round((float) $apiData['marketCap'], 2) : 0.0,
            'volume24h' => isset($apiData['volume24h']) ? round((float) $apiData['volume24h'], 2) : 0.0,
            'isActive' => $crypto->is_active,
            'updatedAt' => Carbon::now()->toIso8601String(),
        ];
    }
    
    /**
     * Calcule le changement de prix sur 24h
     * Utilise la même logique que UpdateCryptoPricesCommand pour garantir la cohérence
     * 
     * @param int $cryptoId ID de la crypto
     * @param float $currentPrice Prix actuel
     * @return float Pourcentage de changement
     */
    private function calculateChange24h(int $cryptoId, float $currentPrice): float
    {
        if ($currentPrice <= 0) {
            return 0.0;
        }

        $now = Carbon::now();
        
        // Chercher le prix le plus proche de 24h dans une fenêtre élargie (6h à 48h)
        // Même logique que UpdateCryptoPricesCommand
        $targetTime = $now->copy()->subHours(24);
        $price24hAgo = CryptoPriceRecord::where('crypto_currency_id', $cryptoId)
            ->where('recorded_at', '>=', $now->copy()->subHours(48))
            ->where('recorded_at', '<', $now->copy()->subHours(6)) // Au moins 6h de différence
            ->where('price', '>', 0)
            ->get()
            ->sortBy(function ($item) use ($targetTime) {
                return abs($item->recorded_at->diffInHours($targetTime));
            })
            ->first();

        // Si pas de données dans la fenêtre, chercher n'importe quel prix antérieur (minimum 1h)
        if (!$price24hAgo) {
            $price24hAgo = CryptoPriceRecord::where('crypto_currency_id', $cryptoId)
                ->where('recorded_at', '<', $now->copy()->subHours(1)) // Au moins 1h de différence
                ->where('price', '>', 0)
                ->orderBy('recorded_at', 'asc') // Prendre le plus ancien disponible
                ->first();
        }

        // Si toujours pas de données, retourner 0%
        if (!$price24hAgo || $price24hAgo->price <= 0) {
            return 0.0;
        }

        $price24hAgoValue = (float) $price24hAgo->price;
        
        // Si les prix sont identiques, retourner 0%
        if (abs($currentPrice - $price24hAgoValue) < 0.00000001) {
            return 0.0;
        }
        
        // Calculer le pourcentage de changement
        $change = (($currentPrice - $price24hAgoValue) / $price24hAgoValue) * 100;

        // Clamper entre -99% et +200% pour éviter les valeurs extrêmes (même logique que UpdateCryptoPricesCommand)
        return max(-99.0, min(200.0, round($change, 2)));
    }
    
    /**
     * Génère un prix initial pour une crypto
     * 
     * @param string $symbol Symbole
     * @param string|null $name Nom
     * @return float Prix initial
     */
    private function generateInitialPrice(string $symbol, ?string $name = null): float
    {
        $cryptoname = $name ?? $symbol;
        if (empty($cryptoname)) {
            return rand(1, 100);
        }
        
        $basePrice = ord(substr($cryptoname, 0, 1)) + rand(0, 10);
        return max(0.00000001, round($basePrice, 8));
    }
    
    /**
     * Nettoie le cache pour toutes les cryptos
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
        
        $cryptos = CryptoCurrency::all();
        foreach ($cryptos as $crypto) {
            $cacheKey = self::CACHE_KEY_PREFIX . strtoupper($crypto->symbol);
            Cache::forget($cacheKey);
        }
        
        Log::info('Cache crypto compressé nettoyé');
    }
    
    /**
     * Nettoie les anciennes données statiques de la base de données
     * Garde uniquement les données des X derniers jours
     * 
     * @param int $keepDays Nombre de jours à garder (par défaut: 7)
     * @return int Nombre d'enregistrements supprimés
     */
    public function cleanOldStaticData(int $keepDays = 7): int
    {
        $cutoffDate = Carbon::now()->subDays($keepDays);
        
        $deleted = CryptoPriceRecord::where('recorded_at', '<', $cutoffDate)->delete();
        
        Log::info("Nettoyage des données statiques: {$deleted} enregistrements supprimés (gardé {$keepDays} jours)");
        
        // Nettoyer aussi le cache
        $this->clearCache();
        
        return $deleted;
    }
}

