<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductoImagenController extends Controller
{
    private const MAXIMO = 8;

    public function store(Request $request, Producto $producto)
    {
        $request->validate([
            'imagenes'   => 'required|array|max:8',
            'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000',
            'alt'        => 'nullable|string|max:160',
        ]);

        $actuales = $producto->imagenes()->count();
        $nuevas = count($request->file('imagenes', []));

        if ($actuales + $nuevas > self::MAXIMO) {
            return back()->with('error', 'Un modelo admite como máximo 8 imágenes.');
        }

        $orden = (int) $producto->imagenes()->max('orden');

        foreach ($request->file('imagenes') as $archivo) {
            $nombre = Str::uuid() . '.' . strtolower($archivo->extension());
            $archivo->move(public_path('img/productos'), $nombre);

            $producto->imagenes()->create([
                'url' => 'img/productos/' . $nombre,
                'orden' => ++$orden,
                'alt' => $request->input('alt') ?: $producto->nombre,
            ]);
        }

        return back()->with('success', 'Imágenes agregadas a la galería.');
    }

    public function update(Request $request, Producto $producto, ProductoImagen $imagen)
    {
        abort_unless($imagen->producto_id === $producto->producto_id, 404);

        $data = $request->validate([
            'alt' => 'required|string|max:160',
        ]);

        $imagen->update($data);

        return back()->with('success', 'Texto alternativo actualizado.');
    }

    public function mover(Producto $producto, ProductoImagen $imagen, string $direccion)
    {
        abort_unless($imagen->producto_id === $producto->producto_id, 404);

        $ordenadas = $producto->imagenes()->get();
        $indice = $ordenadas->search(fn (ProductoImagen $i) => $i->imagen_id === $imagen->imagen_id);

        if ($indice === false) {
            abort(404);
        }

        $destino = $direccion === 'subir' ? $indice - 1 : $indice + 1;

        if (! isset($ordenadas[$destino])) {
            return back();
        }

        $otra = $ordenadas[$destino];
        $ordenImagen = $imagen->orden;
        $imagen->update(['orden' => $otra->orden]);
        $otra->update(['orden' => $ordenImagen]);

        return back()->with('success', 'Galería reordenada.');
    }

    public function portada(Producto $producto, ProductoImagen $imagen)
    {
        abort_unless($imagen->producto_id === $producto->producto_id, 404);

        $producto->update(['imagen_url' => $imagen->url]);

        return back()->with('success', 'Portada actualizada.');
    }

    public function destroy(Producto $producto, ProductoImagen $imagen)
    {
        abort_unless($imagen->producto_id === $producto->producto_id, 404);

        $imagen->delete();

        return back()->with('success', 'Imagen eliminada de la galería.');
    }
}
