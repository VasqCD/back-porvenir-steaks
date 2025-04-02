<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UbicacionResource\Pages;
use App\Filament\Resources\UbicacionResource\RelationManagers;
use App\Models\Ubicacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UbicacionResource extends Resource
{
    protected static ?string $model = Ubicacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin'; // Cambiado para usar un icono más apropiado

    protected static ?string $navigationLabel = 'Ubicaciones'; // Añadido para tener un nombre más amigable en el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('usuario_id')
                            ->relationship('usuario', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('etiqueta')
                            ->placeholder('Casa, Trabajo, etc.')
                            ->maxLength(255),

                        Map::make('location')
                            ->autocomplete('direccion_completa')
                            ->autocompleteReverse(true)
                            ->defaultZoom(15)
                            ->draggable()
                            ->clickable()
                            ->columnSpanFull()
                            ->reactive()
                            ->mapControls([
                                'mapTypeControl' => true,
                                'scaleControl' => true,
                                'streetViewControl' => true,
                                'rotateControl' => false,
                                'fullscreenControl' => true,
                                'zoomControl' => true,
                            ])
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('latitud', $state['lat'] ?? null);
                                    $set('longitud', $state['lng'] ?? null);
                                }
                            })
                            ->afterStateHydrated(function ($component, $state, $record, callable $set) {
                                if ($record) {
                                    $lat = $record->latitud;
                                    $lng = $record->longitud;
                                    if ($lat && $lng) {
                                        $set('location', [
                                            'lat' => (float)$lat,
                                            'lng' => (float)$lng,
                                            'zoom' => 15,
                                        ]);
                                    }
                                }
                            }),

                        Hidden::make('latitud'),
                        Hidden::make('longitud'),

                        TextInput::make('direccion_completa')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('calle')
                            ->maxLength(255),

                        TextInput::make('numero')
                            ->maxLength(255),

                        TextInput::make('colonia')
                            ->maxLength(255),

                        TextInput::make('ciudad')
                            ->maxLength(255),

                        TextInput::make('codigo_postal')
                            ->maxLength(255),

                        Toggle::make('es_principal')
                            ->label('Dirección principal')
                            ->default(false)
                            ->required(),

                        Textarea::make('referencias')
                            ->placeholder('Referencias para encontrar el lugar')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usuario.name')
                ->searchable()
                    ->sortable()
                    ->label('Usuario'),

                Tables\Columns\TextColumn::make('direccion_completa')
                ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('etiqueta')
                    ->searchable(),

                Tables\Columns\TextColumn::make('ciudad')
                    ->searchable(),

                Tables\Columns\TextColumn::make('colonia')
                    ->searchable(),

                Tables\Columns\IconColumn::make('es_principal')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->label('Principal'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Añadido un filtro para buscar por usuario
                Tables\Filters\SelectFilter::make('usuario_id')
                    ->relationship('usuario', 'name')
                    ->label('Usuario'),

                // Añadido un filtro para mostrar solo ubicaciones principales
                Tables\Filters\Filter::make('es_principal')
                    ->query(fn (Builder $query): Builder => $query->where('es_principal', true))
                    ->label('Solo ubicaciones principales')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Añadido para poder ver los detalles completos
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Añadido para poder eliminar desde la acción directa
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
            'view' => Pages\ViewUbicacion::route('/{record}'), // Añadido para tener una página de vista detallada
            'edit' => Pages\EditUbicacion::route('/{record}/edit'),
        ];
    }
}
