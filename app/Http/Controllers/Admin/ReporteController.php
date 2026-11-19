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
        $productos = DB::table('pedido_linea')
            ->join('producto', 'pedido_linea.producto_id', '=', 'producto.producto_id')
            ->leftJoin('categoria', 'producto.categoria_id', '=', 'categoria.categoria_id')
            ->select(
                'producto.producto_id',
                'producto.nombre',
                'categoria.nombre as categoria_nombre',
                DB::raw('SUM(pedido_linea.cantidad) as cantidad_vendida')
            )
            ->groupBy('producto.producto_id', 'producto.nombre', 'categoria.nombre')
            ->orderByDesc('cantidad_vendida')
            ->limit(10)
            ->get();

        return view('admin.reportes.mas_vendidos', compact('productos'));
    }

    public function bajoStock()
    {
        $umbral = Configuracion::obtenerUmbralBajoStock();

        $variantes = \App\Models\ProductoVariante::with('producto.categoria')
            ->bajoStock()
            ->orderBy('stock')
            ->get();

        return view('admin.reportes.bajo_stock', compact('variantes', 'umbral'));
    }

    public function stockPorCategoria()
    {
        $categorias = Categoria::withCount('productos')->get();

        $stockCategorias = DB::table('producto_variante')
            ->join('producto', 'producto_variante.producto_id', '=', 'producto.producto_id')
            ->join('categoria', 'producto.categoria_id', '=', 'categoria.categoria_id')
            ->where('producto_variante.activo', true)
            ->select('categoria.nombre', DB::raw('SUM(producto_variante.stock) as total_stock'))
            ->groupBy('categoria.categoria_id', 'categoria.nombre')
            ->get();

        return view('admin.reportes.stock_categoria', compact('categorias', 'stockCategorias'));
    }
}
