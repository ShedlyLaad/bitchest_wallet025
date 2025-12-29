<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionCompletedNotification extends Notification
{
    public string $type;
    public string $symbol;
    public float $quantity;
    public float $euro;

    public function __construct(string $type, string $symbol, float $quantity, float $euro)
    {
        $this->type = $type;
        $this->symbol = $symbol;
        $this->quantity = $quantity;
        $this->euro = $euro;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Transaction effectuée')
            ->line("Type : {$this->type}")
            ->line("Crypto : {$this->symbol}")
            ->line("Quantité : {$this->quantity}")
            ->line("Montant (€) : {$this->euro}");
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'symbol' => $this->symbol,
            'quantity' => $this->quantity,
            'euro' => $this->euro,
        ];
    }
}
