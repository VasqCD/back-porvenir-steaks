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
        ];
    }
}
