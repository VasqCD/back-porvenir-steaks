<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    /**
     * Mostrar todas las ubicaciones del usuario autenticado.
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
     * Guardar una nueva ubicación.
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
     * Mostrar una ubicación específica.
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
     * Actualizar una ubicación.
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
     * Eliminar una ubicación.
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
