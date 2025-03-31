<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCode;
use App\Mail\RecuperacionPassword;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Generar un código de verificación para un usuario.
     */
    public function generarCodigoVerificacion(User $user)
    {
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->codigo_verificacion = $codigo;
        $user->save();

        return $codigo;
    }

    /**
     * Enviar código de verificación por correo.
     */
    public function enviarCodigoVerificacion(User $user)
    {
        $codigo = $this->generarCodigoVerificacion($user);

        // Enviar el correo con el código
        Mail::to($user->email)->send(new VerificationCode($user, $codigo));

        return true;
    }

    /**
     * Verificar código de verificación.
     */
    public function verificarCodigo(User $user, $codigo)
    {
        if ($user->codigo_verificacion === $codigo) {
            $user->email_verified_at = now();
            $user->codigo_verificacion = null;
            $user->save();

            return true;
        }

        return false;
    }

    /**
     * Generar código para recuperación de contraseña.
     */
    public function generarCodigoRecuperacion(User $user)
    {
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->codigo_verificacion = $codigo;
        $user->save();

        return $codigo;
    }

    /**
     * Enviar código de recuperación de contraseña.
     */
    public function enviarCodigoRecuperacion(User $user)
    {
        $codigo = $this->generarCodigoRecuperacion($user);

        // Enviar el correo con el código
        Mail::to($user->email)->send(new RecuperacionPassword($user, $codigo));

        return true;
    }
}
