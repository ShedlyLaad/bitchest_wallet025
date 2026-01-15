<?php

namespace App\Events;

use App\DTOs\TradeOrderData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BuyExecuted
{
    use Dispatchable, SerializesModels;

    public function __construct(public TradeOrderData $order)
    {
    }
}
