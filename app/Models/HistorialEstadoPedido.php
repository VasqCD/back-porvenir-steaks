<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
