<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\ProductoVariante;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index(PricingService $pricing)
    {
        $items = Carrito::with('producto.categoria', 'variante')
            ->where('usuario_id', auth()->id())
            ->get();

        $subtotal = $items->sum(function ($item) {
            return $item->cantidad * (float) $item->producto->precio;
        });

        $totales = $pricing->desglosar($subtotal);

        return view('carrito.index', compact('items', 'totales'));
    }

    public function agregar(Request $request)
    {
        $data = $request->validate([
            'variante_id' => 'required|exists:producto_variante,variante_id',
            'cantidad'    => 'nullable|integer|min:1',
        ]);

        $variante = ProductoVariante::with('producto')->findOrFail($data['variante_id']);
        $producto = $variante->producto;
        $cantidad = (int) ($data['cantidad'] ?? 1);

        if (! $variante->activo) {
            return back()->with('error', 'Esa talla ya no está disponible.');
        }

        $item = Carrito::firstOrNew([
            'usuario_id'  => auth()->user()->usuario_id,
            'variante_id' => $variante->variante_id,
        ]);

        $item->producto_id = $producto->producto_id;
        $nuevaCantidad = (int) $item->cantidad + $cantidad;

        if ($nuevaCantidad > $variante->stock) {
            return back()->with(
                'error',
                "No hay stock suficiente de {$producto->nombre} talla {$variante->talla}: disponible {$variante->stock}."
            );
        }

        $item->cantidad = $nuevaCantidad;
        $item->save();

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function actualizar(Request $request)
    {
        $data = $request->validate([
            'carrito_id' => 'required|exists:carrito,carrito_id',
            'cantidad'   => 'required|integer|min:0',
        ]);

        $item = Carrito::with('producto', 'variante')
            ->where('carrito_id', $data['carrito_id'])
            ->where('usuario_id', auth()->user()->usuario_id)
            ->firstOrFail();

        if ($data['cantidad'] <= 0) {
            $item->delete();

            return back()->with('success', 'Carrito actualizado.');
        }

        if (! $item->variante || ! $item->variante->activo) {
            return back()->with('error', 'Esa talla ya no está disponible. Elimina la línea y elige otra.');
        }

        if ($data['cantidad'] > $item->variante->stock) {
            return back()->with(
                'error',
                "No hay stock suficiente de {$item->producto->nombre} talla {$item->variante->talla}: disponible {$item->variante->stock}."
            );
        }

        $item->cantidad = $data['cantidad'];
        $item->save();

        return back()->with('success', 'Carrito actualizado.');
    }

    public function eliminar(Request $request)
    {
        $data = $request->validate([
            'carrito_id' => 'required|exists:carrito,carrito_id',
        ]);

        Carrito::where('carrito_id', $data['carrito_id'])
            ->where('usuario_id', auth()->user()->usuario_id)
            ->delete();

        return back()->with('success', 'Producto eliminado del carrito.');
    }
}
