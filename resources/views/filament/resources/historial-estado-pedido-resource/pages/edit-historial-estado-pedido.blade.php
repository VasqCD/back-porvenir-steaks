<x-filament::page>
    {{ $this->form }}

    <x-filament::actions
        :actions="$this->getActions()"
        :full-width="$this->hasFullWidthActions()"
    />

    @if($this->record->pedido)
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Historial completo del pedido #{{ $this->record->pedido_id }}
            </x-slot>
            
            @include('filament.components.historial-estados-avanzado', ['historial' => $this->record->pedido->historialEstados()->with('usuario')->orderBy('fecha_cambio', 'desc')->get()])
        </x-filament::section>
    @endif
</x-filament::page>