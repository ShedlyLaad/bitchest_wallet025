<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\TransactionController;
use App\Http\Controllers\Client\PortfolioController;
use App\Http\Controllers\Client\CryptoMarketController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CryptoController as AdminCryptoController;
use App\Http\Controllers\Admin\AdminProfileController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register'])
    ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);
Route::post('/login', [AuthController::class, 'login'])
    ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);

/*
|--------------------------------------------------------------------------
| ROUTES PROTECTED BY AUTH (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','account.status'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('auth.me');

    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

    /*
    |--------------------------------------------------------------------------
    | CLIENT ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:client')->group(function () {

        // Portfolio
        Route::get('/portfolio', [PortfolioController::class, 'index']);
        Route::put('/profile', [ProfileController::class, 'update']);

        // Achat / vente crypto
        Route::post('/transaction/buy', [TransactionController::class, 'buy']);
        Route::post('/transaction/sell', [TransactionController::class, 'sell']);
        // Historique des transactions client
        Route::get('/transaction/history', [TransactionController::class, 'history']);

        // Marché crypto
        Route::get('/market', [CryptoMarketController::class, 'index']);
        Route::get('/market/history/{crypto_currency_id}', [CryptoMarketController::class, 'history']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Profile Admin
        Route::put('/profile', [AdminProfileController::class, 'update']);

        // CRUD Clients
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::post('/users/{id}/approve', [AdminUserController::class, 'approve']);
        Route::post('/users/{id}/block', [AdminUserController::class, 'block']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

        // Gestion des cryptos
        Route::get('/cryptos', [AdminCryptoController::class, 'index']);
        Route::post('/cryptos/generate', [AdminCryptoController::class, 'generate']);
        Route::get('/cryptos/{symbol}/history', [AdminCryptoController::class, 'history']);

        // Transactions (admin) - consulter l'historique global
        Route::get('/transactions', [\App\Http\Controllers\Admin\TransactionController::class, 'index']);
    });

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
