<?php
namespace App\Filament\Widgets;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoriasChart extends ChartWidget
{
    protected static ?string $heading = 'Ventas por categoría';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';
    
    protected function getData(): array
    {
        $data = $this->getCategoriaData();

        return [
            'datasets' => [
                [
                    'label' => 'Ventas por categoría este mes',
                    'data' => $data['ventas'],
                    'backgroundColor' => $this->getRandomColors(count($data['ventas'])),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
    
    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => '
                            function(context) {
                                return context.label + ": L " + context.parsed.toLocaleString();
                            }
                        ',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'cutout' => '70%',
        ];
    }

    private function getCategoriaData(): array
    {
        // Obtener primer y último día del mes actual
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        
        // Consulta para obtener ventas por categoría
        $ventas = DB::table('categorias')
            ->select('categorias.nombre', DB::raw('SUM(detalle_pedidos.subtotal) as total_ventas'))
            ->join('productos', 'categorias.id', '=', 'productos.categoria_id')
            ->join('detalle_pedidos', 'productos.id', '=', 'detalle_pedidos.producto_id')
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->whereBetween('pedidos.fecha_pedido', [$inicioMes, $finMes])
            ->whereIn('pedidos.estado', ['entregado', 'en_camino', 'en_cocina']) // Excluir cancelados o pendientes
            ->groupBy('categorias.nombre')
            ->orderBy('total_ventas', 'desc')
            ->get();
        
        $labels = $ventas->pluck('nombre')->toArray();
        $ventasData = $ventas->pluck('total_ventas')->toArray();
        
        // Agregar "Otros" si hay más de 5 categorías
        if (count($labels) > 5) {
            $mainLabels = array_slice($labels, 0, 5);
            $mainVentas = array_slice($ventasData, 0, 5);
            
            $otrosVentas = array_sum(array_slice($ventasData, 5));
            
            $labels = array_merge($mainLabels, ['Otros']);
            $ventasData = array_merge($mainVentas, [$otrosVentas]);
        }
        
        return [
            'labels' => $labels,
            'ventas' => $ventasData,
        ];
    }
    
    private function getRandomColors(int $count): array
    {
        $colors = [
            'rgba(54, 162, 235, 0.8)',   // Azul
            'rgba(255, 99, 132, 0.8)',   // Rojo
            'rgba(75, 192, 192, 0.8)',   // Verde azulado
            'rgba(255, 159, 64, 0.8)',   // Naranja
            'rgba(153, 102, 255, 0.8)',  // Morado
            'rgba(255, 205, 86, 0.8)',   // Amarillo
            'rgba(201, 203, 207, 0.8)',  // Gris
            'rgba(255, 99, 71, 0.8)',    // Tomate
            'rgba(120, 190, 33, 0.8)',   // Verde limón
            'rgba(120, 28, 129, 0.8)',   // Morado oscuro
        ];
        
        // Si hay más elementos que colores, repetimos los colores
        if ($count > count($colors)) {
            $colors = array_merge($colors, $colors);
        }
        
        return array_slice($colors, 0, $count);
    }
    
    public static function canView(): bool
    {
        return true; 
    }
}