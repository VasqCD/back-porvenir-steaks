<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\Request;

/**
 * @group Detalles de Pedidos
 *
 * APIs para consultar los detalles de los pedidos
 */
class DetallePedidoController extends Controller
{

    /**
     * Listar detalles de un pedido
     *
     * Obtiene todos los detalles de un pedido específico.
     *
     * @queryParam pedido_id integer required ID del pedido. Example: 1
     *
     * @response {
     *    "data": [
     *      {
     *        "id": 1,
     *        "pedido_id": 1,
     *        "producto_id": 1,
     *        "cantidad": 1,
     *        "precio_unitario": 350.00,
     *        "subtotal": 350.00,
     *        "created_at": "2025-04-02T10:00:00.000000Z",
     *        "updated_at": "2025-04-02T10:00:00.000000Z",
     *        "producto": {
     *          "id": 1,
     *          "nombre": "T-Bone Steak",
     *          "descripcion": "Corte premium de 16oz",
     *          "precio": 350.00,
     *          "imagen": "productos/tbone.jpg"
     *        }
     *      }
     *    ]
     * }
     *
     * @response 400 {
     *    "message": "Debe proporcionar un ID de pedido"
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para ver este pedido"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Pedido] 99"
     * }
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Verificar si se proporciona ID de pedido
        $pedidoId = $request->input('pedido_id');
        if ($pedidoId) {
            $pedido = Pedido::findOrFail($pedidoId);

            // Verificar permisos
            if ($pedido->usuario_id !== $user->id &&
                $user->rol !== 'administrador' &&
                ($user->rol !== 'repartidor' || $pedido->repartidor_id !== $user->repartidor->id)) {
                return response()->json([
                    'message' => 'No tiene permiso para ver este pedido'
                ], 403);
            }

            $detalles = DetallePedido::with('producto')
                ->where('pedido_id', $pedidoId)
                ->get();

            return response()->json($detalles);
        }

        // Si no hay pedido_id, devolver error
        return response()->json([
            'message' => 'Debe proporcionar un ID de pedido'
        ], 400);
    }

    /**
     * Mostrar un detalle específico
     *
     * Obtiene la información de un detalle específico de un pedido.
     *
     * @urlParam id integer required ID del detalle. Example: 1
     *
     * @response {
     *    "id": 1,
     *    "pedido_id": 1,
     *    "producto_id": 1,
     *    "cantidad": 1,
     *    "precio_unitario": 350.00,
     *    "subtotal": 350.00,
     *    "created_at": "2025-04-02T10:00:00.000000Z",
     *    "updated_at": "2025-04-02T10:00:00.000000Z",
     *    "producto": {
     *      "id": 1,
     *      "nombre": "T-Bone Steak",
     *      "descripcion": "Corte premium de 16oz",
     *      "precio": 350.00,
     *      "imagen": "productos/tbone.jpg"
     *    },
     *    "pedido": {
     *      "id": 1,
     *      "usuario_id": 1,
     *      "estado": "en_cocina",
     *      "total": 350.00,
     *      "fecha_pedido": "2025-04-02T10:00:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para ver este detalle"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\DetallePedido] 99"
     * }
     *
     * @authenticated
     */
    public function show($id)
    {
        $user = request()->user();
        $detalle = DetallePedido::with('producto', 'pedido')->findOrFail($id);
        $pedido = $detalle->pedido;

        // Verificar permisos
        if ($pedido->usuario_id !== $user->id &&
            $user->rol !== 'administrador' &&
            ($user->rol !== 'repartidor' || $pedido->repartidor_id !== $user->repartidor->id)) {
            return response()->json([
                'message' => 'No tiene permiso para ver este detalle'
            ], 403);
        }

        return response()->json($detalle);
    }
}
