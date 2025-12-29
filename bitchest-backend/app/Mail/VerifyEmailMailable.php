<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerifyEmailMailable extends Mailable
{
    public function build()
    {
        return $this->subject('Vérifiez votre adresse email')
            ->view('emails.verify');
    }
}
