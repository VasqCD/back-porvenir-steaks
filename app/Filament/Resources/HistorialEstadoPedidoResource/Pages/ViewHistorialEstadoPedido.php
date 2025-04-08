<?php

namespace App\Filament\Resources\HistorialEstadoPedidoResource\Pages;

use App\Filament\Resources\HistorialEstadoPedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHistorialEstadoPedido extends ViewRecord
{
    protected static string $resource = HistorialEstadoPedidoResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('verPedido')
                ->label('Ver pedido')
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.pedidos.edit', ['record' => $this->record->pedido_id]))
                ->openUrlInNewTab(),
        ];
    }
}