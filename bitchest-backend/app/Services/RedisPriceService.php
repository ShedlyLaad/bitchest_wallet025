<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service de gestion des prix crypto dans Redis
 * Cache ultra-rapide avec fallback automatique vers la base de données
 */
class RedisPriceService
{
    private const REDIS_KEY_PREFIX = 'crypto:price:';
    private const REDIS_KEY_ALL = 'cryptos:all';
    private const CACHE_TTL = 86400; // 24 heures

    /**
     * Récupère tous les prix depuis Redis (ultra-rapide)
     * Fallback vers DB si Redis indisponible ou vide
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAllPrices(): \Illuminate\Support\Collection
    {
        try {
            // Vérifier si Redis est disponible
            if (!$this->isAvailable()) {
                Log::warning('Redis indisponible, fallback vers DB');
                return $this->getAllPricesFromDB();
            }

            // Récupérer la liste de toutes les cryptos depuis Redis
            $cryptoKeys = Redis::smembers(self::REDIS_KEY_ALL);
            
            if (empty($cryptoKeys)) {
                Log::info('Redis vide, fallback vers DB et initialisation');
                // Initialiser depuis DB puis retourner
                $this->initializeFromDB();
                $cryptoKeys = Redis::smembers(self::REDIS_KEY_ALL);
            }

            if (empty($cryptoKeys)) {
                return collect([]);
            }

            $prices = collect();
            
            // Récupérer toutes les données en une seule opération pipeline
            $pipe = Redis::pipeline();
            foreach ($cryptoKeys as $symbol) {
                $pipe->hgetall(self::REDIS_KEY_PREFIX . $symbol);
            }
            $results = $pipe->execute();

            // Construire la collection
            foreach ($results as $index => $data) {
                if (!empty($data)) {
                    $symbol = $cryptoKeys[$index];
                    $prices->push($this->formatPriceData($symbol, $data));
                }
            }

            if ($prices->isEmpty()) {
                Log::warning('Aucun prix trouvé dans Redis, fallback vers DB');
                return $this->getAllPricesFromDB();
            }

            return $prices;

        } catch (\Exception $e) {
            Log::error('Erreur Redis getAllPrices', [
                'error' => $e->getMessage()
            ]);
            return $this->getAllPricesFromDB();
        }
    }

    /**
     * Récupère le prix d'une crypto depuis Redis
     * 
     * @param string $symbol
     * @return array|null
     */
    public function getPrice(string $symbol): ?array
    {
        try {
            if (!$this->isAvailable()) {
                return $this->getPriceFromDB($symbol);
            }

            $key = self::REDIS_KEY_PREFIX . strtoupper($symbol);
            $data = Redis::hgetall($key);

            if (empty($data)) {
                return $this->getPriceFromDB($symbol);
            }

            return $this->formatPriceData(strtoupper($symbol), $data);

        } catch (\Exception $e) {
            Log::error('Erreur Redis getPrice', [
                'symbol' => $symbol,
                'error' => $e->getMessage()
            ]);
            return $this->getPriceFromDB($symbol);
        }
    }

    /**
     * Met à jour le prix d'une crypto dans Redis
     * 
     * @param string $symbol
     * @param array $priceData
     * @return bool
     */
    public function updatePrice(string $symbol, array $priceData): bool
    {
        try {
            if (!$this->isAvailable()) {
                return false;
            }

            $key = self::REDIS_KEY_PREFIX . strtoupper($symbol);

            // Préparer les données pour Redis
            $redisData = [
                'id' => (string) ($priceData['id'] ?? 0),
                'symbol' => strtoupper($symbol),
                'name' => (string) ($priceData['name'] ?? ''),
                'price' => (string) ($priceData['price'] ?? 0),
                'change24h' => (string) ($priceData['change24h'] ?? 0),
                'marketCap' => (string) ($priceData['marketCap'] ?? 0),
                'volume24h' => (string) ($priceData['volume24h'] ?? 0),
                'isActive' => (string) (($priceData['isActive'] ?? true) ? '1' : '0'),
                'updatedAt' => $priceData['updatedAt'] ?? Carbon::now()->toIso8601String(),
            ];

            // Stocker dans Redis avec TTL
            Redis::hmset($key, $redisData);
            Redis::expire($key, self::CACHE_TTL);

            // Ajouter à la liste des cryptos actives
            Redis::sadd(self::REDIS_KEY_ALL, strtoupper($symbol));
            Redis::expire(self::REDIS_KEY_ALL, self::CACHE_TTL);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur Redis updatePrice', [
                'symbol' => $symbol,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Met à jour plusieurs prix en batch
     * 
     * @param array $pricesData Array de ['symbol' => ..., 'data' => ...]
     * @return int Nombre de prix mis à jour
     */
    public function updatePricesBatch(array $pricesData): int
    {
        $updated = 0;
        foreach ($pricesData as $item) {
            if (isset($item['symbol']) && isset($item['data'])) {
                if ($this->updatePrice($item['symbol'], $item['data'])) {
                    $updated++;
                }
            }
        }
        return $updated;
    }

    /**
     * Initialise Redis depuis la base de données
     * 
     * @return int Nombre de cryptos initialisées
     */
    public function initializeFromDB(): int
    {
        try {
            if (!$this->isAvailable()) {
                return 0;
            }

            $cryptos = CryptoCurrency::where('is_active', true)->get();
            $initialized = 0;

            foreach ($cryptos as $crypto) {
                $priceData = $this->getPriceDataFromDB($crypto);
                if ($priceData && $this->updatePrice($crypto->symbol, $priceData)) {
                    $initialized++;
                }
            }

            Log::info("Redis initialisé avec {$initialized} cryptos depuis la DB");
            return $initialized;

        } catch (\Exception $e) {
            Log::error('Erreur initialisation Redis depuis DB', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Vide tout le cache Redis
     * 
     * @return bool
     */
    public function clearAll(): bool
    {
        try {
            if (!$this->isAvailable()) {
                return false;
            }

            $keys = Redis::keys(self::REDIS_KEY_PREFIX . '*');
            if (!empty($keys)) {
                Redis::del($keys);
            }
            Redis::del(self::REDIS_KEY_ALL);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur nettoyage Redis', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Vérifie si Redis est disponible
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Formate les données de prix depuis Redis avec validation stricte
     * 
     * @param string $symbol
     * @param array $data
     * @return array
     */
    private function formatPriceData(string $symbol, array $data): array
    {
        // Normaliser et valider change24h (limiter entre -99 et +200, arrondir à 2 décimales)
        $change24h = isset($data['change24h']) && is_numeric($data['change24h'])
            ? max(-99.0, min(200.0, round((float) $data['change24h'], 2)))
            : 0.0;
        
        return [
            'id' => (int) ($data['id'] ?? 0),
            'symbol' => strtoupper($symbol),
            'name' => $data['name'] ?? '',
            'price' => isset($data['price']) && is_numeric($data['price'])
                ? max(0.0, round((float) $data['price'], 8))
                : 0.0,
            'change24h' => $change24h,
            'marketCap' => isset($data['marketCap']) && is_numeric($data['marketCap'])
                ? max(0.0, round((float) $data['marketCap'], 2))
                : 0.0,
            'volume24h' => isset($data['volume24h']) && is_numeric($data['volume24h'])
                ? max(0.0, round((float) $data['volume24h'], 2))
                : 0.0,
            'isActive' => isset($data['isActive']) && ($data['isActive'] === '1' || $data['isActive'] === 1 || $data['isActive'] === true),
            'updatedAt' => $data['updatedAt'] ?? Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Récupère les prix depuis la DB (fallback)
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getAllPricesFromDB(): \Illuminate\Support\Collection
    {
        $cryptos = CryptoCurrency::where('is_active', true)->get();

        return $cryptos->map(function ($crypto) {
            return $this->getPriceDataFromDB($crypto);
        })->filter()->values();
    }

    /**
     * Récupère le prix d'une crypto depuis la DB
     * 
     * @param string $symbol
     * @return array|null
     */
    private function getPriceFromDB(string $symbol): ?array
    {
        $crypto = CryptoCurrency::where('symbol', strtoupper($symbol))
            ->where('is_active', true)
            ->first();

        if (!$crypto) {
            return null;
        }

        return $this->getPriceDataFromDB($crypto);
    }

    /**
     * Construit les données de prix depuis un modèle CryptoCurrency
     * 
     * @param CryptoCurrency $crypto
     * @return array|null
     */
    private function getPriceDataFromDB(CryptoCurrency $crypto): ?array
    {
        $latestPrice = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->latest('recorded_at')
            ->first();

        if (!$latestPrice) {
            return null;
        }

        // Calculer change24h
        $change24h = $this->calculateChange24h($crypto->id, (float) $latestPrice->price);

        return [
            'id' => $crypto->id,
            'symbol' => $crypto->symbol,
            'name' => $crypto->name,
            'price' => (float) $latestPrice->price,
            'change24h' => $change24h,
            'marketCap' => 0.0,
            'volume24h' => 0.0,
            'isActive' => $crypto->is_active,
            'updatedAt' => $latestPrice->recorded_at->toIso8601String(),
        ];
    }

    /**
     * Calcule le changement de prix sur 24h de manière dynamique
     * Utilise la même logique que UpdateCryptoPricesCommand pour garantir la cohérence
     * 
     * @param int $cryptoId
     * @param float $currentPrice
     * @return float
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
}
