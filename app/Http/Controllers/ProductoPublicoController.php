<?php

namespace App\Http\Controllers;

use App\Models\MedidaCliente;
use App\Models\Producto;
use App\Models\Tela;
use Carbon\Carbon;

class ProductoPublicoController extends Controller
{
    public function show(Producto $producto)
    {
        $variantes = $producto->variantes()
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->orderBy('talla')
            ->orderBy('color')
            ->get();

        $imagenes = $producto->imagenes()->get();

        $telas = $producto->vende_a_medida
            ? Tela::where('activo', true)->orderBy('nombre')->get()
            : collect();

        $medidas = auth()->check() && auth()->user()->esCliente()
            ? MedidaCliente::where('usuario_id', auth()->id())->orderBy('nombre')->get()
            : collect();

        $fechaEstimada = $producto->vende_a_medida && $producto->dias_confeccion
            ? Carbon::now()->addWeekdays((int) $producto->dias_confeccion)
            : null;

        return view('productos.show', compact('producto', 'variantes', 'telas', 'medidas', 'fechaEstimada', 'imagenes'));
    }
}
