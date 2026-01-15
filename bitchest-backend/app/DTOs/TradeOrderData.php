<?php

namespace App\DTOs;

class TradeOrderData
{
    public function __construct(
        public readonly string $clientReference,
        public readonly int $userId,
        public readonly int $cryptoId,
        public readonly string $symbol,
        public readonly float $quantity,
        public readonly float $price,
        public readonly string $type
    ) {}

    public function amount(): float
    {
        return $this->quantity * $this->price;
    }
}
