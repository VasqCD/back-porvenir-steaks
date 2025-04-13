<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\User;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = Firebase::messaging();
    }

    /**
     * Registrar o actualizar token FCM
     */
    public function saveToken(User $user, string $token, string $deviceType = 'android')
    {
        // Buscar si ya existe el token
        $existingToken = FcmToken::where('token', $token)->first();

        if ($existingToken) {
            // Si el token existe pero pertenece a otro usuario, desactivar el anterior
            if ($existingToken->usuario_id != $user->id) {
                $existingToken->active = false;
                $existingToken->save();

                // Crear nuevo registro para el usuario actual
                FcmToken::create([
                    'usuario_id' => $user->id,
                    'token' => $token,
                    'device_type' => $deviceType,
                    'active' => true
                ]);
            } else {
                // Actualizar estado si es necesario
                if (!$existingToken->active) {
                    $existingToken->active = true;
                    $existingToken->save();
                }
            }
        } else {
            // Crear nuevo registro
            FcmToken::create([
                'usuario_id' => $user->id,
                'token' => $token,
                'device_type' => $deviceType,
                'active' => true
            ]);
        }

        return true;
    }

    /**
     * Enviar notificación a un usuario
     */
    public function sendNotification(User $user, string $title, string $body, array $data = [])
    {
        $tokens = $user->fcmTokens()
            ->where('active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Enviar notificación por cambio de estado de pedido
     */
    public function sendPedidoStatusNotification(User $user, string $pedidoId, string $status)
    {
        // Preparar mensajes según estado
        $messages = [
            'pendiente' => 'Tu pedido ha sido recibido y está pendiente de procesamiento.',
            'en_cocina' => 'Tu pedido está siendo preparado en la cocina.',
            'en_camino' => 'Tu pedido está en camino a tu ubicación.',
            'entregado' => 'Tu pedido ha sido entregado. ¡Buen provecho!',
            'cancelado' => 'Tu pedido ha sido cancelado.'
        ];

        $title = 'Actualización de pedido';
        $body = $messages[$status] ?? "El estado de tu pedido ha cambiado a: $status";

        $data = [
            'pedido_id' => $pedidoId,
            'status' => $status,
            'type' => 'pedido_update'
        ];

        // También guardar en la tabla de notificaciones
        Notificacion::create([
            'usuario_id' => $user->id,
            'pedido_id' => $pedidoId,
            'titulo' => $title,
            'mensaje' => $body,
            'tipo' => 'estado_pedido',
            'leida' => false
        ]);

        return $this->sendNotification($user, $title, $body, $data);
    }

    /**
     * Enviar notificación de solicitud de repartidor
     * 
     * @param User $admin Administrador que recibirá la notificación
     * @param User $solicitante Usuario que solicita ser repartidor
     * @return mixed Resultado de la notificación
     */
    public function sendRepartidorRequestNotification(User $admin, User $solicitante)
    {
        $title = 'Nueva solicitud de repartidor';
        $body = "El usuario {$solicitante->name} ha solicitado ser repartidor";
        $data = [
            'type' => 'solicitud_repartidor',
            'usuario_id' => $solicitante->id,
        ];

        // Crear notificación en la base de datos
        Notificacion::create([
            'usuario_id' => $admin->id,
            'titulo' => $title,
            'mensaje' => $body,
            'tipo' => 'solicitud_repartidor',
            'leida' => false
        ]);

        // Enviar notificación push
        return $this->sendNotification($admin, $title, $body, $data);
    }

    /**
     * Método de envío a tokens específicos usando Firebase SDK
     */
    private function sendToTokens(array $tokens, string $title, string $body, array $data = [])
    {
        try {
            $notification = FirebaseNotification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $sendReport = $this->messaging->sendMulticast($message, $tokens);

            // Verificar si hay tokens inválidos para marcarlos
            if ($sendReport->hasFailures()) {
                foreach ($sendReport->failures()->getItems() as $failure) {
                    $token = $tokens[$failure->index()];
                    // Marcar token como inactivo
                    FcmToken::where('token', $token)->update(['active' => false]);
                    Log::warning("FCM token failure: {$failure->reason()}");
                }
            }

            return [
                'success' => $sendReport->successes()->count(),
                'failures' => $sendReport->failures()->count()
            ];
        } catch (\Exception $e) {
            Log::error('Firebase error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Método alternativo de envío para casos donde el SDK no funcione
     * Este método usa directamente la API HTTP de FCM
     */
    public function sendNotificationToTokens(array $tokens, array $data)
    {
        if (empty($tokens)) {
            return false;
        }

        $serverKey = config('services.fcm.server_key');
        if (empty($serverKey)) {
            Log::warning('FCM server key no está configurada. Usando el método de SDK.');
            return $this->sendToTokens($tokens, $data['title'], $data['body'], $data['data'] ?? []);
        }

        $client = new \GuzzleHttp\Client();

        $message = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'sound' => 'default',
            ],
            'data' => $data['data'] ?? [],
            'priority' => 'high',
        ];

        try {
            $response = $client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $message,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            Log::info('FCM notification sent via HTTP API', ['result' => $result]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación FCM via HTTP API: ' . $e->getMessage());
            // Intentar con el método SDK como fallback
            return $this->sendToTokens($tokens, $data['title'], $data['body'], $data['data'] ?? []);
        }
    }
}