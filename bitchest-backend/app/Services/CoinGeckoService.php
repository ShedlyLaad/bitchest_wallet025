<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CoinGeckoService
{
    private const BASE_URL = 'https://api.coingecko.com/api/v3';
    
    /**
     * Mapping des symboles vers les IDs CoinGecko
     */
    private const COINGECKO_IDS = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'XRP' => 'ripple',
        'BCH' => 'bitcoin-cash',
        'ADA' => 'cardano',
        'LTC' => 'litecoin',
        'XEM' => 'nem',
        'XLM' => 'stellar',
        'MIOTA' => 'iota',
        'IOTA' => 'iota', // Alias pour compatibilité
        'DASH' => 'dash',
    ];

    /**
     * Récupère les données de marché pour une crypto depuis CoinGecko
     * 
     * @param string $symbol Le symbole de la crypto (BTC, ETH, etc.)
     * @return array|null Retourne ['price' => float, 'change24h' => float, 'marketCap' => float, 'volume24h' => float] ou null
     */
    public function getCryptoData(string $symbol): ?array
    {
        $cryptoId = self::COINGECKO_IDS[strtoupper($symbol)] ?? null;
        
        if (!$cryptoId) {
            Log::warning("CoinGecko ID non trouvé pour le symbole: {$symbol}");
            return null;
        }

        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->get(self::BASE_URL . "/coins/{$cryptoId}", [
                    'localization' => 'false',
                    'tickers' => 'false',
                    'market_data' => 'true',
                    'community_data' => 'false',
                    'developer_data' => 'false',
                    'sparkline' => 'false'
                ]);

            if (!$response->ok()) {
                Log::error("Erreur CoinGecko pour {$cryptoId}: " . $response->status());
                return null;
            }

            $data = $response->json();
            
            if (!isset($data['market_data'])) {
                Log::warning("Données de marché manquantes pour {$cryptoId}");
                return null;
            }

            $marketData = $data['market_data'];
            
            // Prix en EUR
            $price = $marketData['current_price']['eur'] ?? null;
            
            // Changement 24h en pourcentage
            $change24h = $marketData['price_change_percentage_24h'] ?? 0.0;
            
            // Market Cap en EUR
            $marketCap = $marketData['market_cap']['eur'] ?? 0.0;
            
            // Volume 24h en EUR
            $volume24h = $marketData['total_volume']['eur'] ?? 0.0;

            if ($price === null) {
                Log::warning("Prix non disponible pour {$cryptoId}");
                return null;
            }

            return [
                'price' => (float) $price,
                'change24h' => round((float) $change24h, 2),
                'marketCap' => (float) $marketCap,
                'volume24h' => (float) $volume24h,
            ];
        } catch (\Exception $e) {
            Log::error("Exception CoinGecko pour {$cryptoId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les données pour plusieurs cryptos en une seule requête (plus efficace)
     * 
     * @param array $symbols Tableau de symboles
     * @return array Tableau associatif ['BTC' => [...], 'ETH' => [...], ...]
     */
    public function getMultipleCryptoData(array $symbols): array
    {
        $cryptoIds = [];
        $symbolToIdMap = [];
        
        foreach ($symbols as $symbol) {
            $upperSymbol = strtoupper($symbol);
            $cryptoId = self::COINGECKO_IDS[$upperSymbol] ?? null;
            
            if ($cryptoId && !in_array($cryptoId, $cryptoIds)) {
                $cryptoIds[] = $cryptoId;
                $symbolToIdMap[$upperSymbol] = $cryptoId;
            }
        }

        if (empty($cryptoIds)) {
            return [];
        }

        try {
            $idsString = implode(',', $cryptoIds);
            $response = Http::withOptions(['verify' => false])
                ->timeout(15)
                ->get(self::BASE_URL . "/coins/markets", [
                    'vs_currency' => 'eur',
                    'ids' => $idsString,
                    'order' => 'market_cap_desc',
                    'per_page' => 100,
                    'page' => 1,
                    'sparkline' => false,
                    'price_change_percentage' => '24h'
                ]);

            if (!$response->ok()) {
                Log::error("Erreur CoinGecko markets API: " . $response->status());
                return [];
            }

            $markets = $response->json();
            $result = [];

            // Créer un mapping inverse: id => symbol
            $idToSymbolMap = [];
            foreach ($symbolToIdMap as $symbol => $id) {
                $idToSymbolMap[$id] = $symbol;
            }

            foreach ($markets as $market) {
                $marketId = $market['id'] ?? null;
                if (!$marketId || !isset($idToSymbolMap[$marketId])) {
                    continue;
                }

                $symbol = $idToSymbolMap[$marketId];
                
                $result[$symbol] = [
                    'price' => (float) ($market['current_price'] ?? 0),
                    'change24h' => round((float) ($market['price_change_percentage_24h'] ?? 0), 2),
                    'marketCap' => (float) ($market['market_cap'] ?? 0),
                    'volume24h' => (float) ($market['total_volume'] ?? 0),
                ];
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Exception CoinGecko markets API: " . $e->getMessage());
            return [];
        }
    }
}

