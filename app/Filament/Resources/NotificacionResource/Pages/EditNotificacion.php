<?php

namespace App\Filament\Resources\NotificacionResource\Pages;

use App\Filament\Resources\NotificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditNotificacion extends EditRecord
{
    protected static string $resource = NotificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            Actions\Action::make('marcarLeida')
                ->label('Marcar como leída')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function () {
                    if (!$this->record->leida) {
                        $this->record->update(['leida' => true]);
                        
                        Notification::make()
                            ->title('Notificación actualizada')
                            ->body('La notificación ha sido marcada como leída')
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn() => !$this->record->leida),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}