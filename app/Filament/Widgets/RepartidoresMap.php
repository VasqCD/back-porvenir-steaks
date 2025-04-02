<?php
namespace App\Filament\Widgets;

use App\Models\Repartidor;
use Filament\Widgets\Widget;

class RepartidoresMap extends Widget
{
    protected static string $view = 'filament.widgets.repartidores-map';
    protected static ?int $sort = 3;

    public function getRepartidores()
    {
        return Repartidor::with('usuario')
            ->where('disponible', true)
            ->whereNotNull('ultima_ubicacion_lat')
            ->whereNotNull('ultima_ubicacion_lng')
            ->get();
    }
}
