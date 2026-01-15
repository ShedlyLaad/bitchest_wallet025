<?php

namespace App\Listeners;

use App\Events\BuyExecuted;
use App\Events\SellExecuted;
use App\Jobs\ProcessTradeJob;

class QueueTradeProcessing
{
    public function handle(BuyExecuted|SellExecuted $event): void
    {
        ProcessTradeJob::dispatch($event->order);
    }
}
