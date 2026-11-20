<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VarianteRequest;
use App\Models\Producto;
use App\Models\ProductoVariante;

class VarianteController extends Controller
{
    public function store(VarianteRequest $request, Producto $producto)
    {
        $data = $request->validated();
        $data['producto_id'] = $producto->producto_id;
        $data['activo'] = $request->boolean('activo', true);

        ProductoVariante::create($data);

        return redirect()
            ->route('admin.productos.edit', $producto->producto_id)
            ->with('success', 'Talla y color agregados.');
    }

    public function update(VarianteRequest $request, Producto $producto, ProductoVariante $variante)
    {
        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');

        $variante->update($data);

        return redirect()
            ->route('admin.productos.edit', $producto->producto_id)
            ->with('success', 'Variante actualizada.');
    }

    public function destroy(Producto $producto, ProductoVariante $variante)
    {
        if (\App\Models\PedidoLinea::where('variante_id', $variante->variante_id)->exists()) {
            $variante->update(['activo' => false]);

            return redirect()
                ->route('admin.productos.edit', $producto->producto_id)
                ->with('error', 'La talla ya forma parte de un pedido: se desactivó en lugar de borrarla.');
        }

        $variante->delete();

        return redirect()
            ->route('admin.productos.edit', $producto->producto_id)
            ->with('success', 'Variante eliminada.');
    }
}
