<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VerifyEmailMailable extends Mailable
{
    use SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Vérifiez votre adresse email')
            ->view('emails.verify')
            ->with([
                'user' => $this->user,
                'name' => $this->user->name ?? ($this->user->first_name && $this->user->last_name ? $this->user->first_name . ' ' . $this->user->last_name : $this->user->email)
            ]);
    }
}
