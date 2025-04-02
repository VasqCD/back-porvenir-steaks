<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Registrar token FCM para el usuario autenticado
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
