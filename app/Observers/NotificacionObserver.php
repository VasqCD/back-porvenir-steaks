<?php

namespace App\Observers;

use App\Models\Notificacion;
use App\Services\FcmService;

class NotificacionObserver
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Handle the Notificacion "created" event.
     */
    public function created(Notificacion $notificacion): void
    {
        // Enviar push notification automáticamente
        $this->fcmService->sendNotification(
            $notificacion->usuario,
            $notificacion->titulo,
            $notificacion->mensaje,
            [
                'notification_id' => (string) $notificacion->id,
                'type' => $notificacion->tipo,
                'pedido_id' => $notificacion->pedido_id ? (string) $notificacion->pedido_id : null
            ]
        );
    }
}
