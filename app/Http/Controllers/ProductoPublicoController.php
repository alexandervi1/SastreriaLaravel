<?php

namespace App\Http\Controllers;

use App\Models\Producto;

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

        return view('productos.show', compact('producto', 'variantes'));
    }
}
