<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PortfolioService;
use App\Services\CryptoService;
use App\Services\UserService;
use App\Services\CoinbaseAPIService;
use App\Services\CotationGeneratorService;
use App\Services\LevelService;
use App\Services\NotificationService;
use App\Services\UniversalMailService;
use App\Services\NotificationCacheService;
use App\Services\RedisPriceService;
use App\Services\AccountCacheService;
use App\Services\TransactionCacheService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PortfolioService::class, PortfolioService::class);
        $this->app->singleton(CryptoService::class, function ($app) {
            return new CryptoService(
                $app->make(CoinbaseAPIService::class),
                $app->make(\App\Services\CryptoDataCompressionService::class),
                $app->make(RedisPriceService::class)
            );
        });
        $this->app->singleton(UserService::class, UserService::class);
        $this->app->singleton(CoinbaseAPIService::class, CoinbaseAPIService::class);
        // Levels 1–4 from total trade count (thresholds 0 / 5 / 10 / 20)
        $this->app->singleton(LevelService::class, LevelService::class);
        $this->app->singleton(UniversalMailService::class, UniversalMailService::class);
        $this->app->singleton(RedisPriceService::class, RedisPriceService::class);
        $this->app->singleton(AccountCacheService::class, AccountCacheService::class);
        $this->app->singleton(TransactionCacheService::class, TransactionCacheService::class);
        
        // NotificationService dependencies
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(PortfolioService::class),
                $app->make(LevelService::class),
                $app->make(NotificationCacheService::class)
            );
        });
        
        // CotationGeneratorService uses Coinbase API
        $this->app->singleton(CotationGeneratorService::class, function ($app) {
            return new CotationGeneratorService($app->make(CoinbaseAPIService::class));
        });
    }

    public function boot()
    {
        //
    }
}
