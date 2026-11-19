<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function ventas()
    {
        $anio = (int) now()->year;

        $ventasMes = Pedido::whereYear('fecha_pedido', $anio)
            ->where('estado', '!=', 'Cancelado')
            ->get(['fecha_pedido', 'total'])
            ->groupBy(fn ($pedido) => $pedido->fecha_pedido->month)
            ->sortKeys()
            ->map(fn ($grupo, $mes) => (object) [
                'mes' => $mes,
                'total_pedidos' => $grupo->count(),
                'total_ingresos' => $grupo->sum(fn ($pedido) => (float) $pedido->total),
            ])
            ->values();

        $pedidosRecientes = Pedido::with('usuario')->orderBy('fecha_pedido', 'desc')->take(10)->get();

        return view('admin.reportes.ventas', compact('ventasMes', 'pedidosRecientes'));
    }

    public function masVendidos()
    {
        $productos = Producto::with(['categoria'])
        ->withCount(['pedidos as cantidad_vendida' => function($query) {
            $query->select(DB::raw('SUM(pedidoproducto.cantidad)'));
        }])
        ->orderBy('cantidad_vendida', 'desc')
        ->take(10)
        ->get();

        return view('admin.reportes.mas_vendidos', compact('productos'));
    }

    public function bajoStock()
    {
        $umbral = Configuracion::obtenerUmbralBajoStock();

        $productos = Producto::bajoStock()->get();

        return view('admin.reportes.bajo_stock', compact('productos', 'umbral'));
    }

    public function stockPorCategoria()
    {
        $categorias = Categoria::withCount('productos')->get();
        
        $stockCategorias = DB::table('producto')
            ->join('categoria', 'producto.categoria_id', '=', 'categoria.categoria_id')
            ->select('categoria.nombre', DB::raw('SUM(producto.stock) as total_stock'))
            ->groupBy('categoria.categoria_id', 'categoria.nombre')
            ->get();

        return view('admin.reportes.stock_categoria', compact('categorias', 'stockCategorias'));
    }
}
