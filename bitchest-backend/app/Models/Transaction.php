<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'type',
        'quantity', 'price_at_transaction', 'euro_amount'
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price_at_transaction' => 'decimal:8',
        'euro_amount' => 'decimal:2'
    ];

    // Cache TTL en secondes (5 minutes)
    private const CACHE_TTL = 300;

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * Cache key pour les quantités d'un portfolio
     */
    private static function getQuantityCacheKey(int $portfolioId, string $type): string
    {
        return "transaction:portfolio:{$portfolioId}:quantity:{$type}";
    }

    /**
     * Cache key pour l'historique d'un utilisateur
     */
    private static function getHistoryCacheKey(int $userId, int $page, int $perPage): string
    {
        return "transaction:user:{$userId}:history:page:{$page}:perpage:{$perPage}";
    }

    /**
     * Cache key pour invalider toutes les caches d'un portfolio
     */
    private static function getPortfolioCachePrefix(int $portfolioId): string
    {
        return "transaction:portfolio:{$portfolioId}:*";
    }

    /**
     * Cache key pour invalider toutes les caches d'un utilisateur
     */
    private static function getUserCachePrefix(int $userId): string
    {
        return "transaction:user:{$userId}:*";
    }

    /**
     * Récupère la quantité totale avec cache Redis
     */
    public static function getCachedQuantity(int $portfolioId, string $type): float
    {
        $cacheKey = self::getQuantityCacheKey($portfolioId, $type);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($portfolioId, $type) {
            return (float) self::where('portfolio_id', $portfolioId)
                ->where('type', $type)
                ->sum('quantity');
        });
    }

    /**
     * Invalide le cache d'un portfolio
     */
    public static function invalidatePortfolioCache(int $portfolioId): void
    {
        try {
            // Invalider les caches de quantité spécifiques
            Cache::forget(self::getQuantityCacheKey($portfolioId, 'buy'));
            Cache::forget(self::getQuantityCacheKey($portfolioId, 'sell'));
            
            // Invalider les autres caches liés au portfolio
            Cache::forget("portfolio:{$portfolioId}:total_cost");
            Cache::forget("portfolio:{$portfolioId}:buy_count");
            Cache::forget("portfolio:{$portfolioId}:purchase_details");
        } catch (\Exception $e) {
            // En cas d'erreur Redis, continuer sans cache
            \Log::warning('Redis cache invalidation failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Invalide le cache d'un utilisateur (pour l'historique paginé)
     * Invalide les premières pages les plus courantes
     */
    public static function invalidateUserCache(int $userId): void
    {
        try {
            // Invalider les pages les plus courantes (page 1-10, perPage 25-100)
            // Le cache sera régénéré lors de la prochaine requête
            for ($page = 1; $page <= 10; $page++) {
                for ($perPage = 25; $perPage <= 100; $perPage += 25) {
                    Cache::forget(self::getHistoryCacheKey($userId, $page, $perPage));
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Redis cache invalidation failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Hook après création pour invalider le cache
     */
    protected static function booted(): void
    {
        static::created(function ($transaction) {
            // Invalider le cache du portfolio
            self::invalidatePortfolioCache($transaction->portfolio_id);
            
            // Invalider le cache de l'utilisateur si on peut le récupérer
            try {
                // Charger la relation portfolio avec user_id
                $portfolio = \App\Models\Portfolio::find($transaction->portfolio_id);
                if ($portfolio && $portfolio->user_id) {
                    self::invalidateUserCache($portfolio->user_id);
                }
            } catch (\Exception $e) {
                // Ignorer si erreur de chargement
                \Log::debug('Could not invalidate user cache after transaction creation', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }
}
