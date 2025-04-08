<?php

namespace App\Filament\Resources\HistorialEstadoPedidoResource\Widgets;

use Filament\Widgets\Widget;

class PedidoHistorialWidget extends Widget
{
    protected static string $view = 'filament.resources.historial-estado-pedido-resource.widgets.pedido-historial-widget';

    public $record = null;

    public static function canView(): bool
    {
        return true;
    }

    public function mount($record = null)
    {
        $this->record = $record;
    }

    public static function getStyles(): array
    {
        return [
            'resources/css/historial-estados.css', // Solo para este componente
        ];
    }
    
}
