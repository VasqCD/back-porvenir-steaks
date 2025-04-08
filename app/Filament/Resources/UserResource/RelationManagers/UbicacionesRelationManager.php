<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UbicacionesRelationManager extends RelationManager
{
    protected static string $relationship = 'ubicaciones';

    protected static ?string $recordTitleAttribute = 'direccion_completa';
    
    protected static ?string $title = 'Direcciones registradas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('direccion_completa')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('etiqueta')
                    ->searchable()
                    ->placeholder('Sin etiqueta'),

                TextColumn::make('direccion_completa')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),

                TextColumn::make('ciudad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('colonia')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('es_principal')
                    ->boolean()
                    ->label('Principal')
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('ver_mapa')
                    ->label('Ver en mapa')
                    ->icon('heroicon-o-map')
                    ->color('success')
                    ->url(fn ($record): string => "https://www.google.com/maps/search/?api=1&query={$record->latitud},{$record->longitud}")
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}