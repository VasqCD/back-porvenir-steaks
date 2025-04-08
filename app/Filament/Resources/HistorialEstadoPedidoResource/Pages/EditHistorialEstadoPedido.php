<?php

namespace App\Filament\Resources\HistorialEstadoPedidoResource\Pages;

use App\Filament\Resources\HistorialEstadoPedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHistorialEstadoPedido extends EditRecord
{
    protected static string $resource = HistorialEstadoPedidoResource::class;
    
    protected static string $view = 'filament.resources.historial-estado-pedido-resource.pages.edit-historial-estado-pedido';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            
            Actions\Action::make('verPedido')
                ->label('Ver pedido')
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.pedidos.edit', ['record' => $this->record->pedido_id]))
                ->openUrlInNewTab(),
        ];
    }
    
    protected function afterSave(): void
    {
        // Actualizar el estado del pedido si es necesario
        if ($this->record && $this->record->pedido) {
            // Solo actualizar si el estado actual del pedido es diferente al nuevo estado en el historial
            if ($this->record->pedido->estado !== $this->record->estado_nuevo) {
                $this->record->pedido->update([
                    'estado' => $this->record->estado_nuevo,
                    'fecha_entrega' => $this->record->estado_nuevo === 'entregado' ? now() : $this->record->pedido->fecha_entrega,
                ]);
            }
        }
    }
}