<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Detalle de Pedido
 *
 * Representa cada ítem o producto incluido en un pedido.
 *
 * @property int $id ID único del detalle
 * @property int $pedido_id ID del pedido al que pertenece
 * @property int $producto_id ID del producto ordenado
 * @property int $cantidad Cantidad solicitada
 * @property float $precio_unitario Precio unitario al momento de la compra
 * @property float $subtotal Subtotal (precio_unitario * cantidad)
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\Pedido $pedido Pedido al que pertenece este detalle
 * @property-read \App\Models\Producto $producto Producto ordenado
 */
class DetallePedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'detalle_pedidos';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relación con pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // Relación con producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
