@vite('resources/css/app.css')
{{-- resources/views/filament/resources/historial-estado-pedido-resource/pages/view-historial-estado-pedido.blade.php --}}
<x-filament::page>
    <x-filament::card>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Historial de cambio de estado</h2>
            <div class="inline-flex items-center px-3 py-1 rounded-full 
                {{ match($this->record->estado_nuevo) {
                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                    'en_cocina' => 'bg-blue-100 text-blue-800',
                    'en_camino' => 'bg-indigo-100 text-indigo-800',
                    'entregado' => 'bg-green-100 text-green-800',
                    'cancelado' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800',
                } }}">
                <span class="font-medium">{{ ucfirst($this->record->estado_nuevo) }}</span>
            </div>
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Pedido ID</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <a href="{{ route('filament.admin.resources.pedidos.edit', ['record' => $this->record->pedido_id]) }}"
                        class="text-primary-600 hover:text-primary-800 font-medium">
                        #{{ $this->record->pedido_id }}
                    </a>
                </dd>
            </div>

            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($this->record->pedido && $this->record->pedido->usuario)
                        {{ $this->record->pedido->usuario->name }}
                    @else
                        No disponible
                    @endif
                </dd>
            </div>

            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Estado anterior</dt>
                <dd class="mt-1 text-sm">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                    {{ match($this->record->estado_anterior) {
                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                        'en_cocina' => 'bg-blue-100 text-blue-800',
                        'en_camino' => 'bg-indigo-100 text-indigo-800',
                        'entregado' => 'bg-green-100 text-green-800',
                        'cancelado' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    } }}">
                        {{ ucfirst($this->record->estado_anterior ?? 'Nuevo') }}
                    </span>
                </dd>
            </div>

            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Estado nuevo</dt>
                <dd class="mt-1 text-sm">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                    {{ match($this->record->estado_nuevo) {
                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                        'en_cocina' => 'bg-blue-100 text-blue-800',
                        'en_camino' => 'bg-indigo-100 text-indigo-800',
                        'entregado' => 'bg-green-100 text-green-800',
                        'cancelado' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    } }}">
                        {{ ucfirst($this->record->estado_nuevo) }}
                    </span>
                </dd>
            </div>

            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Fecha del cambio</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($this->record->fecha_cambio)->format('d/m/Y H:i:s') }}
                </dd>
            </div>

            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Usuario que realizó el cambio</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $this->record->usuario->name ?? 'Usuario desconocido' }}
                </dd>
            </div>

            @if($this->record->pedido && $this->record->pedido->ubicacion)
                <div class="col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Dirección de entrega</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $this->record->pedido->ubicacion->direccion_completa }}
                    </dd>
                </div>
            @endif
        </dl>

        <div class="mt-6 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium">Detalles adicionales</h3>
            
            @if($this->record->pedido)
                <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium">Resumen del pedido</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        Total: L {{ number_format($this->record->pedido->total ?? 0, 2) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        Fecha del pedido: {{ $this->record->pedido->fecha_pedido ? \Carbon\Carbon::parse($this->record->pedido->fecha_pedido)->format('d/m/Y H:i') : 'No disponible' }}
                    </p>
                    @if($this->record->pedido->repartidor)
                        <p class="mt-1 text-sm text-gray-600">
                            Repartidor: {{ $this->record->pedido->repartidor->usuario->name ?? 'No asignado' }}
                        </p>
                    @endif
                </div>
            @endif
            
            @if($this->record->pedido)
                <div class="mt-6">
                    <h4 class="text-md font-medium">Historial completo de estados</h4>
                    @include('filament.components.historial-estados-avanzado', ['historial' => $this->record->pedido->historialEstados()->with('usuario')->orderBy('fecha_cambio', 'desc')->get()])
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::page>