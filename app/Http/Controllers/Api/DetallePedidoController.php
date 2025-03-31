<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\Request;

class DetallePedidoController extends Controller
{
    /**
     * Mostrar todos los detalles de un pedido.
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
     * Mostrar un detalle específico.
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
