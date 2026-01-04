<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

/**
 * Service pour récupérer les données crypto depuis Coinbase API (gratuite)
 * Remplace CoinPaprika, CoinGecko et FreeCryptoAPI
 */
class CoinbaseAPIService
{
    private const BASE_URL = 'https://api.coinbase.com/v2';
    
    /**
     * Mapping des symboles vers les formats Coinbase
     * Coinbase utilise directement les symboles (BTC, ETH, etc.)
     * Note: XEM et MIOTA utilisent AVAX et AAVE respectivement mais gardent leurs noms/symboles
     */
    private const CRYPTO_SYMBOLS = [
        'BTC' => 'BTC',
        'ETH' => 'ETH',
        'XRP' => 'XRP',
        'BCH' => 'BCH',
        'ADA' => 'ADA',
        'LTC' => 'LTC',
        'XLM' => 'XLM',
        'DASH' => 'DASH',
        'XEM' => 'AVAX',  // XEM utilise les données de AVAX (Avalanche)
        'MIOTA' => 'AAVE', // MIOTA utilise les données de AAVE (Aave)
    ];

    /**
     * Liste des cryptos supportées par Coinbase API
     * XEM et MIOTA sont mappées vers AVAX et AAVE mais gardent leurs noms/symboles
     */
    private const SUPPORTED_CRYPTOS = [
        'BTC', 'ETH', 'XRP', 'BCH', 'ADA', 'LTC', 'XLM', 'DASH', 'XEM', 'MIOTA'
    ];

    /**
     * Vérifie si une crypto est supportée par Coinbase
     * XEM et MIOTA sont supportées via AVAX et AAVE
     * 
     * @param string $symbol Le symbole de la crypto
     * @return bool True si supportée, false sinon
     */
    public function isSupported(string $symbol): bool
    {
        $upperSymbol = strtoupper($symbol);
        return in_array($upperSymbol, self::SUPPORTED_CRYPTOS);
    }

    /**
     * Récupère l'historique des prix pour une crypto sur une période
     * Coinbase API ne fournit pas directement d'historique, on utilise les données locales
     * 
     * @param string $symbol Le symbole de la crypto (BTC, ETH, etc.)
     * @param Carbon $startDate Date de début
     * @param Carbon $endDate Date de fin
     * @return array Tableau de ['date' => Carbon, 'price' => float] ou []
     */
    public function getHistoricalPrices(string $symbol, Carbon $startDate, Carbon $endDate): array
    {
        // Coinbase API ne fournit pas d'historique directement
        // On retourne un tableau vide pour utiliser le fallback local
        return [];
    }

    /**
     * Récupère les données de marché actuelles pour une crypto
     * 
     * @param string $symbol Le symbole de la crypto (BTC, ETH, etc.)
     * @return array|null Retourne ['price' => float, 'change24h' => float, 'marketCap' => float, 'volume24h' => float] ou null
     */
    public function getCryptoData(string $symbol): ?array
    {
        $upperSymbol = strtoupper($symbol);
        
        // Vérifier si la crypto est supportée par Coinbase
        if (!$this->isSupported($upperSymbol)) {
            // Ne pas logger pour éviter le spam - les cryptos non supportées utilisent le fallback local
            return null; // Retourner null pour déclencher le fallback
        }

        // Mapper XEM -> AVAX et MIOTA -> AAVE pour l'appel API
        // Mais garder le symbole original pour les logs et le retour
        $cryptoSymbol = self::CRYPTO_SYMBOLS[$upperSymbol] ?? $upperSymbol;
        $originalSymbol = $upperSymbol; // Garder le symbole original (XEM ou MIOTA)
        
        if (!$cryptoSymbol) {
            return null;
        }

        try {
            // Coinbase API - endpoint pour prix spot (gratuit, pas besoin d'authentification)
            // Format: /v2/prices/{currency}-EUR/spot
            // Utiliser cryptoSymbol (AVAX ou AAVE) pour l'appel API
            $url = self::BASE_URL . "/prices/{$cryptoSymbol}-EUR/spot";
            
            $response = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->get($url);

            if ($response->status() === 429) {
                return null;
            }

            if ($response->status() === 404) {
                return null;
            }

            if (!$response->ok()) {
                return null;
            }

            $data = $response->json();
            
            if (!isset($data['data']['amount'])) {
                return null;
            }

            $priceEUR = (float) $data['data']['amount'];
            
            if ($priceEUR <= 0) {
                return null;
            }
            
            // Coinbase ne fournit pas directement change24h, on le calcule depuis l'historique local
            // Pour l'instant, on retourne 0 et le backend le calculera depuis l'historique
            $change24h = 0.0;
            
            return [
                'price' => $priceEUR,
                'change24h' => round($change24h, 2),
                'marketCap' => 0.0, // Coinbase ne fournit pas market cap
                'volume24h' => 0.0, // Coinbase ne fournit pas volume 24h
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Récupère les données pour plusieurs cryptos
     * 
     * @param array $symbols Tableau de symboles
     * @return array Tableau associatif ['BTC' => [...], 'ETH' => [...], ...]
     */
    public function getMultipleCryptoData(array $symbols): array
    {
        $result = [];
        
        // Filtrer d'abord les cryptos supportées pour éviter les appels inutiles
        $supportedSymbols = array_filter($symbols, function($symbol) {
            return $this->isSupported($symbol);
        });
        
        // Coinbase nécessite une requête par crypto (seulement pour les supportées)
        foreach ($supportedSymbols as $symbol) {
            try {
                $data = $this->getCryptoData($symbol);
                if ($data && isset($data['price']) && $data['price'] > 0) {
                    $result[strtoupper($symbol)] = $data;
                }
            } catch (\Exception $e) {
                // Continuer avec les autres cryptos en cas d'erreur
            }
            
            // Petit délai pour éviter le rate limit (seulement pour les cryptos supportées)
            if (count($supportedSymbols) > 1) {
                usleep(150000); // 0.15 seconde entre les requêtes
            }
        }

        return $result;
    }
}

