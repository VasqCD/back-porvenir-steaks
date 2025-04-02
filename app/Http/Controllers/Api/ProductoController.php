<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Gestión de Productos
 *
 * APIs para administrar el catálogo de productos
 */
class ProductoController extends Controller
{
    /**
     * Listar productos
     *
     * Obtiene un listado de productos disponibles. Se pueden aplicar filtros por categoría y nombre.
     *
     * @queryParam categoria_id integer Filtrar productos por ID de categoría. Example: 1
     * @queryParam nombre string Filtrar productos por nombre (búsqueda parcial). Example: steak
     *
     * @response {
     *    "data": [
     *      {
     *        "id": 1,
     *        "nombre": "T-Bone Steak",
     *        "descripcion": "Corte premium de 16oz",
     *        "precio": 350.00,
     *        "imagen": "productos/tbone.jpg",
     *        "categoria_id": 1,
     *        "disponible": true,
     *        "created_at": "2025-04-01T10:30:00.000000Z",
     *        "updated_at": "2025-04-01T10:30:00.000000Z",
     *        "categoria": {
     *          "id": 1,
     *          "nombre": "Carnes"
     *        }
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
     *        "updated_at": "2025-04-01T10:35:00.000000Z",
     *        "categoria": {
     *          "id": 1,
     *          "nombre": "Carnes"
     *        }
     *      }
     *    ]
     * }
     */
    public function index(Request $request)
    {
        $query = Producto::with('categoria')->where('disponible', true);

        // Filtrar por categoría si se proporciona
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtrar por nombre si se proporciona
        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        $productos = $query->get();

        return response()->json($productos);
    }

    /**
     * Crear un nuevo producto
     *
     * Crea un nuevo producto en el catálogo.
     *
     * @bodyParam nombre string required Nombre del producto. Example: New York Steak
     * @bodyParam descripcion string nullable Descripción del producto. Example: Corte fino de 10oz
     * @bodyParam precio numeric required Precio del producto (mayor a 0). Example: 260.00
     * @bodyParam categoria_id integer required ID de la categoría a la que pertenece. Example: 1
     * @bodyParam imagen file nullable Imagen del producto (jpeg, png, jpg - máx: 2MB).
     * @bodyParam disponible boolean nullable Indica si el producto está disponible para la venta. Example: true
     *
     * @response 201 {
     *    "message": "Producto creado exitosamente",
     *    "producto": {
     *      "id": 3,
     *      "nombre": "New York Steak",
     *      "descripcion": "Corte fino de 10oz",
     *      "precio": 260.00,
     *      "imagen": "productos/newyork.jpg",
     *      "categoria_id": 1,
     *      "disponible": true,
     *      "created_at": "2025-04-02T16:00:00.000000Z",
     *      "updated_at": "2025-04-02T16:00:00.000000Z"
     *    }
     * }
     *
     * @response 422 {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "precio": ["El precio debe ser mayor a 0."]
     *    }
     * }
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'disponible' => 'nullable|boolean',
        ]);

        $producto = new Producto($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            $producto->imagen = $path;
        }

        $producto->save();

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'producto' => $producto
        ], 201);
    }

    /**
     * Mostrar un producto específico
     *
     * Obtiene los detalles de un producto específico.
     *
     * @urlParam id integer required ID del producto. Example: 1
     *
     * @response {
     *    "id": 1,
     *    "nombre": "T-Bone Steak",
     *    "descripcion": "Corte premium de 16oz",
     *    "precio": 350.00,
     *    "imagen": "productos/tbone.jpg",
     *    "categoria_id": 1,
     *    "disponible": true,
     *    "created_at": "2025-04-01T10:30:00.000000Z",
     *    "updated_at": "2025-04-01T10:30:00.000000Z",
     *    "categoria": {
     *      "id": 1,
     *      "nombre": "Carnes",
     *      "descripcion": "Cortes de carne premium"
     *    }
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Producto] 99"
     * }
     */
    public function show($id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);

        return response()->json($producto);
    }

    /**
     * Actualizar un producto
     *
     * Actualiza la información de un producto existente.
     *
     * @urlParam id integer required ID del producto. Example: 1
     * @bodyParam nombre string sometimes Nombre del producto. Example: T-Bone Steak Premium
     * @bodyParam descripcion string nullable Descripción del producto. Example: Corte premium de 16oz, importado USDA Choice
     * @bodyParam precio numeric sometimes Precio del producto (mayor a 0). Example: 375.00
     * @bodyParam categoria_id integer sometimes ID de la categoría a la que pertenece. Example: 1
     * @bodyParam imagen file nullable Nueva imagen del producto (jpeg, png, jpg - máx: 2MB).
     * @bodyParam disponible boolean nullable Indica si el producto está disponible para la venta. Example: true
     *
     * @response {
     *    "message": "Producto actualizado exitosamente",
     *    "producto": {
     *      "id": 1,
     *      "nombre": "T-Bone Steak Premium",
     *      "descripcion": "Corte premium de 16oz, importado USDA Choice",
     *      "precio": 375.00,
     *      "imagen": "productos/tbone_premium.jpg",
     *      "categoria_id": 1,
     *      "disponible": true,
     *      "created_at": "2025-04-01T10:30:00.000000Z",
     *      "updated_at": "2025-04-02T16:30:00.000000Z"
     *    }
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Producto] 99"
     * }
     *
     * @authenticated
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            'categoria_id' => 'sometimes|exists:categorias,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'disponible' => 'nullable|boolean',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->fill($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }

            $path = $request->file('imagen')->store('productos', 'public');
            $producto->imagen = $path;
        }

        $producto->save();

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'producto' => $producto
        ]);
    }

    /**
     * Eliminar un producto
     *
     * Elimina un producto del catálogo (soft delete).
     *
     * @urlParam id integer required ID del producto. Example: 3
     *
     * @response {
     *    "message": "Producto eliminado exitosamente"
     * }
     *
     * @response 404 {
     *    "message": "No query results for model [App\\Models\\Producto] 99"
     * }
     *
     * @authenticated
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        // Soft delete
        $producto->delete();

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ]);
    }
}
