<?php
namespace App\Filament\Widgets;

use App\Models\Pedido;
use Filament\Widgets\LineChartWidget;
use Carbon\Carbon;

class PedidosChart extends LineChartWidget
{
    protected static ?string $heading = 'Pedidos por día';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getPedidoData();

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => $data['pedidos'],
                    'backgroundColor' => 'rgba(255, 117, 15, 0.2)',
                    'borderColor' => 'rgb(255, 117, 15)',
                ],
                [
                    'label' => 'Ingresos (Lps)',
                    'data' => $data['ventas'],
                    'backgroundColor' => 'rgba(72, 187, 120, 0.2)',
                    'borderColor' => 'rgb(72, 187, 120)',
                ]
            ],
            'labels' => $data['labels'],
        ];
    }

    private function getPedidoData(): array
    {
        $days = 7;
        $pedidos = [];
        $ventas = [];
        $labels = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');

            $pedidosDia = Pedido::whereDate('fecha_pedido', $date)->count();
            $pedidos[] = $pedidosDia;

            $ventasDia = Pedido::whereDate('fecha_pedido', $date)->sum('total');
            $ventas[] = $ventasDia;
        }

        return [
            'pedidos' => array_reverse($pedidos),
            'ventas' => array_reverse($ventas),
            'labels' => array_reverse($labels),
        ];
    }
}
