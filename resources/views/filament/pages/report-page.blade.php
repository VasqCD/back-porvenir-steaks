@vite(['resources/css/app.css', 'resources/js/app.js'])
<x-filament::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Filtros del reporte</x-slot>
            {{ $this->form }}
            <x-filament::button wire:click="$refresh" class="mt-4">
                Actualizar Reportes
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Estadísticas Generales</x-slot>
            
            @php
                $estadisticas = $this->getEstadisticasGenerales();
            @endphp
            
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Total de ventas</h3>
                    <p class="mt-1 text-3xl font-semibold text-primary-600">L {{ number_format($estadisticas['total_ventas'], 2) }}</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Total de pedidos</h3>
                    <p class="mt-1 text-3xl font-semibold text-primary-600">{{ $estadisticas['total_pedidos'] }}</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ticket promedio</h3>
                    <p class="mt-1 text-3xl font-semibold text-primary-600">L {{ number_format($estadisticas['ticket_promedio'], 2) }}</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nuevos clientes</h3>
                    <p class="mt-1 text-3xl font-semibold text-primary-600">{{ $estadisticas['nuevos_clientes'] }}</p>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Distribución por estado</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2">
                    @foreach(['pendiente', 'en_cocina', 'en_camino', 'entregado', 'cancelado'] as $estado)
                        @php
                            $cantidad = $estadisticas['distribucion_estados'][$estado] ?? 0;
                            $total = $estadisticas['total_pedidos'] ?: 1;
                            $porcentaje = round(($cantidad / $total) * 100);
                            
                            $colorClass = match($estado) {
                                'pendiente' => 'bg-yellow-500',
                                'en_cocina' => 'bg-blue-500',
                                'en_camino' => 'bg-indigo-500',
                                'entregado' => 'bg-green-500',
                                'cancelado' => 'bg-red-500',
                                default => 'bg-gray-400',
                            };
                        @endphp
                        
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">{{ ucfirst($estado) }}</span>
                                <span class="text-sm">{{ $porcentaje }}%</span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                <div class="{{ $colorClass }} h-2.5 rounded-full" style="width: {{ $porcentaje }}%"></div>
                            </div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $cantidad }} pedidos</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">Ventas por Periodo</x-slot>
                
                @php
                    $datosVentas = $this->getDatosVentasPorPeriodo();
                    $fechas = $datosVentas->pluck('fecha')->toJson();
                    $totales = $datosVentas->pluck('total')->toJson();
                    $cantidades = $datosVentas->pluck('cantidad')->toJson();
                @endphp
                
                @if($datosVentas->isEmpty())
                    <div class="flex flex-col items-center justify-center h-80 text-gray-500">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>No hay datos disponibles para el período seleccionado</p>
                    </div>
                @else
                    <div class="h-80">
                        <canvas id="ventasPorPeriodoChart" 
                            data-fechas="{{ $fechas }}"
                            data-totales="{{ $totales }}"
                            data-cantidades="{{ $cantidades }}">
                        </canvas>
                    </div>
                @endif
            </x-filament::section>
        </div>
        
        <x-filament::section>
            <x-slot name="heading">Top Productos Vendidos</x-slot>
            
            @php
                $topProductos = $this->getTopProductos();
            @endphp
            
            @if($topProductos->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>No hay datos disponibles para el período seleccionado</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cantidad Vendida</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Vendido</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($topProductos as $producto)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $producto->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $producto->cantidad_vendida }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">L {{ number_format($producto->total_vendido, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">Desempeño de Repartidores</x-slot>
            
            @php
                $desempenoRepartidores = $this->getDesempenoRepartidores();
            @endphp
            
            @if($desempenoRepartidores->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>No hay datos disponibles para el período seleccionado</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Repartidor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pedidos</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entregados</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cancelados</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">% Éxito</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Calificación</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($desempenoRepartidores as $repartidor)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $repartidor->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $repartidor->total_pedidos }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $repartidor->pedidos_entregados }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $repartidor->pedidos_cancelados }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @php
                                            $total = $repartidor->total_pedidos ?: 1;
                                            $exito = round(($repartidor->pedidos_entregados / $total) * 100);
                                        @endphp
                                        {{ $exito }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($repartidor->calificacion_promedio)
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= round($repartidor->calificacion_promedio))
                                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @endif
                                                @endfor
                                                <span class="ml-1 text-xs">({{ number_format($repartidor->calificacion_promedio, 1) }})</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">Sin calificar</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament::page>
    
    <!-- Cargar Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Inicializar gráficos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si Chart.js está disponible
            if (typeof Chart === 'undefined') {
                console.error('Chart.js no está cargado correctamente');
                return;
            }
            
            // Inicializar gráfico de ventas por período
            const ventasChart = document.getElementById('ventasPorPeriodoChart');
            if (ventasChart) {
                const fechas = JSON.parse(ventasChart.getAttribute('data-fechas'));
                const totales = JSON.parse(ventasChart.getAttribute('data-totales'));
                const cantidades = JSON.parse(ventasChart.getAttribute('data-cantidades'));
                
                const ctx = ventasChart.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [
                            {
                                label: 'Ventas (L)',
                                data: totales,
                                borderColor: 'rgb(72, 187, 120)',
                                backgroundColor: 'rgba(72, 187, 120, 0.2)',
                                yAxisID: 'y',
                                tension: 0.1
                            },
                            {
                                label: 'Pedidos',
                                data: cantidades,
                                borderColor: 'rgb(255, 117, 15)',
                                backgroundColor: 'rgba(255, 117, 15, 0.2)',
                                yAxisID: 'y1',
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Ventas (L)'
                                },
                                beginAtZero: true
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Pedidos'
                                },
                                grid: {
                                    drawOnChartArea: false
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Inicializar gráfico de ventas por categoría
            const categoriasChart = document.getElementById('ventasPorCategoriaChart');
            if (categoriasChart) {
                const categorias = JSON.parse(categoriasChart.getAttribute('data-categorias'));
                const totales = JSON.parse(categoriasChart.getAttribute('data-totales'));
                
                const ctx = categoriasChart.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: categorias,
                        datasets: [{
                            data: totales,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(255, 159, 64, 0.8)',
                                'rgba(153, 102, 255, 0.8)',
                                'rgba(255, 205, 86, 0.8)',
                                'rgba(201, 203, 207, 0.8)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += 'L ' + new Intl.NumberFormat().format(context.raw);
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
