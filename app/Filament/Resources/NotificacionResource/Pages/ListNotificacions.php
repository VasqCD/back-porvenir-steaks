<?php

namespace App\Filament\Resources\NotificacionResource\Pages;

use App\Filament\Resources\NotificacionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

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
                    $this->notify('success', 'Todas las notificaciones han sido marcadas como leídas');
                })
                ->requiresConfirmation(),
        ];
    }
}