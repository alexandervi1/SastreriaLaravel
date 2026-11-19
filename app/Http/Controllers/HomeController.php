<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $categorias = Categoria::all();
        $query = Producto::with(['categoria']);
        
        $categoria_actual = null;
        if ($request->has('categoria') && !empty($request->categoria)) {
            $categoria_actual = Categoria::find($request->categoria);
            if ($categoria_actual) {
                $query->where('categoria_id', $categoria_actual->categoria_id);
            }
        }
        
        if ($request->has('buscar') && !empty($request->buscar)) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }
        
        if (!$request->has('categoria') && !$request->has('buscar')) {
            // Si no hay filtros, mostrar solo hasta 8 productos como antes
            $query->orderBy('nombre')->take(8);
        }
        
        $productos = $query->get();
        return view('home', compact('productos', 'categorias', 'categoria_actual'));
    }

    public function quienesSomos()
    {
        return view('pages.quienes-somos');
    }

    public function ayuda()
    {
        return view('pages.ayuda');
    }
}
