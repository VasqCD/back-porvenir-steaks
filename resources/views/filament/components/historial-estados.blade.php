{{-- resources/views/filament/components/historial-estados.blade.php --}}
<div class="space-y-3">
    @if($historial->isEmpty())
        <div class="p-4 rounded-lg bg-gray-50 text-center text-gray-500">
            No hay cambios de estado registrados.
        </div>
    @else
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach($historial as $item)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    @php
                                        $colorClass = match($item->estado_nuevo) {
                                            'pendiente' => 'bg-yellow-500',
                                            'en_cocina' => 'bg-blue-500',
                                            'en_camino' => 'bg-indigo-500',
                                            'entregado' => 'bg-green-500',
                                            'cancelado' => 'bg-red-500',
                                            default => 'bg-gray-400',
                                        };
                                        
                                        $iconClass = match($item->estado_nuevo) {
                                            'pendiente' => 'clock',
                                            'en_cocina' => 'fire',
                                            'en_camino' => 'truck',
                                            'entregado' => 'check-circle',
                                            'cancelado' => 'x-circle',
                                            default => 'information-circle',
                                        };
                                    @endphp
                                    
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $colorClass }}">
                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            @if($iconClass === 'clock')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @elseif($iconClass === 'fire')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                            @elseif($iconClass === 'truck')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                            @elseif($iconClass === 'check-circle')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @elseif($iconClass === 'x-circle')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                            @endif
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Cambió de <span class="font-medium">{{ ucfirst($item->estado_anterior) }}</span> a <span class="font-medium">{{ ucfirst($item->estado_nuevo) }}</span>
                                        </p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-xs text-gray-500 flex flex-col">
                                        <time datetime="{{ $item->fecha_cambio }}">{{ \Carbon\Carbon::parse($item->fecha_cambio)->format('d/m/Y H:i') }}</time>
                                        <span>por {{ $item->usuario->name ?? 'Usuario desconocido' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>