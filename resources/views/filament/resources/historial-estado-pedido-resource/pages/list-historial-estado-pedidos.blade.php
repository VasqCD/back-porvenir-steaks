{{-- resources/views/filament/resources/historial-estado-pedido-resource/pages/list-historial-estado-pedidos.blade.php --}}
<x-filament::page>
    {{ $this->table }}

    {{-- Resumen visual de estados --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Resumen de cambios de estado
        </x-slot>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @php
                $estadosCount = [
                    'pendiente' => App\Models\HistorialEstadoPedido::where('estado_nuevo', 'pendiente')->count(),
                    'en_cocina' => App\Models\HistorialEstadoPedido::where('estado_nuevo', 'en_cocina')->count(),
                    'en_camino' => App\Models\HistorialEstadoPedido::where('estado_nuevo', 'en_camino')->count(),
                    'entregado' => App\Models\HistorialEstadoPedido::where('estado_nuevo', 'entregado')->count(),
                    'cancelado' => App\Models\HistorialEstadoPedido::where('estado_nuevo', 'cancelado')->count(),
                ];
                
                $totalCambios = array_sum($estadosCount);
            @endphp
            
            @foreach($estadosCount as $estado => $count)
                @php
                    $colorClass = match($estado) {
                        'pendiente' => 'bg-yellow-100 text-yellow-800 ring-yellow-500/10',
                        'en_cocina' => 'bg-blue-100 text-blue-800 ring-blue-500/10',
                        'en_camino' => 'bg-indigo-100 text-indigo-800 ring-indigo-500/10',
                        'entregado' => 'bg-green-100 text-green-800 ring-green-500/10',
                        'cancelado' => 'bg-red-100 text-red-800 ring-red-500/10',
                        default => 'bg-gray-100 text-gray-800 ring-gray-500/10',
                    };
                    
                    $porcentaje = $totalCambios > 0 ? round(($count / $totalCambios) * 100, 1) : 0;
                @endphp
                
                <div class="relative overflow-hidden rounded-lg px-4 py-5 shadow sm:px-6 {{ $colorClass }}">
                    <div class="absolute opacity-10 right-2 top-2">
                        @if($estado === 'pendiente')
                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($estado === 'en_cocina')
                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                            </svg>
                        @elseif($estado === 'en_camino')
                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        @elseif($estado === 'entregado')
                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($estado === 'cancelado')
                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    
                    <dt class="truncate text-sm font-medium">
                        {{ ucfirst($estado) }}
                    </dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                        <div class="flex items-baseline text-2xl font-semibold">
                            {{ $count }}
                            <span class="ml-2 text-sm font-medium text-opacity-75">
                                ({{ $porcentaje }}%)
                            </span>
                        </div>
                    </dd>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::page>