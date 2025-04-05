<?php

namespace App\Filament\Resources\PedidoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Producto;

class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $recordTitleAttribute = 'producto.nombre';

    protected static ?string $title = 'Detalles del pedido';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('producto_id')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $producto = Producto::find($state);
                        $precioUnitario = $producto ? $producto->precio : 0;
                        $set('precio_unitario', $precioUnitario);
                        
                        $cantidad = 1;
                        $set('cantidad', $cantidad);
                        $set('subtotal', $precioUnitario * $cantidad);
                    }),
                
                Forms\Components\TextInput::make('cantidad')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->minValue(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $precioUnitario = $get('precio_unitario');
                        $subtotal = $state * $precioUnitario;
                        $set('subtotal', $subtotal);
                    }),
                
                Forms\Components\TextInput::make('precio_unitario')
                    ->disabled()
                    ->dehydrated(true)
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                
                Forms\Components\TextInput::make('subtotal')
                    ->disabled()
                    ->dehydrated(true)
                    ->required()
                    ->numeric()
                    ->prefix('L'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->label('Precio Unitario')
                    ->money('HNL')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('HNL')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Asegurarse de que el subtotal se calcule correctamente
                        $data['subtotal'] = $data['precio_unitario'] * $data['cantidad'];
                        return $data;
                    })
                    ->after(function () {
                        // Recalcular el total del pedido después de agregar un detalle
                        $pedido = $this->getOwnerRecord();
                        $subtotal = $pedido->detalles()->sum('subtotal');
                        $impuesto = $subtotal * 0.15;
                        $total = $subtotal + $impuesto;
                        $pedido->update(['total' => round($total, 2)]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Asegurarse de que el subtotal se calcule correctamente
                        $data['subtotal'] = $data['precio_unitario'] * $data['cantidad'];
                        return $data;
                    })
                    ->after(function () {
                        // Recalcular el total del pedido después de editar un detalle
                        $pedido = $this->getOwnerRecord();
                        $subtotal = $pedido->detalles()->sum('subtotal');
                        $impuesto = $subtotal * 0.15;
                        $total = $subtotal + $impuesto;
                        $pedido->update(['total' => round($total, 2)]);
                    }),
                
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        // Recalcular el total del pedido después de eliminar un detalle
                        $pedido = $this->getOwnerRecord();
                        $subtotal = $pedido->detalles()->sum('subtotal');
                        $impuesto = $subtotal * 0.15;
                        $total = $subtotal + $impuesto;
                        $pedido->update(['total' => round($total, 2)]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            // Recalcular el total del pedido después de eliminar detalles
                            $pedido = $this->getOwnerRecord();
                            $subtotal = $pedido->detalles()->sum('subtotal');
                            $impuesto = $subtotal * 0.15;
                            $total = $subtotal + $impuesto;
                            $pedido->update(['total' => round($total, 2)]);
                        }),
                ]),
            ]);
    }
}