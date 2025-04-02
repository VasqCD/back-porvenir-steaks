<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Notificación
 *
 * Representa las notificaciones enviadas a los usuarios.
 *
 * @property int $id ID único de la notificación
 * @property int $usuario_id ID del usuario destinatario
 * @property int|null $pedido_id ID del pedido relacionado (si aplica)
 * @property string $titulo Título de la notificación
 * @property string $mensaje Contenido o mensaje de la notificación
 * @property string $tipo Tipo de notificación (nuevo_pedido, pedido_en_cocina, pedido_en_camino, pedido_entregado)
 * @property bool $leida Indica si la notificación ha sido leída
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\User $usuario Usuario destinatario de esta notificación
 * @property-read \App\Models\Pedido|null $pedido Pedido relacionado con esta notificación
 */
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
