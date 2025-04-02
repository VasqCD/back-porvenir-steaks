<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Categoría
 *
 * Representa las categorías de los productos (ej. Carnes, Bebidas, Postres).
 *
 * @property int $id ID único de la categoría
 * @property string $nombre Nombre de la categoría
 * @property string|null $descripcion Descripción de la categoría
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Producto[] $productos Productos que pertenecen a esta categoría
 */
class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con productos
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
