<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

/**
 * @group Gestión de Categorías
 *
 * APIs para administrar las categorías de productos
 */
class CategoriaController extends Controller
{
    /**
     * Listar todas las categorías
     *
     * Retorna un listado de todas las categorías disponibles con el conteo de productos en cada una.
     *
     * @response {
     *    "data": [
     *      {
     *        "id": 1,
     *        "nombre": "Carnes",
     *        "descripcion": "Cortes de carne premium",
     *        "created_at": "2025-04-01T10:00:00.000000Z",
     *        "updated_at": "2025-04-01T10:00:00.000000Z",
     *        "productos_count": 5
     *      },
     *      {
     *        "id": 2,
     *        "nombre": "Bebidas",
     *        "descripcion": "Refrescos y bebidas",
     *        "created_at": "2025-04-01T10:00:00.000000Z",
     *        "updated_at": "2025-04-01T10:00:00.000000Z",
     *        "productos_count": 8
     *      }
     *    ]
     * }
     */
    public function index()
    {
        $categorias = Categoria::withCount('productos')->get();
        return response()->json($categorias);
    }

    /**
     * Crear una nueva categoría
     *
     * Crea una nueva categoría de productos.
     *
     * @bodyParam nombre string required Nombre de la categoría (debe ser único). Example: Postres
     * @bodyParam descripcion string nullable Descripción de la categoría. Example: Variedad de postres y dulces
     *
     * @response 201 {
     *    "message": "Categoría creada exitosamente",
     *    "categoria": {
     *      "id": 3,
     *      "nombre": "Postres",
     *      "descripcion": "Variedad de postres y dulces",
     *      "created_at": "2025-04-02T14:30:00.000000Z",
     *      "updated_at": "2025-04-02T14:30:00.000000Z"
     *    }
     * }
     *
     * @response 422 {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "nombre": ["El nombre ya ha sido registrado."]
     *    }
     * }
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias',
            'descripcion' => 'nullable|string',
        ]);

        $categoria = Categoria::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'categoria' => $categoria
        ], 201);
    }

    /**
     * Mostrar una categoría específica
     *
     * Muestra los detalles de una categoría específica junto con sus productos.
     *
     * @urlParam id integer required ID de la categoría. Example: 1
     *
     * @response {
     *    "id": 1,
     *    "nombre": "Carnes",
     *    "descripcion": "Cortes de carne premium",
     *    "created_at": "2025-04-01T10:00:00.000000Z",
     *    "updated_at": "2025-04-01T10:00:00.000000Z",
     *    "productos": [
     *      {
     *        "id": 1,
     *        "nombre": "T-Bone Steak",
     *        "descripcion": "Corte premium de 16oz",
     *        "precio": 350.00,
     *        "imagen": "productos/tbone.jpg",
     *        "categoria_id": 1,
     *        "disponible": true,
     *        "created_at": "2025-04-01T10:30:00.000000Z",
     *        "updated_at": "2025-04-01T10:30:00.000000Z"
     *      },
     *      {
     *        "id": 2,
     *        "nombre": "Ribeye Steak",
     *        "descripcion": "Corte jugoso de 12oz",
     *        "precio": 280.00,
     *        "imagen": "productos/ribeye.jpg",
     *        "categoria_id": 1,
     *        "disponible": true,
     *        "created_at": "2025-04-01T10:35:00.000000Z",
     *        "updated_at": "2025-04-01T10:35:00.000000Z"
     *      }
     *    ]
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Categoria] 99"
     * }
     */
    public function show($id)
    {
        $categoria = Categoria::with('productos')->findOrFail($id);
        return response()->json($categoria);
    }

    /**
     * Actualizar una categoría
     *
     * Actualiza los datos de una categoría existente.
     *
     * @urlParam id integer required ID de la categoría. Example: 1
     * @bodyParam nombre string sometimes Nombre de la categoría (debe ser único). Example: Carnes Premium
     * @bodyParam descripcion string nullable Descripción de la categoría. Example: Selección de los mejores cortes importados
     *
     * @response {
     *    "message": "Categoría actualizada exitosamente",
     *    "categoria": {
     *      "id": 1,
     *      "nombre": "Carnes Premium",
     *      "descripcion": "Selección de los mejores cortes importados",
     *      "created_at": "2025-04-01T10:00:00.000000Z",
     *      "updated_at": "2025-04-02T15:45:00.000000Z"
     *    }
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Categoria] 99"
     * }
     *
     * @response 422 {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "nombre": ["El nombre ya ha sido registrado."]
     *    }
     * }
     *
     * @authenticated
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:255|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string',
        ]);

        $categoria->update([
            'nombre' => $request->nombre ?? $categoria->nombre,
            'descripcion' => $request->descripcion ?? $categoria->descripcion,
        ]);

        return response()->json([
            'message' => 'Categoría actualizada exitosamente',
            'categoria' => $categoria
        ]);
    }

    /**
     * Eliminar una categoría
     *
     * Elimina una categoría. Solo se pueden eliminar categorías sin productos asociados.
     *
     * @urlParam id integer required ID de la categoría. Example: 3
     *
     * @response {
     *    "message": "Categoría eliminada exitosamente"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Categoria] 99"
     * }
     *
     * @response 422 {
     *    "message": "No se puede eliminar la categoría porque tiene productos asociados"
     * }
     *
     * @authenticated
     */
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        // Verificar si tiene productos
        if ($categoria->productos()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados'
            ], 422);
        }

        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente'
        ]);
    }
}
