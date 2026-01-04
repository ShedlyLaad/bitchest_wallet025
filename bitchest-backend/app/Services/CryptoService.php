<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\PriceHistory;
use App\Models\CryptoPrice;
use Carbon\Carbon;

class CryptoService
{
    private CoinbaseAPIService $coinbaseAPIService;

    public function __construct(CoinbaseAPIService $coinbaseAPIService)
    {
        $this->coinbaseAPIService = $coinbaseAPIService;
    }

    /**
     * Retourne les prix actuels des 10 cryptos avec données live depuis Coinbase API
     * Met à jour automatiquement crypto_prices avec les données de l'API
     */
    public function getCurrentPrices()
    {
        $cryptos = CryptoCurrency::where('is_active', true)->get();
        $symbols = $cryptos->pluck('symbol')->toArray();

        // Récupérer toutes les données depuis Coinbase API (gratuite)
        // Seules les cryptos supportées par Coinbase seront récupérées
        $liveData = $this->coinbaseAPIService->getMultipleCryptoData($symbols);

        return $cryptos->map(function ($crypto) use ($liveData) {
            $symbol = $crypto->symbol;
            $upperSymbol = strtoupper($symbol);
            
            // Chercher les données live pour ce symbole (essayer le symbole exact et sa version uppercase)
            $data = $liveData[$upperSymbol] ?? $liveData[$symbol] ?? null;

            // Si Coinbase a retourné des données valides, les utiliser
            if ($data && isset($data['price']) && $data['price'] > 0) {
                $currentPrice = (float) $data['price'];
                
                // Mettre à jour crypto_prices avec les données live de l'API
                CryptoPrice::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $currentPrice,
                    'recorded_at' => Carbon::now(),
                ]);
                
                // Aussi enregistrer dans PriceHistory pour le calcul de change24h
                PriceHistory::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $currentPrice,
                    'recorded_at' => Carbon::now(),
                ]);

                // Calculer change24h depuis l'historique : comparer le prix actuel avec le prix d'il y a 24h
                $change24h = $this->calculateChange24h($crypto->id, $currentPrice);

                // Données live depuis Coinbase - format normalisé pour garantir l'identité entre Client et Admin
                return [
                    'id' => $crypto->id,
                    'symbol' => $crypto->symbol,
                    'name' => $crypto->name,
                    'price' => $currentPrice,
                    'change24h' => $change24h,
                    'marketCap' => isset($data['marketCap']) ? (float) $data['marketCap'] : 0.0,
                    'volume24h' => isset($data['volume24h']) ? (float) $data['volume24h'] : 0.0,
                ];
            }

            // Fallback: utiliser les données de la base de données si Coinbase échoue ou crypto non supportée
            
            $price = CryptoPrice::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->value('price');

            if ($price === null) {
                $price = PriceHistory::where('crypto_currency_id', $crypto->id)
                    ->latest('recorded_at')
                    ->value('price');
            }
            
            // Si toujours pas de prix, utiliser un prix par défaut basé sur le symbole
            if ($price === null) {
                // Générer un prix initial si aucune donnée n'existe
                $basePrice = $this->generateInitialPrice($crypto->symbol, $crypto->name);
                $price = $basePrice;
                
                // Créer une entrée dans PriceHistory pour éviter ce problème à l'avenir
                PriceHistory::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $basePrice,
                    'recorded_at' => Carbon::now(),
                ]);
            }

            // Calculer change24h depuis l'historique si disponible (fallback quand API échoue)
            $change24h = $this->calculateChange24h($crypto->id, $price);

            // Format normalisé pour garantir l'identité entre Client et Admin
            return [
                'id' => $crypto->id,
                'symbol' => $crypto->symbol,
                'name' => $crypto->name,
                'price' => $price !== null ? (float) $price : 0.0,
                'change24h' => round((float) $change24h, 2),
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
        // Essayer Coinbase d'abord
        $data = $this->coinbaseAPIService->getCryptoData($symbol);
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

        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // 1) Essayer d'abord les données locales récentes
        $history = PriceHistory::where('crypto_currency_id', $crypto->id)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get();

        // 2) Si trop peu de points, essayer de récupérer depuis Coinbase puis persister
        // Mais si l'API retourne 402, utiliser les données locales disponibles
        if ($history->count() < 5) {
            $apiData = $this->coinbaseAPIService->getHistoricalPrices(
                $symbol,
                $startDate,
                $endDate
            );

            if (!empty($apiData)) {
                // Supprimer l'ancien historique dans la fenêtre ciblée pour éviter les doublons
                PriceHistory::where('crypto_currency_id', $crypto->id)
                    ->whereBetween('recorded_at', [$startDate, $endDate])
                    ->delete();

                foreach ($apiData as $point) {
                    $price = max(0.00000001, round((float) ($point['price'] ?? 0), 8));
                    $recordedAt = $point['date'] ?? null;

                    if ($recordedAt) {
                        PriceHistory::create([
                            'crypto_currency_id' => $crypto->id,
                            'price' => $price,
                            'recorded_at' => $recordedAt,
                        ]);
                    }
                }

                // Recharger l'historique fraîchement persisté
                $history = PriceHistory::where('crypto_currency_id', $crypto->id)
                    ->where('recorded_at', '>=', $startDate)
                    ->orderBy('recorded_at')
                    ->get();
            } else {
                // Si l'API a échoué (402 ou autre), utiliser toutes les données disponibles même en dehors du timeframe
                // Cela permet d'afficher quelque chose même si les données ne sont pas parfaites
                $allHistory = PriceHistory::where('crypto_currency_id', $crypto->id)
                    ->where('recorded_at', '>=', Carbon::now()->subDays(max($days, 90))) // Au moins les données des 90 derniers jours
                    ->orderBy('recorded_at')
                    ->get();
                
                if ($allHistory->count() > 0) {
                    $history = $allHistory;
                }
            }
        }

        return $history;
    }

    /**
     * Génère des prix pour simuler le marché
     * (utilisé par Admin) - Délègue à CotationGeneratorService
     * Utilise getFirstCotation et getCotationFor selon le cahier des charges
     */
    public function generateInitialPrices()
    {
        $cotationService = app(\App\Services\CotationGeneratorService::class);
        // Générer 30 jours avec getFirstCotation/getCotationFor (cahier des charges)
        $cotationService->generateHistory(30, true, false);
    }

    /**
     * Calcule le changement de prix sur 24h en comparant le prix actuel avec le prix d'il y a 24h
     * Pour XEM et MIOTA (qui utilisent AVAX et AAVE), on ne cherche que dans les données récentes
     * 
     * @param int $cryptoId ID de la crypto
     * @param float|null $currentPrice Prix actuel (si null, utilise le dernier prix de l'historique)
     * @return float Pourcentage de changement sur 24h (ex: 2.5 pour +2.5%, -1.2 pour -1.2%)
     */
    private function calculateChange24h(int $cryptoId, ?float $currentPrice = null): float
    {
        // Si pas de prix actuel fourni, utiliser le dernier prix de l'historique
        if ($currentPrice === null || $currentPrice <= 0) {
            $currentPrice = PriceHistory::where('crypto_currency_id', $cryptoId)
                ->latest('recorded_at')
                ->value('price');
        }
        
        if ($currentPrice === null || $currentPrice <= 0) {
            return 0.0;
        }
        
        // Récupérer le symbole de la crypto pour détecter XEM et MIOTA
        $crypto = CryptoCurrency::find($cryptoId);
        $symbol = $crypto ? strtoupper($crypto->symbol) : '';
        $isMappedCrypto = ($symbol === 'XEM' || $symbol === 'MIOTA');
        
        $now = Carbon::now();
        $targetTime = $now->copy()->subHours(24);
        
        // Pour XEM et MIOTA (qui utilisent AVAX/AAVE), ne chercher que dans les 7 derniers jours
        // pour éviter d'utiliser les anciennes valeurs locales incompatibles
        $maxDaysBack = $isMappedCrypto ? 7 : 30;
        
        // Essayer de trouver le prix il y a exactement 24h (avec une marge de ±1h)
        $price24hAgo = PriceHistory::where('crypto_currency_id', $cryptoId)
            ->where('recorded_at', '>=', $now->copy()->subHours(25))
            ->where('recorded_at', '<=', $now->copy()->subHours(23))
            ->where('recorded_at', '>=', $now->copy()->subDays($maxDaysBack))
            ->get()
            ->sortBy(function ($item) use ($targetTime) {
                return abs($item->recorded_at->diffInHours($targetTime));
            })
            ->first();
        
        $price24hAgo = $price24hAgo ? $price24hAgo->price : null;
        
        // Si pas trouvé, chercher dans une fenêtre plus large (20h-28h)
        if ($price24hAgo === null) {
            $price24hAgo = PriceHistory::where('crypto_currency_id', $cryptoId)
                ->where('recorded_at', '>=', $now->copy()->subHours(28))
                ->where('recorded_at', '<=', $now->copy()->subHours(20))
                ->where('recorded_at', '>=', $now->copy()->subDays($maxDaysBack))
                ->get()
                ->sortBy(function ($item) use ($targetTime) {
                    return abs($item->recorded_at->diffInHours($targetTime));
                })
                ->first();
            
            $price24hAgo = $price24hAgo ? $price24hAgo->price : null;
        }
        
        // Si toujours pas trouvé, utiliser le prix le plus proche d'il y a 24h (dans les X derniers jours)
        if ($price24hAgo === null) {
            $price24hAgo = PriceHistory::where('crypto_currency_id', $cryptoId)
                ->where('recorded_at', '>=', $now->copy()->subDays($maxDaysBack))
                ->where('recorded_at', '<', $now)
                ->get()
                ->sortBy(function ($item) use ($targetTime) {
                    return abs($item->recorded_at->diffInHours($targetTime));
                })
                ->first();
            
            $price24hAgo = $price24hAgo ? $price24hAgo->price : null;
        }
        
        // Si toujours pas de prix trouvé, retourner 0
        if ($price24hAgo === null || $price24hAgo <= 0) {
            return 0.0;
        }
        
        // Pour XEM et MIOTA, valider que le prix_24h n'est pas trop différent du prix actuel
        // Si la différence est > 50%, c'est probablement une ancienne valeur locale incompatible
        if ($isMappedCrypto) {
            $priceDiff = abs($currentPrice - $price24hAgo) / max($currentPrice, $price24hAgo) * 100;
            if ($priceDiff > 50.0) {
                // L'ancien prix est trop différent, probablement une ancienne valeur locale
                // Retourner 0% pour éviter des changements aberrants
                return 0.0;
            }
        }
        
        // Calculer le pourcentage de changement
        $rawChange24h = (($currentPrice - $price24hAgo) / $price24hAgo) * 100;
        
        // Valider les valeurs pour éviter les aberrations
        // Limiter à des valeurs réalistes : -50% à +50% pour la plupart des cryptos
        // Mais permettre jusqu'à -99% et +200% pour les cas extrêmes (mais rares)
        if ($rawChange24h < -99.0) {
            // Si changement très négatif, limiter à -99% (peut arriver lors d'un crash)
            $change24h = -99.0;
        } elseif ($rawChange24h > 200.0) {
            // Si changement très positif, limiter à +200% (peut arriver lors d'un pump)
            $change24h = 200.0;
        } else {
            // Utiliser la valeur réelle calculée (arrondie à 2 décimales)
            $change24h = round($rawChange24h, 2);
        }
        
        return $change24h;
    }

    /**
     * Génère un prix initial pour une crypto (méthode du cahier des charges)
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
}
