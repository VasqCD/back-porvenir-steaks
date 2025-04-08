<?php

namespace App\Filament\Resources\HistorialEstadoPedidoResource\Pages;

use App\Filament\Resources\HistorialEstadoPedidoResource;
use App\Models\Pedido;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateHistorialEstadoPedido extends CreateRecord
{
    protected static string $resource = HistorialEstadoPedidoResource::class;
    
    protected function afterCreate(): void
    {
        // Actualizar el estado del pedido
        $pedidoId = $this->record->pedido_id;
        $nuevoEstado = $this->record->estado_nuevo;
        
        $pedido = Pedido::find($pedidoId);
        if ($pedido) {
            $pedido->update([
                'estado' => $nuevoEstado,
                'fecha_entrega' => $nuevoEstado === 'entregado' ? now() : $pedido->fecha_entrega,
            ]);
            
            // Notificar al usuario que el pedido ha sido actualizado
            Notification::make()
                ->title('Estado del pedido actualizado')
                ->body("El pedido #{$pedidoId} ha sido actualizado a '{$nuevoEstado}'.")
                ->success()
                ->send();
        }
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            HistorialEstadoPedidoResource\Widgets\InstruccionesWidget::class,
        ];
    }
}