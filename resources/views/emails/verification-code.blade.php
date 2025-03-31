@component('mail::message')
    # Código de Verificación

    Hola {{ $user->name }},

    Tu código de verificación es:

    @component('mail::panel')
        <div style="text-align: center; font-size: 24px; font-weight: bold;">{{ $codigo }}</div>
    @endcomponent

    Este código es válido por 30 minutos.

    Gracias,<br>
    {{ config('app.name') }}
@endcomponent
