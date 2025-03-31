<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UbicacionResource\Pages;
use App\Filament\Resources\UbicacionResource\RelationManagers;
use App\Models\Ubicacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UbicacionResource extends Resource
{
    protected static ?string $model = Ubicacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('usuario_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('latitud')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('longitud')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('direccion_completa')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('calle')
                    ->maxLength(255),
                Forms\Components\TextInput::make('numero')
                    ->maxLength(255),
                Forms\Components\TextInput::make('colonia')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ciudad')
                    ->maxLength(255),
                Forms\Components\TextInput::make('codigo_postal')
                    ->maxLength(255),
                Forms\Components\Textarea::make('referencias')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('etiqueta')
                    ->maxLength(255),
                Forms\Components\Toggle::make('es_principal')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usuario_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('latitud')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitud')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('calle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('numero')
                    ->searchable(),
                Tables\Columns\TextColumn::make('colonia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ciudad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo_postal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('etiqueta')
                    ->searchable(),
                Tables\Columns\IconColumn::make('es_principal')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUbicacions::route('/'),
            'create' => Pages\CreateUbicacion::route('/create'),
            'edit' => Pages\EditUbicacion::route('/{record}/edit'),
        ];
    }
}
