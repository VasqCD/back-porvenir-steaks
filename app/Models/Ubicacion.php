<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Ubicación
 *
 * Representa las direcciones de entrega registradas por los usuarios.
 *
 * @property int $id ID único de la ubicación
 * @property int $usuario_id ID del usuario al que pertenece
 * @property float $latitud Coordenada de latitud
 * @property float $longitud Coordenada de longitud
 * @property string $direccion_completa Dirección completa en formato de texto
 * @property string|null $calle Nombre de la calle
 * @property string|null $numero Número de casa o edificio
 * @property string|null $colonia Colonia o barrio
 * @property string|null $ciudad Ciudad
 * @property string|null $codigo_postal Código postal
 * @property string|null $referencias Referencias adicionales para facilitar la ubicación
 * @property string|null $etiqueta Etiqueta para identificar la ubicación (ej. Casa, Trabajo)
 * @property bool $es_principal Indica si es la dirección principal del usuario
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización
 * @property \Illuminate\Support\Carbon|null $deleted_at Fecha de eliminación (soft delete)
 *
 * @property-read \App\Models\User $usuario Usuario al que pertenece esta ubicación
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pedido[] $pedidos Pedidos que utilizan esta ubicación
 */
class Ubicacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ubicaciones';

    protected $fillable = [
        'usuario_id',
        'latitud',
        'longitud',
        'direccion_completa',
        'calle',
        'numero',
        'colonia',
        'ciudad',
        'codigo_postal',
        'referencias',
        'etiqueta',
        'es_principal',
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'es_principal' => 'boolean',
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
