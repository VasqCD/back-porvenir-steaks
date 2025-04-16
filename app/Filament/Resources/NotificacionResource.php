<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificacionResource\Pages;
use App\Models\Notificacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificacionResource extends Resource
{
    protected static ?string $model = Notificacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    
    protected static ?string $navigationLabel = 'Notificaciones';
    
    protected static ?string $navigationGroup = 'Administración';
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $recordTitleAttribute = 'titulo';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('leida', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('leida', false)->count() > 0 ? 'warning' : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('usuario_id')
                            ->relationship('usuario', 'name')
                            ->required()
                            ->searchable(),
                            
                        Select::make('pedido_id')
                            ->relationship('pedido', 'id')
                            ->searchable(),
                            
                        TextInput::make('titulo')
                            ->required()
                            ->maxLength(255),
                            
                        Textarea::make('mensaje')
                            ->required()
                            ->maxLength(500),
                            
                        Select::make('tipo')
                            ->options([
                                'nuevo_pedido' => 'Nuevo pedido',
                                'pedido_en_cocina' => 'Pedido en cocina',
                                'pedido_en_camino' => 'Pedido en camino',
                                'pedido_entregado' => 'Pedido entregado',
                                'pedido_cancelado' => 'Pedido cancelado',
                                'solicitud_repartidor' => 'Solicitud de repartidor',
                                'cambio_rol' => 'Cambio de rol',
                                'repartidor_asignado' => 'Repartidor asignado',
                                'estado_pedido' => 'Cambio de estado de pedido',
                            ])
                            ->required(),
                            
                        Toggle::make('leida')
                            ->label('Marcada como leída')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('tipo')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'nuevo_pedido',
                        'secondary' => 'pedido_en_cocina',
                        'success' => 'pedido_entregado',
                        'warning' => 'pedido_en_camino',
                        'danger' => 'pedido_cancelado',
                        'info' => 'solicitud_repartidor',
                        'warning' => 'cambio_rol',
                        'primary' => 'repartidor_asignado',
                        'secondary' => 'estado_pedido',
                    ])
                    ->icons([
                        'heroicon-o-shopping-cart' => 'nuevo_pedido',
                        'heroicon-o-fire' => 'pedido_en_cocina',
                        'heroicon-o-check-circle' => 'pedido_entregado',
                        'heroicon-o-truck' => 'pedido_en_camino',
                        'heroicon-o-x-circle' => 'pedido_cancelado',
                        'heroicon-o-user-plus' => 'solicitud_repartidor',
                        'heroicon-o-identification' => 'cambio_rol',
                        'heroicon-o-user' => 'repartidor_asignado',
                        'heroicon-o-arrow-path' => 'estado_pedido',
                    ])
                    ->searchable(),
                    
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->weight(fn($record) => $record->leida ? 'normal' : 'bold'),
                    
                TextColumn::make('mensaje')
                    ->label('Mensaje')
                    ->limit(50)
                    ->searchable(),
                    
                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('pedido_id')
                    ->label('Pedido #')
                    ->url(fn ($record) => $record->pedido_id ? route('filament.admin.resources.pedidos.edit', ['record' => $record->pedido_id]) : null)
                    ->color('primary'),
                    
                IconColumn::make('leida')
                    ->label('Leída')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'nuevo_pedido' => 'Nuevo pedido',
                        'pedido_en_cocina' => 'Pedido en cocina',
                        'pedido_en_camino' => 'Pedido en camino',
                        'pedido_entregado' => 'Pedido entregado',
                        'pedido_cancelado' => 'Pedido cancelado',
                        'solicitud_repartidor' => 'Solicitud de repartidor',
                        'cambio_rol' => 'Cambio de rol',
                        'repartidor_asignado' => 'Repartidor asignado',
                        'estado_pedido' => 'Cambio de estado de pedido',
                    ]),
                    
                TernaryFilter::make('leida')
                    ->label('Estado de lectura')
                    ->placeholder('Todas')
                    ->trueLabel('Leídas')
                    ->falseLabel('No leídas'),
                    
                SelectFilter::make('usuario')
                    ->relationship('usuario', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('marcarLeida')
                    ->label('Marcar como leída')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Notificacion $record) {
                        $record->update(['leida' => true]);
                    })
                    ->visible(fn($record) => !$record->leida),
                    
                Tables\Actions\Action::make('verRecurso')
                    ->label('Ver recurso relacionado')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(function (Notificacion $record) {
                        if ($record->pedido_id) {
                            return route('filament.admin.resources.pedidos.edit', ['record' => $record->pedido_id]);
                        } elseif ($record->tipo === 'solicitud_repartidor') {
                            return route('filament.admin.resources.users.index', ['tableFilters[rol][value]' => 'cliente']);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->pedido_id !== null || $record->tipo === 'solicitud_repartidor'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('marcarLeidas')
                        ->label('Marcar como leídas')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['leida' => true]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListNotificacions::route('/'),
            'create' => Pages\CreateNotificacion::route('/create'),
            //'view' => Pages\ViewNotificacion::route('/{record}'),
            'edit' => Pages\EditNotificacion::route('/{record}/edit'),
        ];
    }
}