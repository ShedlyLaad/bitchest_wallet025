<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTransactionNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public string $type,
        public string $symbol,
        public float $quantity,
        public float $euroAmount,
        public float $price
    ) {
    }

    public function handle(NotificationService $notificationService): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            return;
        }

        $notificationService->createTransactionNotification(
            $user,
            $this->type,
            $this->symbol,
            $this->quantity,
            $this->euroAmount,
            $this->price
        );

        $notificationService->checkAndCreatePortfolioNotifications($user);
    }
}
