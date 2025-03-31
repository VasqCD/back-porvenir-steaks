<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
}
