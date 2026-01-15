<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public int $transactionId,
        public string $type,
        public string $symbol,
        public float $quantity,
        public float $euroAmount,
        public float $price
    ) {
    }
}
