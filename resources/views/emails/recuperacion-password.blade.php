<x-mail::message>
    <x-mail::header>
        Recuperación de Contraseña
    </x-mail::header>
    
    Hola {{ $user->name }},
    
    Has solicitado restablecer tu contraseña. Utiliza el siguiente código para continuar con el proceso:
    
    <x-mail::panel>
        <div style="text-align: center; font-size: 24px; font-weight: bold;">{{ $codigo }}</div>
    </x-mail::panel>
    
    Este código es válido por 30 minutos. Si no has solicitado este cambio, puedes ignorar este correo.
    
    Gracias,
    {{ config('app.name') }}
</x-mail::message>