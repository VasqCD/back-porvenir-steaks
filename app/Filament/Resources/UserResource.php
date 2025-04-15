<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información Personal')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Nombre del usuario'),

                                        TextInput::make('apellido')
                                            ->label('Apellido')
                                            ->maxLength(255)
                                            ->placeholder('Apellido del usuario'),

                                        TextInput::make('email')
                                            ->label('Correo Electrónico')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('correo@ejemplo.com'),

                                        TextInput::make('telefono')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(255)
                                            ->placeholder('+504 9999-9999'),
                                    ]),

                                FileUpload::make('foto_perfil')
                                    ->label('Foto de Perfil')
                                    ->image()
                                    ->directory('usuarios')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Información de Cuenta')
                            ->schema([
                                Select::make('rol')
                                    ->label('Rol')
                                    ->options([
                                        'cliente' => 'Cliente',
                                        'repartidor' => 'Repartidor',
                                        'administrador' => 'Administrador',
                                    ])
                                    ->required()
                                    ->default('cliente')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                        if ($state === 'repartidor') {
                                            // Mostrar un mensaje informativo cuando se cambia a repartidor
                                            Notification::make()
                                                ->title('Información sobre repartidores')
                                                ->body('Al guardar, se creará un perfil de repartidor y se utilizarán las coordenadas de la ubicación principal si están disponibles.')
                                                ->info()
                                                ->persistent()
                                                ->send();
                                        }
                                    }),

                                TextInput::make('password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->maxLength(255),

                                Toggle::make('email_verified_at')
                                    ->label('Email Verificado')
                                    ->default(false)
                                    ->dehydrateStateUsing(fn($state) => $state ? now() : null)
                                    ->dehydrated(fn($state) => $state)
                                    ->formatStateUsing(fn($state) => $state ? true : false),

                                DateTimePicker::make('fecha_registro')
                                    ->label('Fecha de Registro')
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now())
                                    ->required(),

                                DateTimePicker::make('ultima_conexion')
                                    ->label('Última Conexión')
                                    ->displayFormat('d/m/Y H:i'),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_perfil')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name . ' ' . $record->apellido) . '&color=FFFFFF&background=6366F1'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('apellido')
                    ->label('Apellido')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('rol')
                    ->label('Rol')
                    ->colors([
                        'primary' => 'cliente',
                        'warning' => 'repartidor',
                        'success' => 'administrador',
                    ])
                    ->icons([
                        'heroicon-o-user' => 'cliente',
                        'heroicon-o-truck' => 'repartidor',
                        'heroicon-o-cog' => 'administrador',
                    ])
                    ->searchable()
                    ->sortable(),

                IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('fecha_registro')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ultima_conexion')
                    ->label('Última Conexión')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('rol')
                    ->label('Filtrar por Rol')
                    ->options([
                        'cliente' => 'Clientes',
                        'repartidor' => 'Repartidores',
                        'administrador' => 'Administradores',
                    ]),

                TernaryFilter::make('email_verified_at')
                    ->label('Estado de Verificación')
                    ->placeholder('Todos los usuarios')
                    ->trueLabel('Verificados')
                    ->falseLabel('No verificados'),

                TernaryFilter::make('deleted_at')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Eliminados')
                    ->falseLabel('Activos')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('deleted_at'),
                        false: fn(Builder $query) => $query->whereNull('deleted_at'),
                        blank: fn(Builder $query) => $query
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil'),

                    Tables\Actions\Action::make('resetPassword')
                        ->label('Resetear contraseña')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->form([
                            TextInput::make('password')
                                ->label('Nueva contraseña')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->rule('regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'),

                            TextInput::make('password_confirmation')
                                ->label('Confirmar contraseña')
                                ->password()
                                ->required()
                                ->same('password'),
                        ])
                        ->action(function (User $record, array $data): void {
                            $record->update(['password' => bcrypt($data['password'])]);
                            Notification::make()
                                ->title('Contraseña actualizada')
                                ->body('La contraseña del usuario ha sido actualizada correctamente.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\DeleteAction::make()
                            ->icon('heroicon-o-trash'),

                        Tables\Actions\RestoreAction::make()
                            ->icon('heroicon-o-arrow-path'),

                        Tables\Actions\ForceDeleteAction::make()
                            ->icon('heroicon-o-x-mark'),
                    ])->dropdown(true),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('verificar')
                        ->label('Marcar como verificados')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                $record->update(['email_verified_at' => now()]);
                            });
                            Notification::make()
                                ->title('Usuarios verificados')
                                ->title('Usuarios verificados')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRepartidorStatus($record): string
    {
        if (!$record) return 'No disponible';

        if ($record->rol !== 'repartidor') {
            return 'No es repartidor';
        }

        if (!$record->repartidor) {
            return 'Sin perfil de repartidor';
        }

        $ubicacion = $record->repartidor->ultima_ubicacion_lat ? 'Con ubicación' : 'Sin ubicación';
        return "Perfil de repartidor activo. {$ubicacion}";
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PedidosRelationManager::class,
            RelationManagers\UbicacionesRelationManager::class,
            RelationManagers\NotificacionesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
