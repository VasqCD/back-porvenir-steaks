<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modelo de Usuario
 *
 * Representa a los usuarios del sistema incluyendo clientes, repartidores y administradores.
 *
 * @property int $id ID único del usuario
 * @property string $name Nombre del usuario
 * @property string|null $apellido Apellido del usuario
 * @property string $email Email único del usuario (utilizado para autenticación)
 * @property \Illuminate\Support\Carbon|null $email_verified_at Fecha de verificación de email
 * @property string $password Contraseña encriptada
 * @property string|null $telefono Número telefónico del usuario
 * @property string $rol Rol del usuario (cliente, repartidor, administrador)
 * @property string|null $codigo_verificacion Código temporal para verificaciones y recuperación de cuenta
 * @property string|null $foto_perfil Ruta a la imagen de perfil
 * @property \Illuminate\Support\Carbon $fecha_registro Fecha de registro del usuario
 * @property \Illuminate\Support\Carbon|null $ultima_conexion Última fecha de conexión
 * @property string|null $remember_token Token para recordar sesión
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pedido[] $pedidos Pedidos realizados por el usuario
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ubicacion[] $ubicaciones Ubicaciones registradas por el usuario
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Notificacion[] $notificaciones Notificaciones del usuario
 * @property-read \App\Models\Repartidor|null $repartidor Perfil de repartidor asociado (si el rol es 'repartidor')
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FcmToken[] $fcmTokens Tokens FCM para notificaciones push
 *
 * @method bool esRepartidor() Verifica si el usuario tiene rol de repartidor
 * @method bool esAdministrador() Verifica si el usuario tiene rol de administrador
 * @method bool esCliente() Verifica si el usuario tiene rol de cliente
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'apellido',
        'email',
        'password',
        'telefono',
        'rol',
        'codigo_verificacion',
        'foto_perfil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_registro' => 'datetime',
            'ultima_conexion' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Relación con pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    // Relación con ubicaciones
    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class, 'usuario_id');
    }

    // Relación con notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    // Relación con el perfil de repartidor
    public function repartidor()
    {
        return $this->hasOne(Repartidor::class, 'usuario_id');
    }

    // Verificar si es repartidor
    public function esRepartidor()
    {
        return $this->rol === 'repartidor';
    }

    // Verificar si es administrador
    public function esAdministrador()
    {
        return $this->rol === 'administrador';
    }

    // Verificar si es cliente
    public function esCliente()
    {
        return $this->rol === 'cliente';
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class, 'usuario_id');
    }
}
