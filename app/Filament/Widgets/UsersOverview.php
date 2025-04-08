<?php
namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class UsersOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected function getStats(): array
    {
        // Total de usuarios
        $totalUsuarios = User::count();
        
        // Nuevos usuarios (últimos 7 días)
        $nuevosUsuariosSemanales = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        
        // Porcentaje de crecimiento semanal
        $usuariosSemanaPasada = User::where('created_at', '>=', Carbon::now()->subDays(14))
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->count();
        
        $porcentajeCrecimiento = $usuariosSemanaPasada > 0
            ? round((($nuevosUsuariosSemanales - $usuariosSemanaPasada) / $usuariosSemanaPasada) * 100, 2)
            : 100;
        
        // Distribución por roles
        $clientes = User::where('rol', 'cliente')->count();
        $repartidores = User::where('rol', 'repartidor')->count();
        $administradores = User::where('rol', 'administrador')->count();
        
        return [
            Stat::make('Total de Usuarios', $totalUsuarios)
                ->description($porcentajeCrecimiento >= 0 ? "$porcentajeCrecimiento% de crecimiento" : "$porcentajeCrecimiento% de reducción")
                ->descriptionIcon($porcentajeCrecimiento >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($porcentajeCrecimiento >= 0 ? 'success' : 'danger')
                ->chart([
                    $this->getUsersCountForDay(6),
                    $this->getUsersCountForDay(5),
                    $this->getUsersCountForDay(4),
                    $this->getUsersCountForDay(3),
                    $this->getUsersCountForDay(2),
                    $this->getUsersCountForDay(1),
                    $this->getUsersCountForDay(0),
                ]),
            
            Stat::make('Clientes', $clientes)
                ->description("$nuevosUsuariosSemanales nuevos esta semana")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([$clientes, $repartidores, $administradores]),
            
            Stat::make('Repartidores', $repartidores)
                ->description(round(($repartidores / $totalUsuarios) * 100, 1) . "% del total")
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
        ];
    }
    
    private function getUsersCountForDay($daysAgo)
    {
        $date = Carbon::now()->subDays($daysAgo)->format('Y-m-d');
        return User::whereDate('created_at', $date)->count();
    }
}