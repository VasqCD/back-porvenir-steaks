<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistorialEstadoPedidoResource\Pages;
use App\Models\HistorialEstadoPedido;
use App\Models\Pedido;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class HistorialEstadoPedidoResource extends Resource
{
    protected static ?string $model = HistorialEstadoPedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'Historial de Estados';
    
    protected static ?string $navigationGroup = 'Operaciones';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'id';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('fecha_cambio', Carbon::today())->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::whereDate('fecha_cambio', Carbon::today())->count() > 10 ? 'warning' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información del cambio de estado')
                            ->schema([
                                Select::make('pedido_id')
                                    ->label('Pedido')
                                    ->options(Pedido::all()->pluck('id', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if($state) {
                                            $pedido = Pedido::find($state);
                                            if($pedido) {
                                                $set('estado_anterior', $pedido->estado);
                                            }
                                        }
                                    })
                                    ->columnSpan(2),
                                
                                Select::make('estado_anterior')
                                    ->label('Estado anterior')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'en_cocina' => 'En cocina',
                                        'en_camino' => 'En camino',
                                        'entregado' => 'Entregado',
                                        'cancelado' => 'Cancelado',
                                    ])
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                
                                Select::make('estado_nuevo')
                                    ->label('Nuevo estado')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'en_cocina' => 'En cocina',
                                        'en_camino' => 'En camino',
                                        'entregado' => 'Entregado',
                                        'cancelado' => 'Cancelado',
                                    ])
                                    ->required()
                                    ->columnSpan(1),
                                
                                DateTimePicker::make('fecha_cambio')
                                    ->label('Fecha y hora del cambio')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y H:i')
                                    ->columnSpan(1),
                                
                                Select::make('usuario_id')
                                    ->label('Usuario que realizó el cambio')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->default(auth()->id())
                                    ->columnSpan(1),
                            ])
                            ->columns(2),
                    ]),
                
                Card::make()
                    ->schema([
                        Section::make('Información del pedido')
                            ->schema([
                                Placeholder::make('cliente')
                                    ->label('Cliente')
                                    ->content(function ($get) {
                                        $pedidoId = $get('pedido_id');
                                        if(!$pedidoId) return 'Seleccione un pedido';
                                        
                                        $pedido = Pedido::find($pedidoId);
                                        return $pedido ? $pedido->usuario->name : 'Cliente no encontrado';
                                    }),
                                    
                                Placeholder::make('total')
                                    ->label('Total del pedido')
                                    ->content(function ($get) {
                                        $pedidoId = $get('pedido_id');
                                        if(!$pedidoId) return 'Seleccione un pedido';
                                        
                                        $pedido = Pedido::find($pedidoId);
                                        return $pedido ? 'L ' . number_format($pedido->total, 2) : 'Pedido no encontrado';
                                    }),
                                    
                                Placeholder::make('direccion')
                                    ->label('Dirección de entrega')
                                    ->content(function ($get) {
                                        $pedidoId = $get('pedido_id');
                                        if(!$pedidoId) return 'Seleccione un pedido';
                                        
                                        $pedido = Pedido::find($pedidoId);
                                        return $pedido && $pedido->ubicacion ? $pedido->ubicacion->direccion_completa : 'Dirección no encontrada';
                                    })
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->visible(fn ($get) => (bool) $get('pedido_id')),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pedido_id')
                    ->label('# Pedido')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('pedido.usuario.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('estado_anterior')
                    ->label('Estado anterior')
                    ->colors([
                        'danger' => 'cancelado',
                        'warning' => 'pendiente',
                        'primary' => 'en_cocina',
                        'secondary' => 'en_camino',
                        'success' => 'entregado',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'cancelado',
                        'heroicon-o-clock' => 'pendiente',
                        'heroicon-o-fire' => 'en_cocina',
                        'heroicon-o-truck' => 'en_camino',
                        'heroicon-o-check-circle' => 'entregado',
                    ]),

                BadgeColumn::make('estado_nuevo')
                    ->label('Nuevo estado')
                    ->colors([
                        'danger' => 'cancelado',
                        'warning' => 'pendiente',
                        'primary' => 'en_cocina',
                        'secondary' => 'en_camino',
                        'success' => 'entregado',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'cancelado',
                        'heroicon-o-clock' => 'pendiente',
                        'heroicon-o-fire' => 'en_cocina',
                        'heroicon-o-truck' => 'en_camino',
                        'heroicon-o-check-circle' => 'entregado',
                    ]),

                TextColumn::make('fecha_cambio')
                    ->label('Fecha del cambio')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('usuario.name')
                    ->label('Modificado por')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado_nuevo')
                    ->label('Filtrar por nuevo estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_cocina' => 'En cocina',
                        'en_camino' => 'En camino',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
                    ]),
                    
                SelectFilter::make('usuario')
                    ->label('Filtrar por usuario')
                    ->relationship('usuario', 'name'),
                    
                Filter::make('fecha_cambio')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_cambio', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_cambio', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                    
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                    
                Tables\Actions\Action::make('verPedido')
                    ->label('Ver pedido')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->url(fn (HistorialEstadoPedido $record): string => route('filament.admin.resources.pedidos.edit', ['record' => $record->pedido_id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_cambio', 'desc')
            ->poll('60s'); // Actualizar la tabla cada 60 segundos
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
            'index' => Pages\ListHistorialEstadoPedidos::route('/'),
            'create' => Pages\CreateHistorialEstadoPedido::route('/create'),
            'view' => Pages\ViewHistorialEstadoPedido::route('/{record}'),
            'edit' => Pages\EditHistorialEstadoPedido::route('/{record}/edit'),
        ];
    }
    
    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\HistorialEstadosOverview::class,
        ];
    }
}