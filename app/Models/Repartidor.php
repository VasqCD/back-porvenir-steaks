<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Repartidor
 *
 * Representa el perfil de repartidor asociado a un usuario con rol 'repartidor'.
 *
 * @property int $id ID único del repartidor
 * @property int $usuario_id ID del usuario asociado
 * @property bool $disponible Indica si el repartidor está disponible para entregas
 * @property float|null $ultima_ubicacion_lat Latitud de la última ubicación registrada
 * @property float|null $ultima_ubicacion_lng Longitud de la última ubicación registrada
 * @property \Illuminate\Support\Carbon|null $ultima_actualizacion Fecha y hora de la última actualización de ubicación
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\User $usuario Usuario asociado a este perfil de repartidor
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pedido[] $pedidos Pedidos asignados a este repartidor
 */
class Repartidor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'repartidores';

    protected $fillable = [
        'usuario_id',
        'disponible',
        'ultima_ubicacion_lat',
        'ultima_ubicacion_lng',
        'ultima_actualizacion',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'ultima_ubicacion_lat' => 'decimal:8',
        'ultima_ubicacion_lng' => 'decimal:8',
        'ultima_actualizacion' => 'datetime',
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * Relación con pedidos activos (pendientes, en cocina o en camino)
     */
    public function pedidosActivos()
    {
        return $this->hasMany(Pedido::class)->whereIn('estado', ['pendiente', 'en_cocina', 'en_camino']);
    }
}
