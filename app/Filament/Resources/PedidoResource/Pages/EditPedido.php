<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use App\Models\HistorialEstadoPedido;
use App\Models\Notificacion;
use App\Services\FcmService;
use Filament\Actions;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Agregar este método para manejar las notificaciones después de guardar
    protected function afterSave(): void
    {
        $pedido = $this->record;
        
        // Verificar si se cambió el estado o el repartidor
        if ($pedido->isDirty('estado') || $pedido->isDirty('repartidor_id')) {
            
            // Si cambió el estado, registrarlo en el historial
            if ($pedido->isDirty('estado')) {
                $estadoAnterior = $pedido->getOriginal('estado');
                $estadoNuevo = $pedido->estado;
                
                HistorialEstadoPedido::create([
                    'pedido_id' => $pedido->id,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $estadoNuevo,
                    'fecha_cambio' => now(),
                    'usuario_id' => auth()->id(),
                ]);
                
                // Enviar notificación de cambio de estado
                try {
                    $fcmService = app(FcmService::class);
                    $fcmService->sendPedidoStatusNotification(
                        $pedido->usuario,
                        (string) $pedido->id,
                        $pedido->estado
                    );
                    
                    FilamentNotification::make()
                        ->title('Estado actualizado')
                        ->body('Se ha enviado una notificación al cliente')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Log::error('Error al enviar notificación de cambio de estado: ' . $e->getMessage());
                    
                    FilamentNotification::make()
                        ->title('Estado actualizado')
                        ->body('Pero hubo un problema al enviar la notificación al cliente')
                        ->warning()
                        ->send();
                }
            }
            
            // Si se asignó un repartidor y no tenía uno antes
            if ($pedido->isDirty('repartidor_id') && !$pedido->getOriginal('repartidor_id')) {
                // Crear notificación para el cliente
                Notificacion::create([
                    'usuario_id' => $pedido->usuario_id,
                    'pedido_id' => $pedido->id,
                    'titulo' => 'Repartidor asignado',
                    'mensaje' => 'Se ha asignado un repartidor a tu pedido. Pronto estará en camino.',
                    'tipo' => 'repartidor_asignado',
                ]);
                
                FilamentNotification::make()
                    ->title('Repartidor asignado')
                    ->body('Se ha notificado al cliente')
                    ->success()
                    ->send();
            }
        }
    }
}