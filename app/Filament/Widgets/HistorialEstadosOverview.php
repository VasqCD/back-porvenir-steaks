<?php

namespace App\Filament\Widgets;

use App\Models\HistorialEstadoPedido;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HistorialEstadosOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Obtener cambios de estado del día actual
        $cambiosHoy = HistorialEstadoPedido::whereDate('fecha_cambio', Carbon::today())->count();
        
        // Obtener cambios de estado por tipo en la última semana
        $cambioPorTipo = HistorialEstadoPedido::whereDate('fecha_cambio', '>=', Carbon::now()->subDays(7))
            ->select('estado_nuevo', DB::raw('count(*) as total'))
            ->groupBy('estado_nuevo')
            ->pluck('total', 'estado_nuevo')
            ->toArray();
            
        // Preparar datos para los estados (usar 0 si no hay cambios)
        $pendientes = $cambioPorTipo['pendiente'] ?? 0;
        $enCocina = $cambioPorTipo['en_cocina'] ?? 0;
        $enCamino = $cambioPorTipo['en_camino'] ?? 0;
        $entregados = $cambioPorTipo['entregado'] ?? 0;
        $cancelados = $cambioPorTipo['cancelado'] ?? 0;
        
        // Obtener datos para los últimos 7 días para gráfico
        $datosDiarios = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $datosDiarios[] = HistorialEstadoPedido::whereDate('fecha_cambio', $fecha)->count();
        }
        
        return [
            Stat::make('Cambios de estado hoy', $cambiosHoy)
                ->description('Actualizaciones registradas hoy')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('primary')
                ->chart($datosDiarios),
                
            Stat::make('Pedidos entregados', $entregados)
                ->description(($entregados + $cancelados) > 0 ? round(($entregados / ($entregados + $cancelados)) * 100) . '% tasa de éxito' : '0% tasa de éxito')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([$entregados, $cancelados]),
                
            Stat::make('Pedidos cancelados', $cancelados)
                ->description(($entregados + $cancelados) > 0 ? round(($cancelados / ($entregados + $cancelados)) * 100) . '% tasa de cancelación' : '0% tasa de cancelación')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart([$cancelados]),
        ];
    }
}