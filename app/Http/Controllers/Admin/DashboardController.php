<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Pedido;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Estadísticas Generales Clave
        $totalProductos = Producto::count();
        $totalClientes = Usuario::where('rol_id', 2)->count();
        $totalPedidos = Pedido::count();
        $ingresosTotales = Pedido::where('estado', '!=', 'Cancelado')->sum('total');

        // 2. Últimos Pedidos Recientes para acción rápida
        $pedidosRecientes = Pedido::with('usuario')
            ->orderBy('fecha_pedido', 'desc')
            ->take(5)
            ->get();

        // 3. Productos Críticos con Bajo Stock (umbral configurable)
        $umbral = Configuracion::obtenerUmbralBajoStock();

        $productosBajoStock = \App\Models\ProductoVariante::with('producto.categoria')
            ->bajoStock()
            ->orderBy('stock')
            ->take(5)
            ->get();

        // 4. Analítica Avanzada: Ingresos de los últimos 6 meses para Chart.js
        $desde = Carbon::now()->subMonths(5)->startOfMonth();

        $ventasMesesRaw = Pedido::where('estado', '!=', 'Cancelado')
            ->where('fecha_pedido', '>=', $desde)
            ->get(['fecha_pedido', 'total'])
            ->groupBy(fn ($pedido) => $pedido->fecha_pedido->format('Y-m'))
            ->map(fn ($grupo) => $grupo->sum(fn ($pedido) => (float) $pedido->total))
            ->all();

        // Variables para llenar la gráfica con ceros en meses sin ventas
        $labelsGrafico = [];
        $datosGrafico = [];
        $mesesNombres = [
            1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun',
            7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'
        ];

        // Iteramos los últimos 6 meses dinámicamente hasta el mes en curso
        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $labelsGrafico[] = $mesesNombres[$fecha->month];
            $datosGrafico[] = $ventasMesesRaw[$fecha->format('Y-m')] ?? 0;
        }

        return view('admin.dashboard', compact(
            'totalProductos', 'totalClientes', 'totalPedidos', 'ingresosTotales',
            'pedidosRecientes', 'productosBajoStock', 'labelsGrafico', 'datosGrafico', 'umbral'
        ));
    }
}
