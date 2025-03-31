<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
