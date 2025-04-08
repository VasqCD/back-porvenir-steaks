<?php

namespace App\Filament\Resources\HistorialEstadoPedidoResource\Pages;

use App\Filament\Resources\HistorialEstadoPedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\HistorialEstadosOverview;

class ListHistorialEstadoPedidos extends ListRecords
{
    protected static string $resource = HistorialEstadoPedidoResource::class;
    
    protected static string $view = 'filament.resources.historial-estado-pedido-resource.pages.list-historial-estado-pedidos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            HistorialEstadosOverview::class,
        ];
    }
}