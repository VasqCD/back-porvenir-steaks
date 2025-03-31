<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repartidor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RepartidorController extends Controller
{
    /**
     * Mostrar todos los repartidores.
     */
    public function index()
    {
        $repartidores = Repartidor::with('usuario')->get();
        return response()->json($repartidores);
    }

    /**
     * Crear un nuevo repartidor.
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
     * Mostrar un repartidor específico.
     */
    public function show($id)
    {
        $repartidor = Repartidor::with('usuario')->findOrFail($id);
        return response()->json($repartidor);
    }

    /**
     * Actualizar un repartidor.
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
     * Eliminar un repartidor.
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
     * Actualizar la ubicación del repartidor.
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
     * Cambiar disponibilidad del repartidor.
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
