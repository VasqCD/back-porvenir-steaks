<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Pedido
 *
 * Representa los pedidos realizados por los clientes.
 *
 * @property int $id ID único del pedido
 * @property int $usuario_id ID del usuario que realizó el pedido
 * @property int $ubicacion_id ID de la ubicación de entrega
 * @property string $estado Estado actual del pedido (pendiente, en_cocina, en_camino, entregado, cancelado)
 * @property float $total Monto total del pedido
 * @property \Illuminate\Support\Carbon $fecha_pedido Fecha y hora en que se realizó el pedido
 * @property \Illuminate\Support\Carbon|null $fecha_entrega Fecha y hora de entrega (si está entregado)
 * @property int|null $repartidor_id ID del repartidor asignado (si hay uno)
 * @property int|null $calificacion Calificación del cliente (1-5 estrellas)
 * @property string|null $comentario_calificacion Comentario sobre la calificación
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\User $usuario Usuario que realizó el pedido
 * @property-read \App\Models\Ubicacion $ubicacion Ubicación de entrega
 * @property-read \App\Models\Repartidor|null $repartidor Repartidor asignado al pedido
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DetallePedido[] $detalles Detalles/items del pedido
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Notificacion[] $notificaciones Notificaciones relacionadas con este pedido
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\HistorialEstadoPedido[] $historialEstados Historial de cambios de estado
 */
class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'ubicacion_id',
        'estado',
        'total',
        'fecha_pedido',
        'fecha_entrega',
        'repartidor_id',
        'calificacion',
        'comentario_calificacion',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha_pedido' => 'datetime',
        'fecha_entrega' => 'datetime',
        'calificacion' => 'integer',
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con ubicación
    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    // Relación con repartidor
    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class);
    }

    // Relación con detalle de pedido
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    // Relación con notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    // Relación con historial de estados
    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoPedido::class);
    }
}
