@vite('resources/css/app.css')
<x-filament::section>
    <x-slot name="heading">
        Historial del pedido
    </x-slot>
    
    @if($record && $record->pedido)
        @include('filament.components.historial-estados-avanzado', ['historial' => $record->pedido->historialEstados()->with('usuario')->orderBy('fecha_cambio', 'desc')->get()])
    @else
        <div class="p-4 rounded-lg bg-gray-50 text-center text-gray-500">
            <p>Seleccione un pedido para ver su historial completo.</p>
        </div>
    @endif
</x-filament::section>