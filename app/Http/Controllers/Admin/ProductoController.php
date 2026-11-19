<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\PedidoProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['categoria'])->get();
        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        return view('admin.productos.create', compact('categorias'));
    }

    public function store(\App\Http\Requests\Admin\StoreProductoRequest $request)
    {
        $data = Arr::except($request->validated(), ['imagen']);

        if ($request->hasFile('imagen')) {
            $data['imagen_url'] = $this->guardarImagen($request);
        }

        Producto::create($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        return view('admin.productos.edit', compact('producto', 'categorias'));
    }

    public function update(\App\Http\Requests\Admin\UpdateProductoRequest $request, Producto $producto)
    {
        $data = Arr::except($request->validated(), ['imagen']);

        if ($request->hasFile('imagen')) {
            $this->borrarImagen($producto->imagen_url);
            $data['imagen_url'] = $this->guardarImagen($request);
        }

        $producto->update($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        if (PedidoProducto::where('producto_id', $producto->producto_id)->exists()) {
            return redirect()->route('admin.productos.index')
                ->with('error', 'No se puede eliminar un producto que ya forma parte de un pedido. Edita su stock o descártalo del catálogo.');
        }

        $imagen = $producto->imagen_url;

        try {
            $producto->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.productos.index')
                ->with('error', 'No se puede eliminar el producto porque tiene registros asociados.');
        }

        $this->borrarImagen($imagen);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    private function guardarImagen(Request $request): string
    {
        $file = $request->file('imagen');
        $filename = Str::uuid() . '.' . strtolower($file->extension());
        $file->move(public_path('img/productos'), $filename);

        return 'img/productos/' . $filename;
    }

    /**
     * Borra solo imágenes gestionadas por la aplicación (dentro de public/img).
     */
    private function borrarImagen(?string $url): void
    {
        if (!$url || !Str::startsWith($url, 'img/')) {
            return;
        }

        $base = realpath(public_path('img'));
        $ruta = realpath(public_path($url));

        if ($base === false || $ruta === false || !Str::startsWith($ruta, $base)) {
            return;
        }

        if (is_file($ruta)) {
            unlink($ruta);
        }
    }
}
