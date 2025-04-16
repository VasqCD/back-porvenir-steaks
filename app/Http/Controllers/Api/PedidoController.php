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
use App\Services\FcmService;

/**
 * @group Gestión de Pedidos
 *
 * APIs para la gestión del ciclo de vida de los pedidos
 */
class PedidoController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Listar pedidos del usuario
     *
     * Obtiene todos los pedidos realizados por el usuario autenticado.
     *
     * @queryParam estado string Filtrar por estado (pendiente, en_cocina, en_camino, entregado, cancelado). Example: pendiente
     *
     * @response {
     *    "data": [
     *      {
     *        "id": 1,
     *        "usuario_id": 1,
     *        "ubicacion_id": 1,
     *        "estado": "pendiente",
     *        "total": 350.00,
     *        "fecha_pedido": "2025-04-02T10:00:00.000000Z",
     *        "fecha_entrega": null,
     *        "repartidor_id": null,
     *        "calificacion": null,
     *        "comentario_calificacion": null,
     *        "created_at": "2025-04-02T10:00:00.000000Z",
     *        "updated_at": "2025-04-02T10:00:00.000000Z",
     *        "detalles": [
     *          {
     *            "id": 1,
     *            "pedido_id": 1,
     *            "producto_id": 1,
     *            "cantidad": 1,
     *            "precio_unitario": 350.00,
     *            "subtotal": 350.00,
     *            "producto": {
     *              "id": 1,
     *              "nombre": "T-Bone Steak",
     *              "imagen": "productos/tbone.jpg",
     *              "precio": 350.00
     *            }
     *          }
     *        ],
     *        "ubicacion": {
     *          "id": 1,
     *          "direccion_completa": "Calle Principal #123, Colonia Centro",
     *          "latitud": 14.12345,
     *          "longitud": -87.12345
     *        },
     *        "repartidor": null
     *      }
     *    ]
     * }
     *
     * @authenticated
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
     * Crear un nuevo pedido
     *
     * Crea un nuevo pedido con los productos seleccionados.
     *
     * @bodyParam ubicacion_id integer required ID de la ubicación de entrega. Example: 1
     * @bodyParam productos array required Lista de productos a ordenar.
     * @bodyParam productos.*.producto_id integer required ID del producto. Example: 1
     * @bodyParam productos.*.cantidad integer required Cantidad del producto (mínimo 1). Example: 2
     *
     * @response 201 {
     *    "message": "Pedido creado exitosamente",
     *    "pedido": {
     *      "id": 2,
     *      "usuario_id": 1,
     *      "ubicacion_id": 1,
     *      "estado": "pendiente",
     *      "total": 700.00,
     *      "fecha_pedido": "2025-04-02T17:00:00.000000Z",
     *      "fecha_entrega": null,
     *      "repartidor_id": null,
     *      "created_at": "2025-04-02T17:00:00.000000Z",
     *      "updated_at": "2025-04-02T17:00:00.000000Z",
     *      "detalles": [
     *        {
     *          "id": 2,
     *          "pedido_id": 2,
     *          "producto_id": 1,
     *          "cantidad": 2,
     *          "precio_unitario": 350.00,
     *          "subtotal": 700.00,
     *          "producto": {
     *            "id": 1,
     *            "nombre": "T-Bone Steak",
     *            "precio": 350.00
     *          }
     *        }
     *      ],
     *      "ubicacion": {
     *        "id": 1,
     *        "direccion_completa": "Calle Principal #123, Colonia Centro"
     *      }
     *    }
     * }
     *
     * @response 422 {
     *    "message": "El producto T-Bone Steak no está disponible"
     * }
     *
     * @authenticated
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
     * Mostrar un pedido específico
     *
     * Obtiene los detalles completos de un pedido específico.
     *
     * @urlParam id integer required ID del pedido. Example: 1
     *
     * @response {
     *    "id": 1,
     *    "usuario_id": 1,
     *    "ubicacion_id": 1,
     *    "estado": "pendiente",
     *    "total": 350.00,
     *    "fecha_pedido": "2025-04-02T10:00:00.000000Z",
     *    "fecha_entrega": null,
     *    "repartidor_id": null,
     *    "calificacion": null,
     *    "comentario_calificacion": null,
     *    "created_at": "2025-04-02T10:00:00.000000Z",
     *    "updated_at": "2025-04-02T10:00:00.000000Z",
     *    "detalles": [
     *      {
     *        "id": 1,
     *        "pedido_id": 1,
     *        "producto_id": 1,
     *        "cantidad": 1,
     *        "precio_unitario": 350.00,
     *        "subtotal": 350.00,
     *        "producto": {
     *          "id": 1,
     *          "nombre": "T-Bone Steak",
     *          "descripcion": "Corte premium de 16oz",
     *          "imagen": "productos/tbone.jpg",
     *          "precio": 350.00
     *        }
     *      }
     *    ],
     *    "ubicacion": {
     *      "id": 1,
     *      "direccion_completa": "Calle Principal #123, Colonia Centro",
     *      "latitud": 14.12345,
     *      "longitud": -87.12345
     *    },
     *    "repartidor": null,
     *    "historialEstados": []
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
    public function show($id)
    {
        $usuario = request()->user();

        $pedido = Pedido::with(['detalles.producto', 'ubicacion', 'repartidor.usuario', 'historialEstados.usuario'])
            ->findOrFail($id);

        // Verificar que el pedido pertenezca al usuario o sea repartidor/admin
        if (
            $pedido->usuario_id != $usuario->id &&
            $usuario->rol != 'administrador' &&
            ($usuario->rol != 'repartidor' || $pedido->repartidor_id != $usuario->repartidor->id)
        ) {
            return response()->json([
                'message' => 'No tiene permiso para ver este pedido'
            ], 403);
        }

        return response()->json($pedido);
    }

    /**
     * Actualizar estado de un pedido
     *
     * Actualiza el estado de un pedido y envía notificaciones.
     *
     * @urlParam id integer required ID del pedido. Example: 1
     * @bodyParam estado string required Nuevo estado del pedido (pendiente, en_cocina, en_camino, entregado, cancelado). Example: en_cocina
     *
     * @response {
     *    "message": "Estado de pedido actualizado exitosamente",
     *    "pedido": {
     *      "id": 1,
     *      "estado": "en_cocina",
     *      "updated_at": "2025-04-02T17:15:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para actualizar este pedido"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Pedido] 99"
     * }
     *
     * @authenticated
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

        if (
            $usuario->rol == 'repartidor' &&
            ($pedido->repartidor_id != $usuario->repartidor->id ||
                !in_array($request->estado, ['en_camino', 'entregado']))
        ) {
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

            $this->fcmService->sendPedidoStatusNotification(
                $pedido->usuario,
                (string) $pedido->id,
                $request->estado
            );

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
     * Calificar un pedido
     *
     * Permite al cliente calificar un pedido entregado.
     *
     * @urlParam id integer required ID del pedido. Example: 1
     * @bodyParam calificacion integer required Calificación del pedido (1-5). Example: 5
     * @bodyParam comentario_calificacion string nullable Comentario sobre la calificación. Example: Excelente servicio y comida de calidad
     *
     * @response {
     *    "message": "Pedido calificado exitosamente",
     *    "pedido": {
     *      "id": 1,
     *      "calificacion": 5,
     *      "comentario_calificacion": "Excelente servicio y comida de calidad",
     *      "updated_at": "2025-04-02T20:30:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para calificar este pedido"
     * }
     *
     * @response 422 {
     *    "message": "Solo puede calificar pedidos entregados"
     * }
     *
     * @authenticated
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
     * Listar pedidos pendientes
     *
     * Obtiene los pedidos pendientes para administradores y repartidores.
     * Los administradores ven pedidos 'pendiente' y 'en_cocina'.
     * Los repartidores solo ven sus pedidos asignados con estado 'en_camino'.
     *
     * @response {
     *    "data": [
     *      {
     *        "id": 1,
     *        "usuario_id": 1,
     *        "ubicacion_id": 1,
     *        "estado": "en_cocina",
     *        "total": 350.00,
     *        "fecha_pedido": "2025-04-02T10:00:00.000000Z",
     *        "fecha_entrega": null,
     *        "repartidor_id": null,
     *        "created_at": "2025-04-02T10:00:00.000000Z",
     *        "updated_at": "2025-04-02T17:15:00.000000Z",
     *        "detalles": [...],
     *        "ubicacion": {...},
     *        "usuario": {
     *          "id": 1,
     *          "name": "Juan Pérez",
     *          "telefono": "+504 9999-9999"
     *        }
     *      }
     *    ]
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para esta acción"
     * }
     *
     * @authenticated
     */
    public function pendientes(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->rol == 'cliente') {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        try {
            $query = Pedido::with(['detalles.producto', 'ubicacion', 'usuario']);

            if ($usuario->rol == 'repartidor' && $usuario->repartidor) {
                // Repartidores ven los que están en estado "en_camino" y asignados a ellos
                $query->where('repartidor_id', $usuario->repartidor->id)
                    ->where('estado', 'en_camino');
            } else if ($usuario->rol == 'administrador') {
                // Administradores ven todos los pendientes o en cocina
                $query->whereIn('estado', ['pendiente', 'en_cocina']);
            } else {
                // Si es repartidor pero no tiene perfil de repartidor asociado
                return response()->json([
                    'message' => 'Su perfil de repartidor no está correctamente configurado'
                ], 400);
            }

            $query->orderBy('fecha_pedido', 'asc');
            $pedidos = $query->get();

            return response()->json($pedidos);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener pedidos pendientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar repartidor a un pedido
     *
     * Asigna un repartidor a un pedido en estado 'pendiente' o 'en_cocina'.
     * Solo disponible para administradores.
     *
     * @urlParam id integer required ID del pedido. Example: 1
     * @bodyParam repartidor_id integer required ID del repartidor. Example: 1
     *
     * @response {
     *    "message": "Repartidor asignado exitosamente",
     *    "pedido": {
     *      "id": 1,
     *      "repartidor_id": 1,
     *      "updated_at": "2025-04-02T17:30:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para esta acción"
     * }
     *
     * @response 422 {
     *    "message": "Solo se puede asignar repartidor a pedidos pendientes o en cocina"
     * }
     *
     * @authenticated
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
