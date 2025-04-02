<?php
namespace App\Filament\Widgets;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $pedidosHoy = Pedido::whereDate('fecha_pedido', today())->count();
        $ventasHoy = Pedido::whereDate('fecha_pedido', today())->sum('total');
        $clientesTotal = User::where('rol', 'cliente')->count();
        $productosMasVendidos = Producto::withCount(['detallesPedido as ventas'])->orderBy('ventas', 'desc')->limit(5)->get();

        return [
            Stat::make('Pedidos hoy', $pedidosHoy)
                ->description('Pedidos recibidos hoy')
                ->descriptionIcon('heroicon-s-shopping-cart')
                ->color('success'),

            Stat::make('Ventas hoy', 'L'.number_format($ventasHoy, 2))
                ->description('Ingresos del día')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('success'),

            Stat::make('Clientes', $clientesTotal)
                ->description('Clientes registrados')
                ->descriptionIcon('heroicon-s-users')
                ->color('primary'),
        ];
    }
}
