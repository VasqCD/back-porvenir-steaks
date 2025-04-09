<?php

namespace App\Filament\Widgets;

use App\Models\Categoria;
use App\Models\Producto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CategoriasOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCategorias = Categoria::count();
        $totalProductos = Producto::count();
        $categoriasConProductos = Categoria::whereHas('productos')->count();
        $productosPorCategoria = $totalCategorias > 0 ? round($totalProductos / $totalCategorias, 1) : 0;

        return [
            Stat::make('Total de categorías', $totalCategorias)
                ->description('Categorías disponibles')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
                
            Stat::make('Total de productos', $totalProductos)
                ->description('Productos catalogados')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
                
            Stat::make('Categorías con productos', $categoriasConProductos)
                ->description('De un total de ' . $totalCategorias . ' categorías')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
                
            Stat::make('Promedio por categoría', $productosPorCategoria)
                ->description('Productos por categoría')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
        ];
    }
}