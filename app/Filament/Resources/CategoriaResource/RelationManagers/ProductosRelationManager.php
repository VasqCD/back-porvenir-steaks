<?php

namespace App\Filament\Resources\CategoriaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductosRelationManager extends RelationManager
{
    protected static string $relationship = 'productos';

    protected static ?string $recordTitleAttribute = 'nombre';
    
    protected static ?string $title = 'Productos en esta categoría';
    
    protected static ?string $inverseRelationship = 'categoria';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('precio')
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                    
                Forms\Components\Textarea::make('descripcion')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-shopping-bag'),
                    
                Tables\Columns\TextColumn::make('precio')
                    ->money('HNL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('descripcion')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): mixed {
                        $data['categoria_id'] = $livewire->ownerRecord->id;
                        
                        return $livewire->getRelationship()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay productos en esta categoría')
            ->emptyStateDescription('Empieza a agregar productos a esta categoría')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }
}