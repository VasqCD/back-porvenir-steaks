<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Producto
 *
 * Representa los productos o ítems disponibles para ordenar.
 *
 * @property int $id ID único del producto
 * @property string $nombre Nombre del producto
 * @property string|null $descripcion Descripción del producto
 * @property float $precio Precio unitario del producto
 * @property string|null $imagen Ruta a la imagen del producto
 * @property int $categoria_id ID de la categoría a la que pertenece
 * @property bool $disponible Indica si el producto está disponible para ordenar
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\Categoria $categoria Categoría a la que pertenece este producto
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DetallePedido[] $detallesPedido Detalles de pedidos que incluyen este producto
 */
class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'imagen',
        'categoria_id',
        'disponible',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'disponible' => 'boolean',
    ];

    // Relación con categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con detalles de pedido
    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class);
    }
}
