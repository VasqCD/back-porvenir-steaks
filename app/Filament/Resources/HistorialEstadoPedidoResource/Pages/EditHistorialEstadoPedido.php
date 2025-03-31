<?php

namespace App\Filament\Resources\HistorialEstadoPedidoResource\Pages;

use App\Filament\Resources\HistorialEstadoPedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHistorialEstadoPedido extends EditRecord
{
    protected static string $resource = HistorialEstadoPedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
