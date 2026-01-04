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

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PortfolioService::class, PortfolioService::class);
        $this->app->singleton(CryptoService::class, CryptoService::class);
        $this->app->singleton(UserService::class, UserService::class);
        $this->app->singleton(CoinbaseAPIService::class, CoinbaseAPIService::class);
        $this->app->singleton(LevelService::class, LevelService::class);
        $this->app->singleton(UniversalMailService::class, UniversalMailService::class);
        
        // Injecter les dépendances pour NotificationService
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(PortfolioService::class),
                $app->make(LevelService::class)
            );
        });
        
        // Injecter CoinbaseAPIService dans CotationGeneratorService
        $this->app->singleton(CotationGeneratorService::class, function ($app) {
            return new CotationGeneratorService($app->make(CoinbaseAPIService::class));
        });
    }

    public function boot()
    {
        //
    }
}
