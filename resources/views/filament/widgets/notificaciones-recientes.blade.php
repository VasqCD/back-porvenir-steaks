<x-filament::widget>
    <x-filament::card>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold tracking-tight flex items-center">
                <x-heroicon-o-bell class="h-6 w-6 mr-2 text-primary-500" />
                Notificaciones recientes
                @if($this->getNotificacionesNoLeidas() > 0)
                    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-primary-600 rounded-full">
                        {{ $this->getNotificacionesNoLeidas() }}
                    </span>
                @endif
            </h2>
            
            @if($this->getNotificacionesNoLeidas() > 0)
                <button 
                    wire:click="marcarTodasLeidas"
                    type="button" 
                    class="text-sm text-gray-500 hover:text-primary-500"
                >
                    Marcar todas como leídas
                </button>
            @endif
        </div>
        
        <div class="mt-4 space-y-4 max-h-80 overflow-y-auto">
            @forelse($this->getNotificaciones() as $notificacion)
                <div class="flex items-start p-4 bg-white border rounded-lg shadow-sm relative {{ $notificacion->leida ? 'opacity-60' : '' }}">
                    <div class="mr-4 flex-shrink-0">
                        @switch($notificacion->tipo)
                            @case('nuevo_pedido')
                                <x-heroicon-o-shopping-cart class="h-6 w-6 text-blue-500" />
                                @break
                            @case('solicitud_repartidor')
                                <x-heroicon-o-user-plus class="h-6 w-6 text-orange-500" />
                                @break
                            @case('pedido_cancelado')
                                <x-heroicon-o-x-circle class="h-6 w-6 text-red-500" />
                                @break
                            @default
                                <x-heroicon-o-bell class="h-6 w-6 text-gray-500" />
                        @endswitch
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900">{{ $notificacion->titulo }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $notificacion->mensaje }}</p>
                        <div class="mt-2 flex space-x-2">
                            @if($notificacion->pedido_id)
                                <a 
                                    href="{{ route('filament.admin.resources.pedidos.edit', ['record' => $notificacion->pedido_id]) }}" 
                                    class="text-xs font-medium text-primary-600 hover:text-primary-500"
                                >
                                    Ver pedido
                                </a>
                            @elseif($notificacion->tipo == 'solicitud_repartidor')
                                <a 
                                    href="{{ route('filament.admin.resources.users.index', ['tableFilters[rol][value]' => 'cliente']) }}" 
                                    class="text-xs font-medium text-primary-600 hover:text-primary-500"
                                >
                                    Ver usuarios
                                </a>
                            @endif
                            
                            @if(!$notificacion->leida)
                                <button 
                                    wire:click="marcarLeida({{ $notificacion->id }})"
                                    type="button" 
                                    class="text-xs font-medium text-gray-500 hover:text-gray-700"
                                >
                                    Marcar como leída
                                </button>
                            @endif
                        </div>
                        <span class="absolute top-2 right-2 text-xs text-gray-400">
                            {{ $notificacion->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <x-heroicon-o-bell-slash class="h-8 w-8 mx-auto mb-2" />
                    No hay notificaciones pendientes
                </div>
            @endforelse
        </div>
        
        @if(count($this->getNotificaciones()) > 0)
            <div class="mt-4 text-center">
                <a 
                    href="{{ route('filament.admin.resources.notificaciones.index') }}" 
                    class="text-sm font-medium text-primary-600 hover:text-primary-500"
                >
                    Ver todas las notificaciones
                </a>
            </div>
        @endif
    </x-filament::card>
</x-filament::widget>