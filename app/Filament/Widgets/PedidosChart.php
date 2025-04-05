<?php
namespace App\Filament\Widgets;

use App\Models\Pedido;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PedidosChart extends ChartWidget
{
    protected static ?string $heading = 'Pedidos y ventas de la última semana';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';
    
    // Añadir este método que falta
    protected function getType(): string
    {
        return 'line';
    }
    
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
                    'borderWidth' => 2,
                    'tension' => 0.2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Ventas (L)',
                    'data' => $data['ventas'],
                    'backgroundColor' => 'rgba(72, 187, 120, 0.2)',
                    'borderColor' => 'rgb(72, 187, 120)',
                    'borderWidth' => 2,
                    'tension' => 0.2,
                    'yAxisID' => 'y1',
                ]
            ],
            'labels' => $data['labels'],
        ];
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Pedidos',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Ventas (L)',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.2,
                ],
                'point' => [
                    'radius' => 5,
                    'hoverRadius' => 7,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
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
            
            // Format de fecha con dia de semana
            $labels[] = $date->locale('es')->isoFormat('ddd D MMM');

            $pedidosDia = Pedido::whereDate('fecha_pedido', $date)->count();
            $pedidos[] = $pedidosDia;

            $ventasDia = Pedido::whereDate('fecha_pedido', $date)->sum('total');
            $ventas[] = $ventasDia;
        }

        // Invertir arreglos para mostrar los datos en orden cronológico
        return [
            'pedidos' => array_reverse($pedidos),
            'ventas' => array_reverse($ventas),
            'labels' => array_reverse($labels),
        ];
    }
    
    public static function canView(): bool
    {
        return auth()->user()->can('view stats');
    }
}