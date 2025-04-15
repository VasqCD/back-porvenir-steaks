<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use App\Models\HistorialEstadoPedido;
use App\Models\Notificacion;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification as FilamentNotification;
use App\Services\FcmService;

class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;
    
    protected function afterCreate(): void
    {
        $pedido = $this->record;
        
        // Registrar estado inicial en historial
        $estadoAnterior = $pedido->getOriginal('estado') ?? 'nuevo';

        HistorialEstadoPedido::create([
            'pedido_id' => $pedido->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $pedido->estado,
            'fecha_cambio' => now(),
            'usuario_id' => auth()->id(),
        ]);
        
        // Crear notificación para el cliente
        Notificacion::create([
            'usuario_id' => $pedido->usuario_id,
            'pedido_id' => $pedido->id,
            'titulo' => 'Nuevo Pedido',
            'mensaje' => 'Tu pedido ha sido registrado exitosamente.',
            'tipo' => 'nuevo_pedido',
        ]);
        
        // Notificar al cliente si tiene tokens FCM
        try {
            $fcmService = app(FcmService::class);
            $fcmService->sendPedidoStatusNotification(
                $pedido->usuario,
                (string) $pedido->id,
                $pedido->estado
            );
            
            FilamentNotification::make()
                ->title('Pedido creado')
                ->body('Se ha enviado una notificación al cliente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al enviar notificación de nuevo pedido: ' . $e->getMessage());
        }
    }
}