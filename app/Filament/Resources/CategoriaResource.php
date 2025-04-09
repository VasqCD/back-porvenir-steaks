<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaResource\Pages;
use App\Filament\Resources\CategoriaResource\RelationManagers;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriaResource extends Resource
{
    protected static ?string $model = Categoria::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationLabel = 'Categorías';
    
    protected static ?string $navigationGroup = 'Catálogo';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información de la categoría')
                            ->description('Detalles básicos de la categoría de productos')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Nombre de la categoría')
                                    ->autofocus(),
                                    
                                Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->placeholder('Descripción detallada de la categoría')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-tag'),
                
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->searchable()
                    ->wrap(),
                    
                TextColumn::make('productos_count')
                    ->label('Productos')
                    ->counts('productos')
                    ->sortable()
                    ->icon('heroicon-o-shopping-bag')
                    ->color('success'),
                    
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->since(),
                    
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('deleted_at')
                    ->label('Eliminada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->tooltip('Ver detalles'),
                    
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->tooltip('Editar categoría'),
                    
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->tooltip('Eliminar categoría'),
                    
                Tables\Actions\RestoreAction::make()
                    ->icon('heroicon-o-arrow-path')
                    ->tooltip('Restaurar categoría'),
                    
                Tables\Actions\Action::make('verProductos')
                    ->label('Ver productos')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('success')
                    ->url(fn ($record) => route('filament.admin.resources.productos.index', ['tableFilters[categoria][value]' => $record->id]))
                    ->openUrlInNewTab()
                    ->tooltip('Ver todos los productos en esta categoría'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre', 'asc')
            ->emptyStateHeading('No hay categorías')
            ->emptyStateDescription('Crea una categoría para organizar tus productos')
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateActions([
                Tables\Actions\Action::make('crearCategoria')
                    ->label('Crear categoría')
                    ->url(route('filament.admin.resources.categorias.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorias::route('/'),
            'create' => Pages\CreateCategoria::route('/create'),
            'view' => Pages\ViewCategoria::route('/{record}'),
            'edit' => Pages\EditCategoria::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withCount('productos');
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre', 'descripcion'];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}