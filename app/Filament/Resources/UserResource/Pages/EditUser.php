<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Refrescar el modelo para asegurarnos de tener la información más actualizada
        $this->record->refresh();

        // Verificar si se cambió el rol del usuario
        if ($this->record->wasChanged('rol')) {
            $rolAnterior = $this->record->getOriginal('rol');
            $nuevoRol = $this->record->rol;

            // Mostrar notificación diferente según el cambio de rol
            if ($nuevoRol === 'repartidor') {
                $ubicacionMensaje = '';

                // Verificar si tiene ubicación
                if ($this->record->repartidor) {
                    $ubicacionMensaje = $this->record->repartidor->ultima_ubicacion_lat
                        ? ' Se han utilizado coordenadas de su ubicación principal.'
                        : ' No se encontraron coordenadas disponibles.';
                }

                Notification::make()
                    ->title('Usuario convertido a repartidor')
                    ->body("Se ha asignado el rol de repartidor a {$this->record->name}.{$ubicacionMensaje}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Rol de usuario actualizado')
                    ->body("El rol de {$this->record->name} ha cambiado de '{$rolAnterior}' a '{$nuevoRol}'.")
                    ->success()
                    ->send();
            }

            // También mostrar notificación sobre el envío de notificación push
            Notification::make()
                ->title('Notificación enviada')
                ->body("Se ha enviado una notificación a {$this->record->name} sobre el cambio de rol.")
                ->success()
                ->send();
        }
    }
}
