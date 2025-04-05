<?php
namespace App\Filament\Widgets;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Repartidor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Estadísticas de pedidos
        $pedidosHoy = Pedido::whereDate('fecha_pedido', today())->count();
        $pedidosAyer = Pedido::whereDate('fecha_pedido', today()->subDay())->count();
        $difPedidos = $pedidosAyer > 0 ? (($pedidosHoy - $pedidosAyer) / $pedidosAyer) * 100 : 100;
        
        // Estadísticas de ventas
        $ventasHoy = Pedido::whereDate('fecha_pedido', today())->sum('total');
        $ventasAyer = Pedido::whereDate('fecha_pedido', today()->subDay())->sum('total');
        $difVentas = $ventasAyer > 0 ? (($ventasHoy - $ventasAyer) / $ventasAyer) * 100 : 100;
        
        // Estadísticas generales
        $clientesTotal = User::where('rol', 'cliente')->count();
        $pedidosEnProceso = Pedido::whereIn('estado', ['pendiente', 'en_cocina', 'en_camino'])->count();
        $repartidoresActivos = Repartidor::where('disponible', true)->count();
        
        // Productos más vendidos (esta semana)
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        $topProductos = Producto::withCount(['detallesPedido' => function($query) use ($inicioSemana, $finSemana) {
            $query->whereHas('pedido', function($q) use ($inicioSemana, $finSemana) {
                $q->whereBetween('fecha_pedido', [$inicioSemana, $finSemana]);
            });
        }])
        ->orderBy('detalles_pedido_count', 'desc')
        ->limit(5)
        ->get();
        
        $topProductoTexto = $topProductos->count() > 0 
            ? $topProductos->first()->nombre . ' (' . $topProductos->first()->detalles_pedido_count . ' vendidos)' 
            : 'Sin datos';

        return [
            Stat::make('Pedidos hoy', $pedidosHoy)
                ->description($difPedidos >= 0 ? '+' . number_format(abs($difPedidos), 1) . '% vs ayer' : number_format($difPedidos, 1) . '% vs ayer')
                ->descriptionIcon($difPedidos >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($difPedidos >= 0 ? 'success' : 'danger')
                ->chart([7, 2, 10, 3, 15, 4, $pedidosHoy]),

            Stat::make('Ventas hoy', 'L'.number_format($ventasHoy, 2))
                ->description($difVentas >= 0 ? '+' . number_format(abs($difVentas), 1) . '% vs ayer' : number_format($difVentas, 1) . '% vs ayer')
                ->descriptionIcon($difVentas >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($difVentas >= 0 ? 'success' : 'danger')
                ->chart([750, 1100, 900, 1500, 800, 1200, $ventasHoy]),

            Stat::make('Pedidos en proceso', $pedidosEnProceso)
                ->icon('heroicon-o-clock')
                ->color('warning'),
                
            Stat::make('Repartidores activos', $repartidoresActivos)
                ->icon('heroicon-o-truck')
                ->color('info'),
                
            Stat::make('Clientes registrados', $clientesTotal)
                ->icon('heroicon-o-users')
                ->color('primary'),
                
            Stat::make('Producto más vendido', $topProductoTexto)
                ->icon('heroicon-o-fire')
                ->color('success'),
        ];
    }
}