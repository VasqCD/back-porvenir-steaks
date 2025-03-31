<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Notificacion;
use App\Models\HistorialEstadoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /**
     * Mostrar listado de pedidos del usuario autenticado.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        $query = Pedido::with(['detalles.producto', 'ubicacion', 'repartidor.usuario'])
            ->where('usuario_id', $usuario->id);

        // Filtrar por estado si se proporciona
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Ordenar por fecha, más recientes primero
        $query->orderBy('fecha_pedido', 'desc');

        $pedidos = $query->get();

        return response()->json($pedidos);
    }

    /**
     * Crear un nuevo pedido.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ubicacion_id' => 'required|exists:ubicaciones,id',
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $usuario = $request->user();
            $total = 0;

            // Calcular total y verificar productos
            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['producto_id']);

                if (!$producto->disponible) {
                    return response()->json([
                        'message' => "El producto {$producto->nombre} no está disponible"
                    ], 422);
                }

                $subtotal = $producto->precio * $item['cantidad'];
                $total += $subtotal;
            }

            // Crear pedido
            $pedido = new Pedido([
                'usuario_id' => $usuario->id,
                'ubicacion_id' => $request->ubicacion_id,
                'estado' => 'pendiente',
                'total' => $total,
                'fecha_pedido' => now(),
            ]);

            $pedido->save();

            // Crear detalles del pedido
            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $subtotal = $producto->precio * $item['cantidad'];

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);
            }

            // Registrar historial
            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'estado_anterior' => null,
                'estado_nuevo' => 'pendiente',
                'fecha_cambio' => now(),
                'usuario_id' => $usuario->id,
            ]);

            // Crear notificación para el administrador
            Notificacion::create([
                'usuario_id' => $usuario->id,
                'pedido_id' => $pedido->id,
                'titulo' => 'Nuevo Pedido',
                'mensaje' => "El usuario {$usuario->name} ha realizado un nuevo pedido",
                'tipo' => 'nuevo_pedido',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'pedido' => $pedido->load(['detalles.producto', 'ubicacion'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al crear el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un pedido específico.
     */
    public function show($id)
    {
        $usuario = request()->user();

        $pedido = Pedido::with(['detalles.producto', 'ubicacion', 'repartidor.usuario', 'historialEstados.usuario'])
            ->findOrFail($id);

        // Verificar que el pedido pertenezca al usuario o sea repartidor/admin
        if ($pedido->usuario_id != $usuario->id &&
            $usuario->rol != 'administrador' &&
            ($usuario->rol != 'repartidor' || $pedido->repartidor_id != $usuario->repartidor->id)) {
            return response()->json([
                'message' => 'No tiene permiso para ver este pedido'
            ], 403);
        }

        return response()->json($pedido);
    }

    /**
     * Actualizar el estado de un pedido.
     */
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_cocina,en_camino,entregado,cancelado',
        ]);

        $usuario = $request->user();
        $pedido = Pedido::findOrFail($id);

        // Verificar permisos según el rol
        if ($usuario->rol == 'cliente' && $usuario->id != $pedido->usuario_id) {
            return response()->json([
                'message' => 'No tiene permiso para actualizar este pedido'
            ], 403);
        }

        if ($usuario->rol == 'repartidor' &&
            ($pedido->repartidor_id != $usuario->repartidor->id ||
                !in_array($request->estado, ['en_camino', 'entregado']))) {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $estadoAnterior = $pedido->estado;
            $pedido->estado = $request->estado;

            // Actualizar fecha de entrega si corresponde
            if ($request->estado == 'entregado') {
                $pedido->fecha_entrega = now();
            }

            $pedido->save();

            // Registrar historial
            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $request->estado,
                'fecha_cambio' => now(),
                'usuario_id' => $usuario->id,
            ]);

            // Crear notificación para el cliente
            $tipoNotificacion = '';
            switch ($request->estado) {
                case 'en_cocina':
                    $tipoNotificacion = 'pedido_en_cocina';
                    $mensaje = 'Tu pedido está siendo preparado';
                    break;
                case 'en_camino':
                    $tipoNotificacion = 'pedido_en_camino';
                    $mensaje = 'Tu pedido está en camino';
                    break;
                case 'entregado':
                    $tipoNotificacion = 'pedido_entregado';
                    $mensaje = 'Tu pedido ha sido entregado';
                    break;
                default:
                    $tipoNotificacion = 'nuevo_pedido';
                    $mensaje = 'El estado de tu pedido ha cambiado a: ' . $request->estado;
            }

            Notificacion::create([
                'usuario_id' => $pedido->usuario_id,
                'pedido_id' => $pedido->id,
                'titulo' => 'Actualización de pedido',
                'mensaje' => $mensaje,
                'tipo' => $tipoNotificacion,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Estado de pedido actualizado exitosamente',
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al actualizar el estado del pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calificar un pedido.
     */
    public function calificar(Request $request, $id)
    {
        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario_calificacion' => 'nullable|string',
        ]);

        $usuario = $request->user();
        $pedido = Pedido::findOrFail($id);

        if ($pedido->usuario_id != $usuario->id) {
            return response()->json([
                'message' => 'No tiene permiso para calificar este pedido'
            ], 403);
        }

        if ($pedido->estado != 'entregado') {
            return response()->json([
                'message' => 'Solo puede calificar pedidos entregados'
            ], 422);
        }

        $pedido->calificacion = $request->calificacion;
        $pedido->comentario_calificacion = $request->comentario_calificacion;
        $pedido->save();

        return response()->json([
            'message' => 'Pedido calificado exitosamente',
            'pedido' => $pedido
        ]);
    }

    /**
     * Obtener pedidos pendientes (para administrador o repartidor)
     */
    public function pendientes(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->rol == 'cliente') {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        $query = Pedido::with(['detalles.producto', 'ubicacion', 'usuario']);

        if ($usuario->rol == 'repartidor') {
            // Repartidores ven los que están en estado "en_camino" y asignados a ellos
            $query->where('repartidor_id', $usuario->repartidor->id)
                ->where('estado', 'en_camino');
        } else {
            // Administradores ven todos los pendientes o en cocina
            $query->whereIn('estado', ['pendiente', 'en_cocina']);
        }

        $query->orderBy('fecha_pedido', 'asc');

        $pedidos = $query->get();

        return response()->json($pedidos);
    }

    /**
     * Asignar repartidor a un pedido (solo administrador)
     */
    public function asignarRepartidor(Request $request, $id)
    {
        $request->validate([
            'repartidor_id' => 'required|exists:repartidores,id',
        ]);

        $usuario = $request->user();

        if ($usuario->rol != 'administrador') {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        $pedido = Pedido::findOrFail($id);

        if (!in_array($pedido->estado, ['pendiente', 'en_cocina'])) {
            return response()->json([
                'message' => 'Solo se puede asignar repartidor a pedidos pendientes o en cocina'
            ], 422);
        }

        $pedido->repartidor_id = $request->repartidor_id;
        $pedido->save();

        return response()->json([
            'message' => 'Repartidor asignado exitosamente',
            'pedido' => $pedido
        ]);
    }
}
