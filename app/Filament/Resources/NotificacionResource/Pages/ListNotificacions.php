<?php

namespace App\Filament\Resources\NotificacionResource\Pages;

use App\Filament\Resources\NotificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListNotificacions extends ListRecords
{
    protected static string $resource = NotificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('marcarTodasLeidas')
                ->label('Marcar todas como leídas')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function () {
                    \App\Models\Notificacion::where('leida', false)->update(['leida' => true]);
                    
                    // Usar el método correcto para mostrar notificaciones en Filament
                    Notification::make()
                        ->title('Notificaciones actualizadas')
                        ->body('Todas las notificaciones han sido marcadas como leídas')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),
        ];
    }
}