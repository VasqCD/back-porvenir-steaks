<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecuperacionPassword extends Mailable
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
        return $this->subject('Recuperación de contraseña - El Porvenir Steaks')
            ->markdown('emails.recuperacion-password');
    }
}
