<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repartidor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * @group Gestión de Repartidores
 *
 * APIs para administrar los repartidores del sistema
 */
class RepartidorController extends Controller
{
    /**
     * Listar todos los repartidores
     *
     * Obtiene un listado de todos los repartidores registrados.
     *
     * @response {
     *    "data": [
     *      {
     *        "id": 1,
     *        "usuario_id": 2,
     *        "disponible": true,
     *        "ultima_ubicacion_lat": 14.55555,
     *        "ultima_ubicacion_lng": -87.55555,
     *        "ultima_actualizacion": "2025-04-02T18:30:00.000000Z",
     *        "created_at": "2025-04-01T11:00:00.000000Z",
     *        "updated_at": "2025-04-02T18:30:00.000000Z",
     *        "usuario": {
     *          "id": 2,
     *          "name": "Carlos Martínez",
     *          "apellido": "López",
     *          "email": "carlos@ejemplo.com",
     *          "telefono": "+504 8888-8888",
     *          "rol": "repartidor"
     *        }
     *      },
     *      {
     *        "id": 2,
     *        "usuario_id": 3,
     *        "disponible": true,
     *        "ultima_ubicacion_lat": 14.66666,
     *        "ultima_ubicacion_lng": -87.66666,
     *        "ultima_actualizacion": "2025-04-02T18:35:00.000000Z",
     *        "created_at": "2025-04-01T11:15:00.000000Z",
     *        "updated_at": "2025-04-02T18:35:00.000000Z",
     *        "usuario": {
     *          "id": 3,
     *          "name": "Ana García",
     *          "apellido": "Mendoza",
     *          "email": "ana@ejemplo.com",
     *          "telefono": "+504 7777-7777",
     *          "rol": "repartidor"
     *        }
     *      }
     *    ]
     * }
     *
     * @authenticated
     */
    public function index()
    {
        $repartidores = Repartidor::with('usuario')->get();
        return response()->json($repartidores);
    }

    /**
     * Crear un nuevo repartidor
     *
     * Registra un nuevo repartidor en el sistema. Crea tanto el usuario como el perfil de repartidor.
     *
     * @bodyParam name string required Nombre del repartidor. Example: Luis
     * @bodyParam apellido string nullable Apellido del repartidor. Example: Hernández
     * @bodyParam email string required Email del repartidor (debe ser único). Example: luis@ejemplo.com
     * @bodyParam password string required Contraseña del repartidor (mínimo 8 caracteres). Example: Password123
     * @bodyParam telefono string required Número telefónico del repartidor. Example: +504 9876-5432
     *
     * @response 201 {
     *    "message": "Repartidor creado exitosamente",
     *    "repartidor": {
     *      "id": 3,
     *      "usuario_id": 4,
     *      "disponible": true,
     *      "created_at": "2025-04-02T19:00:00.000000Z",
     *      "updated_at": "2025-04-02T19:00:00.000000Z",
     *      "usuario": {
     *        "id": 4,
     *        "name": "Luis",
     *        "apellido": "Hernández",
     *        "email": "luis@ejemplo.com",
     *        "telefono": "+504 9876-5432",
     *        "rol": "repartidor",
     *        "fecha_registro": "2025-04-02T19:00:00.000000Z"
     *      }
     *    }
     * }
     *
     * @response 422 {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "email": ["El email ya ha sido registrado."]
     *    }
     * }
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'telefono' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'rol' => 'repartidor',
                'fecha_registro' => now(),
            ]);

            // Asignar rol
            $user->assignRole('repartidor');

            // Crear perfil de repartidor
            $repartidor = Repartidor::create([
                'usuario_id' => $user->id,
                'disponible' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Repartidor creado exitosamente',
                'repartidor' => $repartidor->load('usuario')
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al crear el repartidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un repartidor específico
     *
     * Obtiene los detalles de un repartidor específico.
     *
     * @urlParam id integer required ID del repartidor. Example: 1
     *
     * @response {
     *    "id": 1,
     *    "usuario_id": 2,
     *    "disponible": true,
     *    "ultima_ubicacion_lat": 14.55555,
     *    "ultima_ubicacion_lng": -87.55555,
     *    "ultima_actualizacion": "2025-04-02T18:30:00.000000Z",
     *    "created_at": "2025-04-01T11:00:00.000000Z",
     *    "updated_at": "2025-04-02T18:30:00.000000Z",
     *    "usuario": {
     *      "id": 2,
     *      "name": "Carlos Martínez",
     *      "apellido": "López",
     *      "email": "carlos@ejemplo.com",
     *      "telefono": "+504 8888-8888",
     *      "rol": "repartidor"
     *    }
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Repartidor] 99"
     * }
     *
     * @authenticated
     */
    public function show($id)
    {
        $repartidor = Repartidor::with('usuario')->findOrFail($id);
        return response()->json($repartidor);
    }

    /**
     * Actualizar un repartidor
     *
     * Actualiza la información de un repartidor existente.
     *
     * @urlParam id integer required ID del repartidor. Example: 1
     * @bodyParam disponible boolean Estado de disponibilidad del repartidor. Example: false
     * @bodyParam ultima_ubicacion_lat numeric Latitud de la última ubicación registrada. Example: 14.55565
     * @bodyParam ultima_ubicacion_lng numeric Longitud de la última ubicación registrada. Example: -87.55565
     *
     * @response {
     *    "message": "Repartidor actualizado exitosamente",
     *    "repartidor": {
     *      "id": 1,
     *      "usuario_id": 2,
     *      "disponible": false,
     *      "ultima_ubicacion_lat": 14.55565,
     *      "ultima_ubicacion_lng": -87.55565,
     *      "ultima_actualizacion": "2025-04-02T19:15:00.000000Z",
     *      "created_at": "2025-04-01T11:00:00.000000Z",
     *      "updated_at": "2025-04-02T19:15:00.000000Z"
     *    }
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Repartidor] 99"
     * }
     *
     * @authenticated
     */
    public function update(Request $request, $id)
    {
        $repartidor = Repartidor::findOrFail($id);

        $request->validate([
            'disponible' => 'boolean',
            'ultima_ubicacion_lat' => 'nullable|numeric',
            'ultima_ubicacion_lng' => 'nullable|numeric',
        ]);

        // Solo actualizar los campos del repartidor
        $repartidor->update($request->only([
            'disponible',
            'ultima_ubicacion_lat',
            'ultima_ubicacion_lng'
        ]));

        // Si se actualiza la ubicación, actualizar la fecha
        if ($request->has('ultima_ubicacion_lat') || $request->has('ultima_ubicacion_lng')) {
            $repartidor->ultima_actualizacion = now();
            $repartidor->save();
        }

        return response()->json([
            'message' => 'Repartidor actualizado exitosamente',
            'repartidor' => $repartidor->fresh()
        ]);
    }

    /**
     * Eliminar un repartidor
     *
     * Elimina un repartidor y cambia su rol a cliente.
     *
     * @urlParam id integer required ID del repartidor. Example: 3
     *
     * @response {
     *    "message": "Repartidor eliminado exitosamente"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Repartidor] 99"
     * }
     *
     * @authenticated
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $repartidor = Repartidor::findOrFail($id);
            $userId = $repartidor->usuario_id;

            // Eliminar repartidor
            $repartidor->delete();

            // Cambiar rol de usuario a cliente
            $user = User::findOrFail($userId);
            $user->removeRole('repartidor');
            $user->assignRole('cliente');
            $user->rol = 'cliente';
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Repartidor eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al eliminar el repartidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar ubicación del repartidor
     *
     * Actualiza la ubicación actual del repartidor autenticado.
     *
     * @bodyParam latitud numeric required Latitud de la ubicación actual. Example: 14.57575
     * @bodyParam longitud numeric required Longitud de la ubicación actual. Example: -87.57575
     *
     * @response {
     *    "message": "Ubicación actualizada exitosamente",
     *    "repartidor": {
     *      "id": 1,
     *      "ultima_ubicacion_lat": 14.57575,
     *      "ultima_ubicacion_lng": -87.57575,
     *      "ultima_actualizacion": "2025-04-02T19:30:00.000000Z",
     *      "updated_at": "2025-04-02T19:30:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para esta acción"
     * }
     *
     * @authenticated
     */
    public function actualizarUbicacion(Request $request)
    {
        $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        $user = $request->user();

        if (!$user->esRepartidor()) {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        $repartidor = $user->repartidor;
        $repartidor->ultima_ubicacion_lat = $request->latitud;
        $repartidor->ultima_ubicacion_lng = $request->longitud;
        $repartidor->ultima_actualizacion = now();
        $repartidor->save();

        return response()->json([
            'message' => 'Ubicación actualizada exitosamente',
            'repartidor' => $repartidor
        ]);
    }

    /**
     * Cambiar disponibilidad del repartidor
     *
     * Actualiza el estado de disponibilidad del repartidor autenticado.
     *
     * @bodyParam disponible boolean required Estado de disponibilidad (true=disponible, false=no disponible). Example: false
     *
     * @response {
     *    "message": "Disponibilidad actualizada exitosamente",
     *    "repartidor": {
     *      "id": 1,
     *      "disponible": false,
     *      "updated_at": "2025-04-02T19:45:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para esta acción"
     * }
     *
     * @authenticated
     */
    public function cambiarDisponibilidad(Request $request)
    {
        $request->validate([
            'disponible' => 'required|boolean',
        ]);

        $user = $request->user();

        if (!$user->esRepartidor()) {
            return response()->json([
                'message' => 'No tiene permiso para esta acción'
            ], 403);
        }

        $repartidor = $user->repartidor;
        $repartidor->disponible = $request->disponible;
        $repartidor->save();

        return response()->json([
            'message' => 'Disponibilidad actualizada exitosamente',
            'repartidor' => $repartidor
        ]);
    }
}
