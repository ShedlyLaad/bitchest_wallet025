<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TemporaryPasswordMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $password;
    public string $name;

    public function __construct(string $password, string $name = '')
    {
        $this->password = $password;
        $this->name = $name;
    }

    public function build()
    {
        // Utiliser la configuration universelle - fonctionne avec tous les fournisseurs
        return $this->subject('Votre mot de passe temporaire - BitChest')
            ->view('emails.temp_password')
            ->with([
                'password' => $this->password,
                'name' => $this->name,
            ]);
    }
}
