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
        $this->record->refresh();

        if ($this->record->rol === 'repartidor' && $this->record->repartidor) {
            $ubicacionUsada = $this->record->repartidor->ultima_ubicacion_lat ? 'con' : 'sin';

            Notification::make()
                ->title('Usuario convertido a repartidor')
                ->body("Se ha creado un perfil de repartidor para {$this->record->name} {$ubicacionUsada} coordenadas iniciales.")
                ->success()
                ->send();
        }
    }
}
