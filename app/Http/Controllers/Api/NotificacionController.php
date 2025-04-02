<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

/**
 * @group Gestión de Notificaciones
 *
 * APIs para administrar las notificaciones de los usuarios
 */
class NotificacionController extends Controller
{
    /**
     * Listar notificaciones del usuario
     *
     * Obtiene todas las notificaciones del usuario autenticado.
     *
     * @queryParam leida boolean Filtrar por notificaciones leídas (true) o no leídas (false). Example: false
     * @queryParam per_page integer Número de resultados por página. Example: 15
     *
     * @response {
     *    "current_page": 1,
     *    "data": [
     *      {
     *        "id": 1,
     *        "usuario_id": 1,
     *        "pedido_id": 1,
     *        "titulo": "Actualización de pedido",
     *        "mensaje": "Tu pedido está siendo preparado",
     *        "tipo": "pedido_en_cocina",
     *        "leida": false,
     *        "created_at": "2025-04-02T17:15:00.000000Z",
     *        "updated_at": "2025-04-02T17:15:00.000000Z"
     *      },
     *      {
     *        "id": 2,
     *        "usuario_id": 1,
     *        "pedido_id": 2,
     *        "titulo": "Nuevo Pedido",
     *        "mensaje": "Tu pedido ha sido recibido",
     *        "tipo": "nuevo_pedido",
     *        "leida": false,
     *        "created_at": "2025-04-02T17:00:00.000000Z",
     *        "updated_at": "2025-04-02T17:00:00.000000Z"
     *      }
     *    ],
     *    "first_page_url": "http://localhost/api/notificaciones?page=1",
     *    "from": 1,
     *    "last_page": 1,
     *    "last_page_url": "http://localhost/api/notificaciones?page=1",
     *    "links": [...],
     *    "next_page_url": null,
     *    "path": "http://localhost/api/notificaciones",
     *    "per_page": 15,
     *    "prev_page_url": null,
     *    "to": 2,
     *    "total": 2
     * }
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Notificacion::where('usuario_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filtrar por leídas/no leídas si se proporciona
        if ($request->has('leida')) {
            $query->where('leida', $request->leida);
        }

        // Paginación
        $perPage = $request->input('per_page', 15);
        $notificaciones = $query->paginate($perPage);

        return response()->json($notificaciones);
    }

    /**
     * Marcar una notificación como leída
     *
     * Marca una notificación específica como leída.
     *
     * @urlParam id integer required ID de la notificación. Example: 1
     *
     * @response {
     *    "message": "Notificación marcada como leída",
     *    "notificacion": {
     *      "id": 1,
     *      "usuario_id": 1,
     *      "pedido_id": 1,
     *      "titulo": "Actualización de pedido",
     *      "mensaje": "Tu pedido está siendo preparado",
     *      "tipo": "pedido_en_cocina",
     *      "leida": true,
     *      "created_at": "2025-04-02T17:15:00.000000Z",
     *      "updated_at": "2025-04-02T20:00:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para esta acción"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Notificacion] 99"
     * }
     *
     * @authenticated
     */
    public function marcarLeida(Request $request, $id)
    {
        $user = $request->user();
        $notificacion = Notificacion::findOrFail($id);

        // Verificar que la notificación pertenezca al usuario
        if ($notificacion->usuario_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        $notificacion->leida = true;
        $notificacion->save();

        return response()->json([
            'message' => 'Notificación marcada como leída',
            'notificacion' => $notificacion
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     *
     * Marca todas las notificaciones no leídas del usuario como leídas.
     *
     * @response {
     *    "message": "Todas las notificaciones marcadas como leídas"
     * }
     *
     * @authenticated
     */
    public function marcarTodasLeidas(Request $request)
    {
        $user = $request->user();

        Notificacion::where('usuario_id', $user->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Eliminar una notificación
     *
     * Elimina una notificación específica.
     *
     * @urlParam id integer required ID de la notificación. Example: 1
     *
     * @response {
     *    "message": "Notificación eliminada exitosamente"
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para esta acción"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Notificacion] 99"
     * }
     *
     * @authenticated
     */
    public function destroy($id)
    {
        $user = request()->user();
        $notificacion = Notificacion::findOrFail($id);

        // Verificar que la notificación pertenezca al usuario
        if ($notificacion->usuario_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        $notificacion->delete();

        return response()->json([
            'message' => 'Notificación eliminada exitosamente'
        ]);
    }
}
