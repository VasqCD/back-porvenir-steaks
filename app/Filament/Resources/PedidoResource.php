<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\HistorialEstadoPedido;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

                        Select::make('ubicacion_id')
                            ->relationship('ubicacion', 'direccion_completa')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'en_cocina' => 'En cocina',
                                'en_camino' => 'En camino',
                                'entregado' => 'Entregado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('pendiente')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'entregado') {
                                    $set('fecha_entrega', now());
                                }
                            }),

                        TextInput::make('total')
                            ->disabled()
                            ->dehydrated(true)
                            ->required()
                            ->numeric()
                            ->prefix('L')
                            ->helperText('15% de impuesto incluido'),

                        DateTimePicker::make('fecha_pedido')
                            ->required()
                            ->default(now()),

                        DateTimePicker::make('fecha_entrega')
                            ->hidden(fn ($get) => $get('estado') !== 'entregado'),

                        Select::make('repartidor_id')
                            ->relationship('repartidor', 'id', fn ($query) => $query->whereHas('usuario'))
                            ->searchable()
                            ->preload()
                            ->label('Repartidor')
                            ->hidden(fn ($get) => in_array($get('estado'), ['pendiente', 'cancelado'])),
                    ])
                    ->columns(2),

                Card::make()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Select::make('producto_id')
                                    ->relationship('producto', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $producto = Producto::find($state);
                                        $precioUnitario = $producto ? $producto->precio : 0;
                                        $set('precio_unitario', $precioUnitario);

                                        // Obtener la cantidad actual y calcular subtotal
                                        $cantidad = $get('cantidad') ?? 1;
                                        $set('subtotal', $precioUnitario * $cantidad);
                                    }),

                                TextInput::make('cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                        $precioUnitario = $get('precio_unitario');
                                        $subtotal = $state * $precioUnitario;
                                        $set('subtotal', $subtotal);

                                        // Recalcular total
                                        $this->calcularTotal($livewire);
                                    }),

                                TextInput::make('precio_unitario')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->required()
                                    ->numeric()
                                    ->prefix('L'),

                                TextInput::make('subtotal')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->required()
                                    ->numeric()
                                    ->prefix('L'),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->afterStateUpdated(function (array $state, callable $set, $livewire) {
                                self::calcularTotal($livewire);
                            })
                    ])
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreatePedido || $livewire instanceof Pages\EditPedido),

                Card::make()
                    ->schema([
                        Placeholder::make('calificacion')
                            ->content(function ($record) {
                                if (!$record || !$record->calificacion) return 'Sin calificación';

                                return view('filament.components.star-rating', [
                                    'rating' => $record->calificacion
                                ]);
                            }),

                        Textarea::make('comentario_calificacion')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record && $record->estado === 'entregado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usuario.name')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('estado')
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
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->money('HNL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_entrega')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('calificacion')
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state ? "★ {$state}" : 'Sin calificar'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_cocina' => 'En cocina',
                        'en_camino' => 'En camino',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
                    ]),

                Filter::make('fecha_pedido')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pedido', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pedido', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('cambiar_estado')
                    ->label('Cambiar estado')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'en_cocina' => 'En cocina',
                                'en_camino' => 'En camino',
                                'entregado' => 'Entregado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required(),
                    ])
                    ->action(function (Pedido $record, array $data): void {
                        $record->update([
                            'estado' => $data['estado'],
                            'fecha_entrega' => $data['estado'] === 'entregado' ? now() : $record->fecha_entrega,
                        ]);

                        // Registrar cambio en historial
                        HistorialEstadoPedido::create([
                            'pedido_id' => $record->id,
                            'estado_anterior' => $record->getOriginal('estado'),
                            'estado_nuevo' => $data['estado'],
                            'fecha_cambio' => now(),
                            'usuario_id' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Estado actualizado')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListPedidos::route('/'),
            'create' => Pages\CreatePedido::route('/create'),
            'edit' => Pages\EditPedido::route('/{record}/edit'),
        ];
    }

    private static function calcularTotal($livewire): void
    {
        $subtotal = 0;
        $detalles = $livewire->data['detalles'] ?? [];

        foreach ($detalles as $detalle) {
            $subtotal += $detalle['subtotal'] ?? 0;
        }

        // Aplicar 15% de impuesto
        $impuesto = $subtotal * 0.15;
        $total = $subtotal + $impuesto;

        // Actualizar el campo total
        $livewire->data['total'] = round($total, 2);
    }
}
