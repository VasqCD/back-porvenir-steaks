<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notificacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_id',
        'pedido_id',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
