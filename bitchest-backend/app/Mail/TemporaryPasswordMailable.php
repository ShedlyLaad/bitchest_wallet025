<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TemporaryPasswordMailable extends Mailable
{
    public string $password;
    public string $name;

    public function __construct(string $password, string $name = '')
    {
        $this->password = $password;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('Votre mot de passe temporaire')
            ->view('emails.temp_password')
            ->with([
                'password' => $this->password,
                'name' => $this->name,
            ]);
    }
}
