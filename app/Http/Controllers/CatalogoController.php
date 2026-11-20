<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $publicoActual = array_key_exists((string) $request->query('publico'), Producto::PUBLICOS)
            ? (string) $request->query('publico')
            : null;

        $tipoActual = array_key_exists((string) $request->query('tipo_prenda'), Producto::TIPOS_PRENDA)
            ? (string) $request->query('tipo_prenda')
            : null;

        $productos = Producto::query()
            ->where('activo', true)
            ->when($publicoActual, fn ($query) => $query->where('publico', $publicoActual))
            ->when($tipoActual, fn ($query) => $query->where('tipo_prenda', $tipoActual))
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = (string) $request->query('buscar');
                $query->where(fn ($sub) => $sub->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%"));
            })
            ->orderBy('nombre')
            ->get();

        return view('catalogo.index', compact('productos', 'publicoActual', 'tipoActual'));
    }
}
