<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Events\BuyExecuted;
use App\Events\SellExecuted;
use App\Events\TransactionCreated;
use App\Listeners\QueueTradeProcessing;
use App\Listeners\QueueTransactionNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        BuyExecuted::class => [
            QueueTradeProcessing::class,
        ],
        SellExecuted::class => [
            QueueTradeProcessing::class,
        ],
        TransactionCreated::class => [
            QueueTransactionNotifications::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
