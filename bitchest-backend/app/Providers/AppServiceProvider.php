<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PortfolioService;
use App\Services\CryptoService;
use App\Services\UserService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PortfolioService::class, PortfolioService::class);
        $this->app->singleton(CryptoService::class, CryptoService::class);
        $this->app->singleton(UserService::class, UserService::class);
        $this->app->singleton(\App\Services\CotationGeneratorService::class, \App\Services\CotationGeneratorService::class);
    }

    public function boot()
    {
        //
    }
}
