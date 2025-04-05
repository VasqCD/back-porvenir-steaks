{{-- resources/views/filament/pages/pedido-detalles.blade.php --}}
<div class="p-4">
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Información del pedido</h3>
            <div class="mt-2 border-t border-gray-200 pt-2">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-600">Número de pedido:</div>
                    <div class="font-medium">{{ $pedido->id }}</div>
                    
                    <div class="text-gray-600">Cliente:</div>
                    <div class="font-medium">{{ $pedido->usuario->name }}</div>
                    
                    <div class="text-gray-600">Estado:</div>
                    <div>
                        @php
                            $badgeColor = match($pedido->estado) {
                                'pendiente' => 'bg-yellow-100 text-yellow-800',
                                'en_cocina' => 'bg-blue-100 text-blue-800',
                                'en_camino' => 'bg-indigo-100 text-indigo-800',
                                'entregado' => 'bg-green-100 text-green-800',
                                'cancelado' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeColor }}">
                            {{ ucfirst($pedido->estado) }}
                        </span>
                    </div>
                    
                    <div class="text-gray-600">Fecha de pedido:</div>
                    <div class="font-medium">{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i') }}</div>
                    
                    @if($pedido->fecha_entrega)
                        <div class="text-gray-600">Fecha de entrega:</div>
                        <div class="font-medium">{{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y H:i') }}</div>
                    @endif
                    
                    @if($pedido->repartidor)
                        <div class="text-gray-600">Repartidor:</div>
                        <div class="font-medium">{{ $pedido->repartidor->usuario->name }}</div>
                    @endif
                    
                    @if($pedido->calificacion)
                        <div class="text-gray-600">Calificación:</div>
                        <div class="font-medium">
                            @include('filament.components.star-rating', ['rating' => $pedido->calificacion])
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-base font-semibold text-gray-900">Dirección de entrega</h3>
            <div class="mt-2 border-t border-gray-200 pt-2">
                <div class="text-sm">
                    <p class="font-medium">{{ $pedido->ubicacion->direccion_completa }}</p>
                    @if($pedido->ubicacion->referencias)
                        <p class="mt-1 text-gray-600">Referencias: {{ $pedido->ubicacion->referencias }}</p>
                    @endif
                    <p class="mt-1 text-gray-600">
                        @if($pedido->ubicacion->colonia)
                            Col. {{ $pedido->ubicacion->colonia }},
                        @endif
                        @if($pedido->ubicacion->ciudad)
                            {{ $pedido->ubicacion->ciudad }}
                        @endif
                        @if($pedido->ubicacion->codigo_postal)
                            - {{ $pedido->ubicacion->codigo_postal }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detalles de productos -->
    <h3 class="text-base font-semibold text-gray-900">Productos</h3>
    <div class="mt-2 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Producto</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Precio unitario</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Cantidad</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($pedido->detalles as $detalle)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $detalle->producto->nombre }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">L {{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $detalle->cantidad }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">L {{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-gray-200">
                    <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 text-right text-sm font-semibold text-gray-900 sm:table-cell">Subtotal</th>
                    <td class="pl-3 pr-4 pt-4 text-right text-sm font-semibold text-gray-900">L {{ number_format($pedido->detalles->sum('subtotal'), 2) }}</td>
                </tr>
                <tr>
                    <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 text-right text-sm font-normal text-gray-500 sm:table-cell">Impuesto (15%)</th>
                    <td class="pl-3 pr-4 pt-4 text-right text-sm text-gray-500">L {{ number_format($pedido->detalles->sum('subtotal') * 0.15, 2) }}</td>
                </tr>
                <tr>
                    <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 pb-4 text-right text-sm font-semibold text-gray-900 sm:table-cell">Total</th>
                    <td class="pl-3 pr-4 pt-4 pb-4 text-right text-sm font-semibold text-gray-900">L {{ number_format($pedido->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    @if($pedido->comentario_calificacion)
        <div class="mt-6">
            <h3 class="text-base font-semibold text-gray-900">Comentario del cliente</h3>
            <div class="mt-2 rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                {{ $pedido->comentario_calificacion }}
            </div>
        </div>
    @endif
</div>