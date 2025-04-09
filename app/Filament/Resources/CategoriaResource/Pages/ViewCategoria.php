<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoria extends ViewRecord
{
    protected static string $resource = CategoriaResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil')
                ->tooltip('Editar esta categoría'),
                
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
}