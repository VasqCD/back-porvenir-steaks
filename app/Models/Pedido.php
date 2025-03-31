<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
