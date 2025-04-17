<?php

namespace App\Filament\Resources\NotificacionResource\Pages;

use App\Filament\Resources\NotificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewNotificacion extends ViewRecord
{
    protected static string $resource = NotificacionResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
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
            
            Actions\Action::make('verRelacionado')
                ->label('Ver recurso relacionado')
                ->icon('heroicon-o-link')
                ->color('primary')
                ->url(function () {
                    if ($this->record->pedido_id) {
                        return route('filament.admin.resources.pedidos.edit', ['record' => $this->record->pedido_id]);
                    } elseif ($this->record->tipo === 'solicitud_repartidor') {
                        return route('filament.admin.resources.users.index', ['tableFilters[rol][value]' => 'cliente']);
                    }
                    
                    return null;
                })
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->pedido_id !== null || $this->record->tipo === 'solicitud_repartidor'),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!$this->record->leida) {
            // Marcar como leída automáticamente cuando se ve
            $this->record->update(['leida' => true]);
        }
        
        return $data;
    }
}