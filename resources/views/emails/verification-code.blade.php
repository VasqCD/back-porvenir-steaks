<x-mail::message>
    <x-mail::header>
        Código de Verificación
    </x-mail::header>
    
    Hola {{ $user->name }},
    
    Tu código de verificación es:
    
    <x-mail::panel>
        <div style="text-align: center; font-size: 24px; font-weight: bold;">{{ $codigo }}</div>
    </x-mail::panel>
    
    Este código es válido por 30 minutos.
    
    Gracias,
    {{ config('app.name') }}
</x-mail::message>