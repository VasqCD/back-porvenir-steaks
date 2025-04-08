<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PedidosRelationManager extends RelationManager
{
    protected static string $relationship = 'pedidos';

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $title = 'Pedidos realizados';
    
    protected static ?string $inverseRelationship = 'usuario';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('# Pedido')
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
                    ->sortable(),
                    
                TextColumn::make('fecha_pedido')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                BadgeColumn::make('calificacion')
                    ->label('Calificación')
                    ->color('success')
                    ->formatStateUsing(fn($state) => $state ? "★ {$state}" : 'Sin calificar'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_cocina' => 'En cocina',
                        'en_camino' => 'En camino',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
                    ]),
            ])
            ->headerActions([
                // No permitimos crear pedidos desde aquí
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.pedidos.edit', ['record' => $record])),
            ])
            ->bulkActions([
                // No permitimos acciones masivas para pedidos desde aquí
            ])
            ->defaultSort('fecha_pedido', 'desc');
    }
}