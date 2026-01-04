<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tempPassword;

    public function __construct($user, $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
    }

    public function build()
    {
        // Utiliser la configuration universelle - fonctionne avec tous les fournisseurs
        return $this
            ->subject("Welcome to BitChest - Your Access Credentials")
            ->view('emails.user_created')
            ->with([
                'name' => $this->user->name,
                'password' => $this->tempPassword
            ]);
    }
}
