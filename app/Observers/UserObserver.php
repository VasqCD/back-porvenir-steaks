<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Repartidor;
use App\Models\Ubicacion;
use App\Models\Notificacion;
use App\Services\FcmService;
use Illuminate\Support\Facades\App;

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
        // Si el campo 'rol' ha cambiado
        if ($user->isDirty('rol')) {
            // El rol anterior
            $rolAnterior = $user->getOriginal('rol');
            
            // El nuevo rol
            $nuevoRol = $user->rol;
            
            // Eliminar roles anteriores
            $user->syncRoles([$nuevoRol]);
            
            // Si cambió a repartidor, crear perfil de repartidor
            if ($nuevoRol === 'repartidor' && $rolAnterior !== 'repartidor') {
                $this->crearPerfilRepartidor($user);
                $this->notificarCambioRol($user, 'repartidor');
            }
            
            // Si era repartidor y ahora es otro rol, también notificar
            if ($rolAnterior === 'repartidor' && $nuevoRol !== 'repartidor') {
                $this->notificarCambioRol($user, $nuevoRol);
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
     * Notificar al usuario sobre el cambio de rol
     */
    private function notificarCambioRol(User $user, string $nuevoRol): void
    {
        // Preparar los mensajes según el rol
        $mensajes = [
            'repartidor' => [
                'titulo' => '¡Ahora eres repartidor!',
                'mensaje' => 'Tu cuenta ha sido actualizada con acceso de repartidor. Ya puedes empezar a entregar pedidos.',
                'tipo' => 'cambio_rol'
            ],
            'cliente' => [
                'titulo' => 'Cambio de rol en tu cuenta',
                'mensaje' => 'Tu rol ha sido cambiado a cliente. Ya no tienes acceso como repartidor.',
                'tipo' => 'cambio_rol'
            ],
            'administrador' => [
                'titulo' => '¡Ahora eres administrador!',
                'mensaje' => 'Tu cuenta ha sido actualizada con acceso de administrador.',
                'tipo' => 'cambio_rol'
            ]
        ];
        
        // Obtener el mensaje adecuado según el rol, o usar un mensaje por defecto
        $mensaje = $mensajes[$nuevoRol] ?? [
            'titulo' => 'Cambio de rol en tu cuenta',
            'mensaje' => "Tu rol ha sido cambiado a {$nuevoRol}.",
            'tipo' => 'cambio_rol'
        ];
        
        // Crear la notificación en la base de datos
        $notificacion = Notificacion::create([
            'usuario_id' => $user->id,
            'titulo' => $mensaje['titulo'],
            'mensaje' => $mensaje['mensaje'],
            'tipo' => $mensaje['tipo'],
            'leida' => false
        ]);
        
        // Enviar la notificación push usando FCM
        try {
            $fcmService = App::make(FcmService::class);
            $fcmService->sendNotification(
                $user,
                $mensaje['titulo'],
                $mensaje['mensaje'],
                [
                    'notification_id' => (string) $notificacion->id,
                    'type' => $mensaje['tipo']
                ]
            );
        } catch (\Exception $e) {
            // Registrar error pero no interrumpir el proceso
            \Illuminate\Support\Facades\Log::error('Error al enviar notificación FCM: ' . $e->getMessage());
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