<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\MedidaCliente;
use App\Models\Producto;
use App\Models\Tela;
use App\Services\OrderService;

class ProductoPublicoController extends Controller
{
    public function show(Producto $producto, OrderService $orders)
    {
        $ventaPorTalla = Configuracion::ventaPorTallaActiva() && $producto->vende_por_talla;

        $variantes = $ventaPorTalla
            ? $producto->variantes()
                ->where('activo', true)
                ->where('stock', '>', 0)
                ->orderBy('talla')
                ->orderBy('color')
                ->get()
            : collect();

        $imagenes = $producto->imagenes()->get();

        $telas = $producto->vende_a_medida
            ? Tela::where('activo', true)->orderBy('nombre')->get()
            : collect();

        $medidas = auth()->check() && auth()->user()->esCliente()
            ? MedidaCliente::where('usuario_id', auth()->id())->orderBy('nombre')->get()
            : collect();

        $fechaEstimada = $producto->vende_a_medida && $producto->dias_confeccion
            ? $orders->fechaEntregaEstimada((int) $producto->dias_confeccion)
            : null;

        return view('productos.show', compact('producto', 'variantes', 'telas', 'medidas', 'fechaEstimada', 'imagenes', 'ventaPorTalla'));
    }
}
