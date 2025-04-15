<?php

namespace App\Filament\Resources\PedidoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\FcmService;

class HistorialEstadosRelationManager extends RelationManager
{
    protected static string $relationship = 'historialEstados';

    protected static ?string $recordTitleAttribute = 'estado_nuevo';

    protected static ?string $title = 'Historial de estados';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('estado_nuevo')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_cocina' => 'En cocina',
                        'en_camino' => 'En camino',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
                    ])
                    ->required(),

                Forms\Components\DateTimePicker::make('fecha_cambio')
                    ->label('Fecha de cambio')
                    ->required()
                    ->default(now()),

                Forms\Components\Select::make('usuario_id')
                    ->label('Usuario que realizó el cambio')
                    ->relationship('usuario', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('estado_anterior')
                    ->label('Estado anterior')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('estado_anterior')
                    ->label('Estado anterior')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en_cocina' => 'primary',
                        'en_camino' => 'secondary',
                        'entregado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('estado_nuevo')
                    ->label('Nuevo estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en_cocina' => 'primary',
                        'en_camino' => 'secondary',
                        'entregado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('fecha_cambio')
                    ->label('Fecha de cambio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Cambiado por')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, $livewire): array {
                        $pedido = $livewire->getOwnerRecord();
                        $data['estado_anterior'] = $pedido->estado;

                        // También actualizar el estado del pedido
                        $pedido->update(['estado' => $data['estado_nuevo']]);

                        // Si el estado es "entregado", actualizar la fecha de entrega
                        if ($data['estado_nuevo'] === 'entregado') {
                            $pedido->update(['fecha_entrega' => now()]);
                        }

                        // Enviar notificación push al usuario
                        try {
                            $fcmService = app(FcmService::class);
                            $fcmService->sendPedidoStatusNotification(
                                $pedido->usuario,
                                (string) $pedido->id,
                                $data['estado_nuevo']
                            );
                        } catch (\Exception $e) {
                            // Registrar el error pero continuar
                            \Illuminate\Support\Facades\Log::error('Error al enviar notificación: ' . $e->getMessage());
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Generalmente no permitimos acciones masivas en historial
            ])
            ->defaultSort('fecha_cambio', 'desc');
    }
}
