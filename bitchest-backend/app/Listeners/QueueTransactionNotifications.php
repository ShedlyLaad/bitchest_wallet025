<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Jobs\ProcessTransactionNotificationsJob;

class QueueTransactionNotifications
{
    public function handle(TransactionCreated $event): void
    {
        ProcessTransactionNotificationsJob::dispatch(
            $event->userId,
            $event->type,
            $event->symbol,
            $event->quantity,
            $event->euroAmount,
            $event->price
        );
    }
}
