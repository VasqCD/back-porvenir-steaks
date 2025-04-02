<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use Illuminate\Http\Request;

/**
 * @group Notificaciones FCM
 *
 * APIs para administrar los tokens FCM (Firebase Cloud Messaging) y enviar notificaciones push
 */
class FcmController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Registrar token FCM
     *
     * Registra un token FCM para el usuario autenticado, permitiendo el envío de notificaciones push.
     *
     * @bodyParam token string required Token FCM del dispositivo. Example: fMEYI7D6T-KOMyUyP0Rj1B:APA91bGj6kRc5...
     * @bodyParam device_type string nullable Tipo de dispositivo (android, ios, web). Example: android
     *
     * @response {
     *    "message": "Token FCM registrado exitosamente"
     * }
     *
     * @authenticated
     */
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
        ]);

        $user = $request->user();
        $deviceType = $request->input('device_type', 'android');

        $this->fcmService->saveToken($user, $request->token, $deviceType);

        return response()->json([
            'message' => 'Token FCM registrado exitosamente'
        ]);
    }

    /**
     * Enviar notificación de prueba
     *
     * Envía una notificación push de prueba al dispositivo del usuario autenticado.
     *
     * @response {
     *    "message": "Notificación enviada exitosamente",
     *    "result": {
     *      "success": 1,
     *      "failures": 0
     *    }
     * }
     *
     * @response 400 {
     *    "message": "No se pudo enviar la notificación. Asegúrate de haber registrado tu token FCM."
     * }
     *
     * @authenticated
     */
    public function testNotification(Request $request)
    {
        $user = $request->user();

        $result = $this->fcmService->sendNotification(
            $user,
            'Notificación de prueba',
            'Esta es una notificación de prueba desde la API'
        );

        if (!$result) {
            return response()->json([
                'message' => 'No se pudo enviar la notificación. Asegúrate de haber registrado tu token FCM.'
            ], 400);
        }

        return response()->json([
            'message' => 'Notificación enviada exitosamente',
            'result' => $result
        ]);
    }
}
