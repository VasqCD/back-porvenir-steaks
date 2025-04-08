{{-- resources/views/filament/resources/historial-estado-pedido-resource/pages/edit-historial-estado-pedido.blade.php --}}
<x-filament::page
    :class="static::getPageClass()"
>
    <x-filament::form wire:submit.prevent="save">
        {{ $this->form }}

        <x-filament::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament::form>

    @if($this->record->pedido)
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Historial completo del pedido #{{ $this->record->pedido_id }}
            </x-slot>
            
            @include('filament.components.historial-estados-avanzado', ['historial' => $this->record->pedido->historialEstados()->with('usuario')->orderBy('fecha_cambio', 'desc')->get()])
        </x-filament::section>
    @endif
</x-filament::page>