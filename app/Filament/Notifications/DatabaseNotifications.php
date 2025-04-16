<?php

namespace App\Filament\Notifications;

use App\Models\Notificacion;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;

class DatabaseNotifications
{
    public static function register(): void
    {
        // Registra el observador para nuevas notificaciones
        Notificacion::created(function (Notificacion $notificacion) {
            // Solo notificar al administrador
            if (Auth::check() && Auth::user()->rol === 'administrador') {
                self::createFilamentNotification($notificacion);
            }
        });
    }

    public static function createFilamentNotification(Notificacion $notificacion): void
    {
        // Configurar el color y el icono según el tipo de notificación
        $color = match ($notificacion->tipo) {
            'nuevo_pedido' => Color::Blue,
            'solicitud_repartidor' => Color::Orange,
            'pedido_en_cocina' => Color::Indigo,
            'pedido_en_camino' => Color::Amber,
            'pedido_entregado' => Color::Green,
            'pedido_cancelado' => Color::Red,
            default => Color::Gray,
        };

        $icon = match ($notificacion->tipo) {
            'nuevo_pedido' => 'heroicon-o-shopping-cart',
            'solicitud_repartidor' => 'heroicon-o-user-plus',
            'pedido_en_cocina' => 'heroicon-o-fire',
            'pedido_en_camino' => 'heroicon-o-truck',
            'pedido_entregado' => 'heroicon-o-check-circle',
            'pedido_cancelado' => 'heroicon-o-x-circle',
            default => 'heroicon-o-bell',
        };

        // Crear notificación en Filament
        FilamentNotification::make()
            ->title($notificacion->titulo)
            ->body($notificacion->mensaje)
            ->icon($icon)
            ->actions([
                Action::make('ver')
                    ->button()
                    ->url(self::getUrlForNotification($notificacion)),
                Action::make('marcar_leida')
                    ->button()
                    ->color('gray')
                    ->close()
                    ->action(function () use ($notificacion) {
                        $notificacion->update(['leida' => true]);
                    }),
            ])
            ->persistent()
            ->send();
    }

    private static function getUrlForNotification(Notificacion $notificacion): string
    {
        return match ($notificacion->tipo) {
            'nuevo_pedido', 'pedido_en_cocina', 'pedido_en_camino', 'pedido_entregado', 'pedido_cancelado' => 
                $notificacion->pedido_id ? 
                    route('filament.admin.resources.pedidos.edit', ['record' => $notificacion->pedido_id]) : 
                    route('filament.admin.resources.pedidos.index'),
            'solicitud_repartidor' => route('filament.admin.resources.users.index', [
                'tableFilters[rol][value]' => 'cliente',
            ]),
            default => route('filament.admin.dashboard'),
        };
    }
}