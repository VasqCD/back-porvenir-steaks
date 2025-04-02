<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Historial de Estado de Pedido
 *
 * Registra los cambios de estado de un pedido para mantener un historial completo.
 *
 * @property int $id ID único del registro
 * @property int $pedido_id ID del pedido al que pertenece
 * @property string|null $estado_anterior Estado anterior del pedido
 * @property string $estado_nuevo Nuevo estado del pedido
 * @property \Illuminate\Support\Carbon $fecha_cambio Fecha y hora del cambio de estado
 * @property int $usuario_id ID del usuario que realizó el cambio
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\Pedido $pedido Pedido al que pertenece este registro
 * @property-read \App\Models\User $usuario Usuario que realizó el cambio de estado
 */
class HistorialEstadoPedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historial_estados_pedido';

    protected $fillable = [
        'pedido_id',
        'estado_anterior',
        'estado_nuevo',
        'fecha_cambio',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    // Relación con pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // Relación con usuario que realizó el cambio
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
