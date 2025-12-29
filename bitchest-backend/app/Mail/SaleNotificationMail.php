<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class SaleNotificationMail extends Mailable
{
    public $user;
    public $symbol;
    public $quantity;
    public $price;
    public $total;
    public $newBalance;

    public function __construct($user, $symbol, $quantity, $price, $total, $newBalance)
    {
        $this->user = $user;
        $this->symbol = $symbol;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->total = $total;
        $this->newBalance = $newBalance;
    }

    public function build()
    {
        return $this->subject("Sale confirmation — {$this->symbol}")
            ->view('emails.sale_notification');
    }
}

