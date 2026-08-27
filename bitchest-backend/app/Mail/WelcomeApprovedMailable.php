<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class WelcomeApprovedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('Welcome to BitChest - Your account is active')
            ->view('emails.welcome_approved')
            ->with([
                'name' => $this->name,
                'loginUrl' => rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/login',
            ]);
    }
}
