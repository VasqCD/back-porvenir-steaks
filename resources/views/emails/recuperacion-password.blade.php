@component('mail::message')
    # Recuperación de Contraseña

    Hola {{ $user->name }},

    Has solicitado restablecer tu contraseña. Utiliza el siguiente código para continuar con el proceso:

    @component('mail::panel')
        <div style="text-align: center; font-size: 24px; font-weight: bold;">{{ $codigo }}</div>
    @endcomponent

    Este código es válido por 30 minutos. Si no has solicitado este cambio, puedes ignorar este correo.

    Gracias,<br>
    {{ config('app.name') }}
@endcomponent
