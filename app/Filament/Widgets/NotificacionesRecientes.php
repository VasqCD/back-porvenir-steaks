<?php

namespace App\Filament\Widgets;

use App\Models\Notificacion;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class NotificacionesRecientes extends Widget
{
    protected static string $view = 'filament.widgets.notificaciones-recientes';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;
    
    public function getNotificaciones()
    {
        return Notificacion::whereIn('tipo', [
            'nuevo_pedido',
            'solicitud_repartidor',
            'pedido_cancelado'
        ])
        ->where('leida', false)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    }
    
    public function marcarLeida($id)
    {
        $notificacion = Notificacion::find($id);
        if ($notificacion) {
            $notificacion->update(['leida' => true]);
            $this->dispatch('notificacion-actualizada');
        }
    }
    
    public function marcarTodasLeidas()
    {
        Notificacion::where('leida', false)
            ->update(['leida' => true]);
        
        $this->dispatch('notificaciones-actualizadas');
    }
    
    public function getNotificacionesNoLeidas()
    {
        return Notificacion::where('leida', false)->count();
    }
}