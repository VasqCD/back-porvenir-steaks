<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Repartidor;
use App\Models\Ubicacion;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Asegurarse de que el usuario tenga el rol correspondiente a su campo 'rol'
        if (!$user->hasRole($user->rol)) {
            $user->assignRole($user->rol);
        }
        
        // Si el usuario es un repartidor, crear registro en la tabla repartidores
        if ($user->rol === 'repartidor') {
            $this->crearPerfilRepartidor($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Si el campo 'rol' ha cambiado, actualizar roles
        if ($user->isDirty('rol')) {
            // Eliminar roles anteriores
            $user->syncRoles([$user->rol]);
            
            // Si cambió a repartidor, crear perfil de repartidor
            if ($user->rol === 'repartidor' && !$user->repartidor) {
                $this->crearPerfilRepartidor($user);
            }
        }
    }

    /**
     * Crear perfil de repartidor para el usuario
     */
    private function crearPerfilRepartidor(User $user): void
    {
        // Verificar si ya existe un perfil de repartidor
        if ($user->repartidor) {
            return;
        }
        
        // Buscar si el usuario tiene ubicaciones registradas
        $ubicacion = Ubicacion::where('usuario_id', $user->id)
            ->where('es_principal', true)
            ->first();
            
        // Si no hay ubicación principal, buscar cualquier ubicación
        if (!$ubicacion) {
            $ubicacion = Ubicacion::where('usuario_id', $user->id)->first();
        }
        
        // Crear el perfil de repartidor
        $repartidor = new Repartidor();
        $repartidor->usuario_id = $user->id;
        $repartidor->disponible = true;
        
        // Si hay ubicación, usar sus coordenadas
        if ($ubicacion) {
            $repartidor->ultima_ubicacion_lat = $ubicacion->latitud;
            $repartidor->ultima_ubicacion_lng = $ubicacion->longitud;
            $repartidor->ultima_actualizacion = now();
        }
        
        $repartidor->save();
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}