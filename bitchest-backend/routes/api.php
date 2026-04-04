<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\TransactionController;
use App\Http\Controllers\Client\PortfolioController;
use App\Http\Controllers\Client\CryptoMarketController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CryptoController as AdminCryptoController;
use App\Http\Controllers\Admin\AdminProfileController;
use Illuminate\Http\Request;
use App\Services\LevelService;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (no auth required)
|--------------------------------------------------------------------------
*/
// Public authentication routes - Bearer token authentication only (no CSRF needed)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public crypto market data for landing page
Route::get('/public/market', [CryptoMarketController::class, 'index'])
    ->withoutMiddleware(['throttle:api']);

/*
|--------------------------------------------------------------------------
| ROUTES PROTECTED BY AUTH (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','account.status'])->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $levelProgress = app(LevelService::class)->getLevelProgressSnapshot($user);

        return array_merge($user->toArray(), [
            'level_progress' => $levelProgress,
        ]);
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
        Route::get('/portfolio/crypto/{cryptoCurrencyId}/purchases', [PortfolioController::class, 'purchaseDetails']);
        Route::put('/profile', [ProfileController::class, 'update']);
        
        // Profile picture and banner uploads
        Route::post('/profile/picture', [ProfileController::class, 'uploadProfilePicture']);
        Route::post('/profile/banner', [ProfileController::class, 'uploadProfileBanner']);
        Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture']);
        Route::delete('/profile/banner', [ProfileController::class, 'deleteProfileBanner']);

        // Achat / vente crypto
        Route::post('/transaction/buy', [TransactionController::class, 'buy']);
        Route::post('/transaction/sell', [TransactionController::class, 'sell']);
        // Historique des transactions client
        Route::get('/transaction/history', [TransactionController::class, 'history']);

        // Marché crypto
        Route::get('/market', [CryptoMarketController::class, 'index']);
        Route::get('/market/history/{crypto_currency_id}', [CryptoMarketController::class, 'history'])->where('crypto_currency_id', '[0-9A-Za-z]+');
        // Nouvelle API REST pour utilisateurs - données Coinbase avec cache
        Route::get('/user/cryptos', [CryptoMarketController::class, 'userCryptos']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Profile Admin
        Route::put('/profile', [AdminProfileController::class, 'update']);
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'summary']);

        // CRUD Clients
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
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
