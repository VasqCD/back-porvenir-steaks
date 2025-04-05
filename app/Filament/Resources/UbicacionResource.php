<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UbicacionResource\Pages;
use App\Filament\Resources\UbicacionResource\RelationManagers;
use App\Models\Ubicacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UbicacionResource extends Resource
{
    protected static ?string $model = Ubicacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Ubicaciones';
    
    protected static ?string $navigationGroup = 'Gestión';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $recordTitleAttribute = 'direccion_completa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información general')
                            ->schema([
                                Select::make('usuario_id')
                                    ->relationship('usuario', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('etiqueta')
                                    ->label('Etiqueta (opcional)')
                                    ->placeholder('Casa, Trabajo, etc.')
                                    ->maxLength(255),
                                
                                Toggle::make('es_principal')
                                    ->label('Dirección principal')
                                    ->helperText('Si se marca, esta será la dirección predeterminada del usuario')
                                    ->default(false)
                                    ->required(),
                            ])
                            ->columns(3),
                    
                        Section::make('Ubicación en el mapa')
                            ->schema([
                                Map::make('location')
                                    ->autocomplete('direccion_completa')
                                    ->autocompleteReverse(true)
                                    ->defaultZoom(15)
                                    ->draggable()
                                    ->clickable()
                                    ->height('400px')
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
                            ]),
                        
                        Section::make('Detalles de la dirección')
                            ->schema([
                                TextInput::make('direccion_completa')
                                    ->required()
                                    ->columnSpanFull()
                                    ->label('Dirección completa')
                                    ->placeholder('Ej: Calle Principal, #123, Colonia Centro'),

                                Grid::make()
                                    ->schema([
                                        TextInput::make('calle')
                                            ->maxLength(255),

                                        TextInput::make('numero')
                                            ->label('Número')
                                            ->maxLength(255),

                                        TextInput::make('colonia')
                                            ->maxLength(255),

                                        TextInput::make('ciudad')
                                            ->maxLength(255),

                                        TextInput::make('codigo_postal')
                                            ->label('Código postal')
                                            ->maxLength(255),
                                    ])
                                    ->columns(3),

                                Textarea::make('referencias')
                                    ->placeholder('Referencias para encontrar el lugar (opcional)')
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
                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('usuario')
                    ->relationship('usuario', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Usuario'),

                TernaryFilter::make('es_principal')
                    ->label('Direcciones principales')
                    ->placeholder('Todas las direcciones')
                    ->trueLabel('Solo direcciones principales')
                    ->falseLabel('Solo direcciones secundarias'),
                    
                SelectFilter::make('ciudad')
                    ->options(function () {
                        return Ubicacion::whereNotNull('ciudad')
                            ->distinct()
                            ->pluck('ciudad', 'ciudad')
                            ->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('ver_mapa')
                    ->label('Ver en mapa')
                    ->icon('heroicon-o-map')
                    ->color('success')
                    ->url(fn (Ubicacion $record): string => "https://www.google.com/maps/search/?api=1&query={$record->latitud},{$record->longitud}")
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PedidosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUbicacions::route('/'),
            'create' => Pages\CreateUbicacion::route('/create'),
            'view' => Pages\ViewUbicacion::route('/{record}'),
            'edit' => Pages\EditUbicacion::route('/{record}/edit'),
        ];
    }
}