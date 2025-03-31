<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Mostrar las notificaciones del usuario.
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
     * Marcar una notificación como leída.
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
     * Marcar todas las notificaciones como leídas.
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
     * Eliminar una notificación.
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
