<?php

namespace App\Http\Controllers;

use App\Models\Producto;

class ProductoPublicoController extends Controller
{
    public function show($id)
    {
        $producto = Producto::with(['categoria'])->findOrFail($id);
        return view('productos.show', compact('producto'));
    }
}
