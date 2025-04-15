<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Filament\Resources\PedidoResource\RelationManagers;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\HistorialEstadoPedido;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\FcmService;
use App\Models\Notificacion;


class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Información del Pedido')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('usuario_id')
                                                    ->relationship('usuario', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->columnSpan(1),

                                                Select::make('ubicacion_id')
                                                    ->relationship('ubicacion', 'direccion_completa')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),

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
                                            })
                                            ->columnSpan(1),

                                        Grid::make(2)
                                            ->schema([
                                                DateTimePicker::make('fecha_pedido')
                                                    ->required()
                                                    ->default(now())
                                                    ->displayFormat('d/m/Y H:i'),

                                                DateTimePicker::make('fecha_entrega')
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->hidden(fn($get) => $get('estado') !== 'entregado'),
                                            ]),

                                        Select::make('repartidor_id')
                                            ->relationship('repartidor', 'id', fn($query) => $query->whereHas('usuario'))
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->usuario->name)
                                            ->searchable()
                                            ->preload()
                                            ->label('Repartidor')
                                            ->hidden(fn($get) => in_array($get('estado'), ['pendiente', 'cancelado'])),
                                    ])
                                    ->columnSpan(['lg' => 2]),

                                Section::make('Resumen')
                                    ->schema([
                                        TextInput::make('total')
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->required()
                                            ->numeric()
                                            ->prefix('L')
                                            ->helperText('15% de impuesto incluido'),

                                        Placeholder::make('subtotal')
                                            ->label('Subtotal')
                                            ->content(function ($get, $record) {
                                                if ($record) {
                                                    $subtotal = $record->detalles->sum('subtotal');
                                                    return 'L ' . number_format($subtotal, 2);
                                                }
                                                return 'L 0.00';
                                            }),

                                        Placeholder::make('impuesto')
                                            ->label('Impuesto (15%)')
                                            ->content(function ($get, $record) {
                                                if ($record) {
                                                    $subtotal = $record->detalles->sum('subtotal');
                                                    $impuesto = $subtotal * 0.15;
                                                    return 'L ' . number_format($impuesto, 2);
                                                }
                                                return 'L 0.00';
                                            }),
                                    ])
                                    ->columnSpan(['lg' => 1]),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpan('full'),

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
                                    ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                        $producto = Producto::find($state);
                                        $precioUnitario = $producto ? $producto->precio : 0;
                                        $set('precio_unitario', $precioUnitario);

                                        // Obtener la cantidad actual y calcular subtotal
                                        $cantidad = $get('cantidad') ?? 1;
                                        $set('subtotal', $precioUnitario * $cantidad);

                                        // Recalcular el total
                                        self::calcularTotal($livewire);
                                    }),

                                TextInput::make('cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                        $precioUnitario = $get('precio_unitario');
                                        $subtotal = $state * $precioUnitario;
                                        $set('subtotal', $subtotal);

                                        // Recalcular total
                                        self::calcularTotal($livewire);
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
                            ->itemLabel(fn(array $state): ?string =>
                            $state['producto_id'] ? Producto::find($state['producto_id'])?->nombre ?? 'Producto' : 'Nuevo producto')
                            ->collapsible()
                            ->reorderable()
                            ->cloneable()
                    ])
                    ->visible(fn($livewire) => $livewire instanceof Pages\CreatePedido || $livewire instanceof Pages\EditPedido),

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
                            ->columnSpanFull()
                            ->disabled(),
                    ])
                    ->visible(fn($record) => $record && $record->estado === 'entregado'),

                Card::make()
                    ->schema([
                        Placeholder::make('historial_title')
                            ->label('Historial de Estados')
                            ->content(fn($record) => $record ? '' : 'El historial estará disponible después de guardar'),

                        Placeholder::make('historial')
                            ->content(function ($record) {
                                if (!$record) return null;

                                return view('filament.components.historial-estados', [
                                    'historial' => $record->historialEstados()->with('usuario')->orderBy('fecha_cambio', 'desc')->get()
                                ]);
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('# Pedido')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('estado')
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

                TextColumn::make('total')
                    ->money('HNL')
                    ->label('Total')
                    ->sortable(),

                TextColumn::make('repartidor.usuario.name')
                    ->label('Repartidor')
                    ->searchable()
                    ->default('Sin asignar')
                    ->sortable(),

                TextColumn::make('fecha_pedido')
                    ->label('Fecha Pedido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fecha_entrega')
                    ->label('Fecha Entrega')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('calificacion')
                    ->label('Calificación')
                    ->color('success')
                    ->formatStateUsing(fn($state) => $state ? "★ {$state}" : 'Sin calificar')
                    ->toggleable(),
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
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_pedido', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_pedido', '<=', $date),
                            );
                    }),

                SelectFilter::make('repartidor')
                    ->relationship('repartidor.usuario', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('cambiar_estado')
                    ->label('Cambiar estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->modalWidth('md')
                    ->modalHeading('Cambiar estado del pedido')
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

                        Select::make('repartidor_id')
                            ->relationship('repartidor', 'id', fn($query) => $query->whereHas('usuario')->where('disponible', true))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->usuario->name)
                            ->searchable()
                            ->preload()
                            ->label('Asignar repartidor')
                            ->visible(fn($get) => in_array($get('estado'), ['en_cocina', 'en_camino'])),
                    ])
                    ->action(function (Pedido $record, array $data): void {
                        $estadoAnterior = $record->estado;

                        $record->update([
                            'estado' => $data['estado'],
                            'fecha_entrega' => $data['estado'] === 'entregado' ? now() : $record->fecha_entrega,
                            'repartidor_id' => $data['repartidor_id'] ?? $record->repartidor_id,
                        ]);

                        // Registrar cambio en historial
                        HistorialEstadoPedido::create([
                            'pedido_id' => $record->id,
                            'estado_anterior' => $estadoAnterior,
                            'estado_nuevo' => $data['estado'],
                            'fecha_cambio' => now(),
                            'usuario_id' => auth()->id(),
                        ]);

                        // Enviar notificación push al usuario
                        try {
                            $fcmService = app(FcmService::class);
                            $fcmService->sendPedidoStatusNotification(
                                $record->usuario,
                                (string) $record->id,
                                $data['estado']
                            );

                            Notification::make()
                                ->title('Estado actualizado')
                                ->body('Se ha enviado una notificación al cliente')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            // Registrar el error pero continuar
                            \Illuminate\Support\Facades\Log::error('Error al enviar notificación: ' . $e->getMessage());

                            Notification::make()
                                ->title('Estado actualizado')
                                ->body('Pero hubo un problema al enviar la notificación al cliente')
                                ->warning()
                                ->send();
                        }
                    }),

                Action::make('asignar_repartidor')
                    ->label('Asignar repartidor')
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->modalWidth('md')
                    ->modalHeading('Asignar repartidor al pedido')
                    ->form([
                        Select::make('repartidor_id')
                            ->relationship('repartidor', 'id', fn($query) => $query->whereHas('usuario')->where('disponible', true))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->usuario->name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Repartidor'),
                    ])
                    ->visible(fn($record) => in_array($record->estado, ['pendiente', 'en_cocina']) && !$record->repartidor_id)
                    ->action(function (Pedido $record, array $data): void {
                        $record->update([
                            'repartidor_id' => $data['repartidor_id'],
                        ]);

                        // Crear notificación para el cliente
                        Notificacion::create([
                            'usuario_id' => $record->usuario_id,
                            'pedido_id' => $record->id,
                            'titulo' => 'Repartidor asignado',
                            'mensaje' => 'Se ha asignado un repartidor a tu pedido. Pronto estará en camino.',
                            'tipo' => 'repartidor_asignado',
                        ]);

                        Notification::make()
                            ->title('Repartidor asignado')
                            ->success()
                            ->send();
                    }),

                Action::make('ver_detalles')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('secondary')
                    ->modalContent(fn(Pedido $record) => view('filament.pages.pedido-detalles', ['pedido' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_pedido', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DetallesRelationManager::class,
            RelationManagers\HistorialEstadosRelationManager::class,
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
