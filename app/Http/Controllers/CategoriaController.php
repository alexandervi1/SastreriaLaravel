<?php

namespace App\Http\Controllers;

use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function show($id)
    {
        $categoria = Categoria::with(['productos'])->findOrFail($id);
        $categorias = Categoria::all();
        return view('categorias.show', compact('categoria', 'categorias'));
    }
}
