<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $codigo;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $codigo)
    {
        $this->user = $user;
        $this->codigo = $codigo;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Código de verificación - El Porvenir Steaks')
            ->markdown('emails.verification-code');
    }
}
