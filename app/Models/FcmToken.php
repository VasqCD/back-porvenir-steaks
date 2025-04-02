<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Token FCM
 *
 * Almacena los tokens de Firebase Cloud Messaging para enviar notificaciones push a los dispositivos.
 *
 * @property int $id ID único del token
 * @property int $usuario_id ID del usuario al que pertenece
 * @property string $token Token FCM único del dispositivo
 * @property string $device_type Tipo de dispositivo (android, ios, web)
 * @property bool $active Indica si el token está activo
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\User $usuario Usuario al que pertenece este token
 */
class FcmToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'token',
        'device_type',
        'active'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
