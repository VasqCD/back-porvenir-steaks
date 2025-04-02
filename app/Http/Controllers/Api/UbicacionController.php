<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

/**
 * @group Gestión de Ubicaciones
 *
 * APIs para administrar direcciones de entrega de los usuarios
 */
class UbicacionController extends Controller
{
    /**
     * Listar ubicaciones del usuario
     *
     * Obtiene todas las ubicaciones registradas por el usuario autenticado.
     * Los administradores pueden ver todas las ubicaciones de todos los usuarios.
     *
     * @response {
     *    "data": [
     *      {
     *        "id": 1,
     *        "usuario_id": 1,
     *        "latitud": 14.12345,
     *        "longitud": -87.12345,
     *        "direccion_completa": "Calle Principal #123, Colonia Centro",
     *        "calle": "Calle Principal",
     *        "numero": "123",
     *        "colonia": "Centro",
     *        "ciudad": "Tegucigalpa",
     *        "codigo_postal": "11101",
     *        "referencias": "Edificio azul, segunda planta",
     *        "etiqueta": "Casa",
     *        "es_principal": true,
     *        "created_at": "2025-04-01T10:00:00.000000Z",
     *        "updated_at": "2025-04-01T10:00:00.000000Z"
     *      },
     *      {
     *        "id": 2,
     *        "usuario_id": 1,
     *        "latitud": 14.54321,
     *        "longitud": -87.54321,
     *        "direccion_completa": "Avenida La Paz, Edificio Corporativo",
     *        "calle": "Avenida La Paz",
     *        "numero": "45",
     *        "colonia": "Distrito Financiero",
     *        "ciudad": "Tegucigalpa",
     *        "codigo_postal": "11102",
     *        "referencias": "Edificio de cristal, piso 8",
     *        "etiqueta": "Trabajo",
     *        "es_principal": false,
     *        "created_at": "2025-04-01T10:15:00.000000Z",
     *        "updated_at": "2025-04-01T10:15:00.000000Z"
     *      }
     *    ]
     * }
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->rol === 'administrador') {
            $ubicaciones = Ubicacion::with('usuario')->get();
        } else {
            $ubicaciones = Ubicacion::where('usuario_id', $user->id)->get();
        }

        return response()->json($ubicaciones);
    }

    /**
     * Guardar una nueva ubicación
     *
     * Registra una nueva dirección de entrega para el usuario autenticado.
     *
     * @bodyParam latitud numeric required Latitud de la ubicación. Example: 14.09876
     * @bodyParam longitud numeric required Longitud de la ubicación. Example: -87.23456
     * @bodyParam direccion_completa string required Dirección completa. Example: Boulevard Morazán, Torre Morazán, Local 5
     * @bodyParam calle string nullable Nombre de la calle. Example: Boulevard Morazán
     * @bodyParam numero string nullable Número de casa/edificio. Example: 300
     * @bodyParam colonia string nullable Colonia o barrio. Example: Morazán
     * @bodyParam ciudad string nullable Ciudad. Example: Tegucigalpa
     * @bodyParam codigo_postal string nullable Código postal. Example: 11103
     * @bodyParam referencias string nullable Referencias adicionales. Example: Torre de oficinas, entrada principal
     * @bodyParam etiqueta string nullable Etiqueta para identificar la ubicación. Example: Oficina
     * @bodyParam es_principal boolean Indica si es la dirección principal. Example: false
     *
     * @response 201 {
     *    "message": "Ubicación guardada exitosamente",
     *    "ubicacion": {
     *      "id": 3,
     *      "usuario_id": 1,
     *      "latitud": 14.09876,
     *      "longitud": -87.23456,
     *      "direccion_completa": "Boulevard Morazán, Torre Morazán, Local 5",
     *      "calle": "Boulevard Morazán",
     *      "numero": "300",
     *      "colonia": "Morazán",
     *      "ciudad": "Tegucigalpa",
     *      "codigo_postal": "11103",
     *      "referencias": "Torre de oficinas, entrada principal",
     *      "etiqueta": "Oficina",
     *      "es_principal": false,
     *      "created_at": "2025-04-02T18:00:00.000000Z",
     *      "updated_at": "2025-04-02T18:00:00.000000Z"
     *    }
     * }
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'direccion_completa' => 'required|string',
            'calle' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:255',
            'colonia' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:255',
            'referencias' => 'nullable|string',
            'etiqueta' => 'nullable|string|max:255',
            'es_principal' => 'boolean',
        ]);

        $user = $request->user();

        $ubicacion = new Ubicacion($request->all());
        $ubicacion->usuario_id = $user->id;

        // Si es la ubicación principal, desmarcar las demás
        if ($request->es_principal) {
            Ubicacion::where('usuario_id', $user->id)
                ->update(['es_principal' => false]);
        }

        $ubicacion->save();

        return response()->json([
            'message' => 'Ubicación guardada exitosamente',
            'ubicacion' => $ubicacion
        ], 201);
    }

    /**
     * Mostrar una ubicación específica
     *
     * Obtiene los detalles de una ubicación específica.
     *
     * @urlParam id integer required ID de la ubicación. Example: 1
     *
     * @response {
     *    "id": 1,
     *    "usuario_id": 1,
     *    "latitud": 14.12345,
     *    "longitud": -87.12345,
     *    "direccion_completa": "Calle Principal #123, Colonia Centro",
     *    "calle": "Calle Principal",
     *    "numero": "123",
     *    "colonia": "Centro",
     *    "ciudad": "Tegucigalpa",
     *    "codigo_postal": "11101",
     *    "referencias": "Edificio azul, segunda planta",
     *    "etiqueta": "Casa",
     *    "es_principal": true,
     *    "created_at": "2025-04-01T10:00:00.000000Z",
     *    "updated_at": "2025-04-01T10:00:00.000000Z"
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para ver esta ubicación"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Ubicacion] 99"
     * }
     *
     * @authenticated
     */
    public function show($id)
    {
        $user = request()->user();
        $ubicacion = Ubicacion::findOrFail($id);

        // Verificar que la ubicación pertenezca al usuario o sea administrador
        if ($ubicacion->usuario_id !== $user->id && $user->rol !== 'administrador') {
            return response()->json([
                'message' => 'No tiene permiso para ver esta ubicación'
            ], 403);
        }

        return response()->json($ubicacion);
    }

    /**
     * Actualizar una ubicación
     *
     * Actualiza la información de una ubicación existente.
     *
     * @urlParam id integer required ID de la ubicación. Example: 1
     * @bodyParam latitud numeric sometimes Latitud de la ubicación. Example: 14.12355
     * @bodyParam longitud numeric sometimes Longitud de la ubicación. Example: -87.12365
     * @bodyParam direccion_completa string sometimes Dirección completa. Example: Calle Principal #124, Colonia Centro
     * @bodyParam calle string nullable Nombre de la calle. Example: Calle Principal
     * @bodyParam numero string nullable Número de casa/edificio. Example: 124
     * @bodyParam colonia string nullable Colonia o barrio. Example: Centro
     * @bodyParam ciudad string nullable Ciudad. Example: Tegucigalpa
     * @bodyParam codigo_postal string nullable Código postal. Example: 11101
     * @bodyParam referencias string nullable Referencias adicionales. Example: Edificio azul, apartamento 2B
     * @bodyParam etiqueta string nullable Etiqueta para identificar la ubicación. Example: Casa
     * @bodyParam es_principal boolean Indica si es la dirección principal. Example: true
     *
     * @response {
     *    "message": "Ubicación actualizada exitosamente",
     *    "ubicacion": {
     *      "id": 1,
     *      "usuario_id": 1,
     *      "latitud": 14.12355,
     *      "longitud": -87.12365,
     *      "direccion_completa": "Calle Principal #124, Colonia Centro",
     *      "calle": "Calle Principal",
     *      "numero": "124",
     *      "colonia": "Centro",
     *      "ciudad": "Tegucigalpa",
     *      "codigo_postal": "11101",
     *      "referencias": "Edificio azul, apartamento 2B",
     *      "etiqueta": "Casa",
     *      "es_principal": true,
     *      "created_at": "2025-04-01T10:00:00.000000Z",
     *      "updated_at": "2025-04-02T18:15:00.000000Z"
     *    }
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para actualizar esta ubicación"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Ubicacion] 99"
     * }
     *
     * @authenticated
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'latitud' => 'sometimes|numeric',
            'longitud' => 'sometimes|numeric',
            'direccion_completa' => 'sometimes|string',
            'calle' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:255',
            'colonia' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:255',
            'referencias' => 'nullable|string',
            'etiqueta' => 'nullable|string|max:255',
            'es_principal' => 'boolean',
        ]);

        $user = $request->user();
        $ubicacion = Ubicacion::findOrFail($id);

        // Verificar que la ubicación pertenezca al usuario
        if ($ubicacion->usuario_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene permiso para actualizar esta ubicación'
            ], 403);
        }

        $ubicacion->fill($request->except('usuario_id'));

        // Si es la ubicación principal, desmarcar las demás
        if ($request->input('es_principal', false)) {
            Ubicacion::where('usuario_id', $user->id)
                ->where('id', '!=', $id)
                ->update(['es_principal' => false]);
        }

        $ubicacion->save();

        return response()->json([
            'message' => 'Ubicación actualizada exitosamente',
            'ubicacion' => $ubicacion
        ]);
    }

    /**
     * Eliminar una ubicación
     *
     * Elimina una ubicación registrada por el usuario.
     *
     * @urlParam id integer required ID de la ubicación. Example: 3
     *
     * @response {
     *    "message": "Ubicación eliminada exitosamente"
     * }
     *
     * @response 403 {
     *    "message": "No tiene permiso para eliminar esta ubicación"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Ubicacion] 99"
     * }
     *
     * @authenticated
     */
    public function destroy($id)
    {
        $user = request()->user();
        $ubicacion = Ubicacion::findOrFail($id);

        // Verificar que la ubicación pertenezca al usuario
        if ($ubicacion->usuario_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene permiso para eliminar esta ubicación'
            ], 403);
        }

        $ubicacion->delete();

        return response()->json([
            'message' => 'Ubicación eliminada exitosamente'
        ]);
    }
}
