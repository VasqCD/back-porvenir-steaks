<?php

namespace App\Observers;

use App\Models\User;

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
        }
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
