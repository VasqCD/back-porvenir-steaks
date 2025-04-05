<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepartidorResource\Pages;
use App\Filament\Resources\RepartidorResource\RelationManagers;
use App\Models\Repartidor;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Illuminate\Support\Collection;

class RepartidorResource extends Resource
{
    protected static ?string $model = Repartidor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationLabel = 'Repartidores';
    
    protected static ?string $navigationGroup = 'Gestión';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información del repartidor')
                            ->schema([
                                Select::make('usuario_id')
                                    ->label('Usuario')
                                    ->relationship('usuario', 'name', function ($query) {
                                        return $query->where('rol', 'repartidor');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('apellido')
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->unique(User::class, 'email')
                                            ->maxLength(255),
                                        TextInput::make('password')
                                            ->password()
                                            ->required()
                                            ->minLength(8)
                                            ->maxLength(255),
                                        TextInput::make('telefono')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('rol')
                                            ->default('repartidor')
                                            ->required()
                                            ->hidden()
                                    ])
                                    ->required(),

                                Toggle::make('disponible')
                                    ->label('Disponible para entregas')
                                    ->helperText('Indica si el repartidor está actualmente en servicio')
                                    ->default(true)
                                    ->required(),
                            ])
                            ->columns(2),
                            
                        Section::make('Última ubicación conocida')
                            ->schema([
                                Map::make('location')
                                    ->defaultLocation([14.0723, -87.1921]) // Honduras
                                    ->defaultZoom(13)
                                    ->draggable()
                                    ->clickable()
                                    ->height('400px')
                                    ->columnSpanFull()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('ultima_ubicacion_lat', $state['lat'] ?? null);
                                            $set('ultima_ubicacion_lng', $state['lng'] ?? null);
                                            $set('ultima_actualizacion', now());
                                        }
                                    })
                                    ->afterStateHydrated(function ($component, $state, $record, callable $set) {
                                        if ($record) {
                                            $lat = $record->ultima_ubicacion_lat;
                                            $lng = $record->ultima_ubicacion_lng;
                                            if ($lat && $lng) {
                                                $set('location', [
                                                    'lat' => (float)$lat,
                                                    'lng' => (float)$lng,
                                                    'zoom' => 15,
                                                ]);
                                            }
                                        }
                                    }),
                                    
                                TextInput::make('ultima_ubicacion_lat')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->placeholder('Ej. 14.0723')
                                    ->disabled(),
                                    
                                TextInput::make('ultima_ubicacion_lng')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->placeholder('Ej. -87.1921')
                                    ->disabled(),
                                    
                                DateTimePicker::make('ultima_actualizacion')
                                    ->label('Última actualización')
                                    ->displayFormat('d/m/Y H:i:s')
                                    ->disabled(),
                            ])
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('usuario.foto_perfil')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->usuario->name ?? 'R') . '&color=FFFFFF&background=6366F1'),
                
                TextColumn::make('usuario.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('usuario.telefono')
                    ->label('Teléfono')
                    ->searchable(),
                
                IconColumn::make('disponible')
                    ->boolean()
                    ->label('Disponible')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                TextColumn::make('ultima_actualizacion')
                    ->label('Última actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin actualizar'),
                    
                TextColumn::make('pedidos_count')
                    ->label('Pedidos entregados')
                    ->counts('pedidos')
                    ->sortable(),
                    
                TextColumn::make('pedidos_activos_count')
                    ->label('Pedidos activos')
                    ->counts('pedidosActivos')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('disponible')
                    ->label('Disponibilidad')
                    ->placeholder('Todos los repartidores')
                    ->trueLabel('Solo disponibles')
                    ->falseLabel('Solo no disponibles'),
                    
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('ver_ubicacion')
                    ->label('Ver ubicación')
                    ->icon('heroicon-o-map')
                    ->color('success')
                    ->visible(fn ($record) => $record->ultima_ubicacion_lat && $record->ultima_ubicacion_lng)
                    ->url(fn (Repartidor $record): string => "https://www.google.com/maps/search/?api=1&query={$record->ultima_ubicacion_lat},{$record->ultima_ubicacion_lng}")
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('cambiar_disponibilidad')
                    ->label(fn ($record) => $record->disponible ? 'Marcar no disponible' : 'Marcar disponible')
                    ->icon(fn ($record) => $record->disponible ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->disponible ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Repartidor $record) {
                        $record->update(['disponible' => !$record->disponible]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\RestoreBulkAction::make(),
                    
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('cambiar_disponibilidad_masiva')
                        ->label('Cambiar disponibilidad')
                        ->icon('heroicon-o-arrow-path')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['disponible' => $data['disponible']]);
                            }
                        })
                        ->form([
                            Forms\Components\Toggle::make('disponible')
                                ->label('Disponible')
                                ->default(true)
                                ->required(),
                        ]),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withCount(['pedidos', 'pedidosActivos']);
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
            'index' => Pages\ListRepartidors::route('/'),
            'create' => Pages\CreateRepartidor::route('/create'),
            'edit' => Pages\EditRepartidor::route('/{record}/edit'),
        ];
    }
}