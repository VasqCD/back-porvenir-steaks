<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCategoria extends EditRecord
{
    protected static string $resource = CategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('heroicon-o-eye')
                ->tooltip('Ver detalles de la categoría'),
                
            Actions\Action::make('verProductos')
                ->label('Ver productos')
                ->icon('heroicon-o-shopping-bag')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.productos.index', ['tableFilters[categoria][value]' => $this->record->id]))
                ->openUrlInNewTab()
                ->tooltip('Ver todos los productos en esta categoría'),
                
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->tooltip('Eliminar esta categoría'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Categoría actualizada')
            ->body('La categoría ha sido actualizada correctamente.');
    }
}