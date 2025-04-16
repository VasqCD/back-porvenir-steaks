<?php

namespace App\Filament\Pages;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\User;
use App\Models\Repartidor;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\DetallePedido;
use Illuminate\Support\Facades\Cache;

class ReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    // Cambiamos el icono a uno que existe en Heroicons v2 que usa Filament 3.x
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $title = 'Reportes y Estadísticas';
    protected static ?string $slug = 'reportes';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.report-page';

    public $fechaInicio;
    public $fechaFin;
    public $categoria;
    public $estado;

    public function mount()
    {
        // Validar fechas
        try {
            $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->fechaFin = Carbon::now()->format('Y-m-d');
        } catch (\Exception $e) {
            // Manejar error de fechas inválidas
            $this->fechaInicio = Carbon::now()->subMonths(1)->format('Y-m-d');
            $this->fechaFin = Carbon::now()->format('Y-m-d');
            // Notificar al usuario
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('fechaInicio')
                                    ->label('Fecha Inicio')
                                    ->default(Carbon::now()->startOfMonth())
                                    ->maxDate(Carbon::now())
                                    ->reactive(),

                                DatePicker::make('fechaFin')
                                    ->label('Fecha Fin')
                                    ->default(Carbon::now())
                                    ->minDate(fn($get) => $get('fechaInicio'))
                                    ->maxDate(Carbon::now())
                                    ->reactive(),

                                Select::make('categoria')
                                    ->label('Categoría')
                                    ->options(Categoria::pluck('nombre', 'id'))
                                    ->placeholder('Todas las categorías')
                                    ->reactive(),

                                Select::make('estado')
                                    ->label('Estado de Pedidos')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'en_cocina' => 'En cocina',
                                        'en_camino' => 'En camino',
                                        'entregado' => 'Entregado',
                                        'cancelado' => 'Cancelado',
                                    ])
                                    ->placeholder('Todos los estados')
                                    ->reactive(),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public function getDatosVentasPorPeriodo()
    {
        $cacheKey = "ventas_periodo_{$this->fechaInicio}_{$this->fechaFin}_{$this->categoria}_{$this->estado}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $query = Pedido::query()
                ->whereBetween('fecha_pedido', [$this->fechaInicio, $this->fechaFin . ' 23:59:59']);

            if ($this->estado) {
                $query->where('estado', $this->estado);
            } else {
                $query->whereIn('estado', ['entregado', 'en_camino', 'en_cocina']);
            }

            if ($this->categoria) {
                $query->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('detalle_pedidos')
                        ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
                        ->whereRaw('detalle_pedidos.pedido_id = pedidos.id')
                        ->where('productos.categoria_id', $this->categoria);
                });
            }

            $ventasPorDia = $query->select(
                DB::raw('DATE(fecha_pedido) as fecha'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
                ->groupBy(DB::raw('DATE(fecha_pedido)'))
                ->orderBy('fecha', 'asc')
                ->get();

            return $ventasPorDia;
        });
    }

    public function getTopProductos()
    {
        $query = DetallePedido::query()
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
            ->whereBetween('pedidos.fecha_pedido', [$this->fechaInicio, $this->fechaFin . ' 23:59:59']);

        if ($this->estado) {
            $query->where('pedidos.estado', $this->estado);
        } else {
            $query->whereIn('pedidos.estado', ['entregado', 'en_camino', 'en_cocina']);
        }

        if ($this->categoria) {
            $query->where('productos.categoria_id', $this->categoria);
        }

        $topProductos = $query->select(
            'productos.nombre',
            DB::raw('SUM(detalle_pedidos.cantidad) as cantidad_vendida'),
            DB::raw('SUM(detalle_pedidos.subtotal) as total_vendido')
        )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('cantidad_vendida', 'desc')
            ->limit(10)
            ->get();

        return $topProductos;
    }

    public function getVentasPorCategorias()
    {
        $query = DetallePedido::query()
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereBetween('pedidos.fecha_pedido', [$this->fechaInicio, $this->fechaFin . ' 23:59:59']);

        if ($this->estado) {
            $query->where('pedidos.estado', $this->estado);
        } else {
            $query->whereIn('pedidos.estado', ['entregado', 'en_camino', 'en_cocina']);
        }

        if ($this->categoria) {
            $query->where('categorias.id', $this->categoria);
        }

        $ventasPorCategoria = $query->select(
            'categorias.nombre',
            DB::raw('SUM(detalle_pedidos.subtotal) as total_vendido'),
            DB::raw('COUNT(DISTINCT pedidos.id) as cantidad_pedidos')
        )
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderBy('total_vendido', 'desc')
            ->get();

        return $ventasPorCategoria;
    }

    public function getDesempenoRepartidores()
    {
        $query = Pedido::query()
            ->join('repartidores', 'pedidos.repartidor_id', '=', 'repartidores.id')
            ->join('users', 'repartidores.usuario_id', '=', 'users.id')
            ->whereBetween('pedidos.fecha_pedido', [$this->fechaInicio, $this->fechaFin . ' 23:59:59'])
            ->whereNotNull('pedidos.repartidor_id');

        if ($this->estado) {
            $query->where('pedidos.estado', $this->estado);
        }

        $desempenoRepartidores = $query->select(
            'users.name',
            DB::raw('COUNT(pedidos.id) as total_pedidos'),
            DB::raw('SUM(CASE WHEN pedidos.estado = "entregado" THEN 1 ELSE 0 END) as pedidos_entregados'),
            DB::raw('SUM(CASE WHEN pedidos.estado = "cancelado" THEN 1 ELSE 0 END) as pedidos_cancelados'),
            DB::raw('AVG(pedidos.calificacion) as calificacion_promedio')
        )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_pedidos', 'desc')
            ->get();

        return $desempenoRepartidores;
    }

    public function getEstadisticasGenerales()
    {
        $estadosFiltro = $this->estado
            ? [$this->estado]
            : ['entregado', 'en_camino', 'en_cocina'];

        // Obtener todos los pedidos del período con una sola consulta
        $pedidosBase = Pedido::query()
            ->whereBetween('fecha_pedido', [$this->fechaInicio, $this->fechaFin . ' 23:59:59']);

        // Obtener estadísticas principales
        $pedidosPrincipales = (clone $pedidosBase)
            ->when($this->estado, function ($query) {
                $query->where('estado', $this->estado);
            }, function ($query) use ($estadosFiltro) {
                $query->whereIn('estado', $estadosFiltro);
            })
            ->selectRaw('SUM(total) as total_ventas, COUNT(*) as total_pedidos')
            ->first();

        // Distribución por estado en una sola consulta
        $distribucionEstados = (clone $pedidosBase)
            ->select('estado', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('estado')
            ->pluck('cantidad', 'estado')
            ->toArray();

        // Nuevos clientes en el periodo
        $nuevosClientes = User::query()
            ->where('rol', 'cliente')
            ->whereBetween('fecha_registro', [$this->fechaInicio, $this->fechaFin . ' 23:59:59'])
            ->count();

        // Cálculo del ticket promedio
        $totalVentas = $pedidosPrincipales->total_ventas ?? 0;
        $totalPedidos = $pedidosPrincipales->total_pedidos ?? 0;
        $ticketPromedio = $totalPedidos > 0 ? $totalVentas / $totalPedidos : 0;

        return [
            'total_ventas' => $totalVentas,
            'total_pedidos' => $totalPedidos,
            'ticket_promedio' => $ticketPromedio,
            'distribucion_estados' => $distribucionEstados,
            'nuevos_clientes' => $nuevosClientes,
        ];
    }

    protected function getFormModel(): string
    {
        return 'ReportPage';
    }
}
