<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Tela;

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

        $publicoActual = array_key_exists((string) $request->query('publico'), Producto::PUBLICOS)
            ? (string) $request->query('publico')
            : null;

        $tipoActual = array_key_exists((string) $request->query('tipo_prenda'), Producto::TIPOS_PRENDA)
            ? (string) $request->query('tipo_prenda')
            : null;

        if ($publicoActual) {
            $query->where('publico', $publicoActual);
        }

        if ($tipoActual) {
            $query->where('tipo_prenda', $tipoActual);
        }
        
        if (!$request->has('categoria') && !$request->has('buscar') && !$publicoActual && !$tipoActual) {
            // Si no hay filtros, mostrar solo hasta 8 productos como antes
            $query->orderBy('nombre')->take(8);
        }
        
        $productos = $query->get();

        $destacados = Producto::with('categoria')
            ->where('activo', true)
            ->where('destacado', true)
            ->orderBy('nombre')
            ->take(4)
            ->get();

        $telas = Tela::where('activo', true)->orderBy('nombre')->get();

        return view('home', compact('productos', 'categorias', 'categoria_actual', 'destacados', 'publicoActual', 'tipoActual', 'telas'));
    }

    public function quienesSomos()
    {
        return view('pages.quienes-somos');
    }

    public function ayuda()
    {
        return view('pages.ayuda');
    }

    public function politicas()
    {
        return view('pages.politicas');
    }

    public function terminos()
    {
        return view('pages.terminos');
    }
}
