<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Producto;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index(PricingService $pricing)
    {
        $items = Carrito::with('producto.categoria')
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
            'producto_id' => 'required|exists:producto,producto_id',
            'cantidad'    => 'nullable|integer|min:1',
        ]);

        $producto = Producto::findOrFail($data['producto_id']);
        $cantidad = (int) ($data['cantidad'] ?? 1);
        $usuario_id = auth()->user()->usuario_id;

        $item = Carrito::firstOrNew([
            'usuario_id'  => $usuario_id,
            'producto_id' => $producto->producto_id,
        ]);

        $nuevaCantidad = (int) $item->cantidad + $cantidad;

        if ($nuevaCantidad > $producto->stock) {
            return back()->with(
                'error',
                "No hay stock suficiente de {$producto->nombre}: disponible {$producto->stock}."
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

        $item = Carrito::with('producto')
            ->where('carrito_id', $data['carrito_id'])
            ->where('usuario_id', auth()->user()->usuario_id)
            ->firstOrFail();

        if ($data['cantidad'] <= 0) {
            $item->delete();

            return back()->with('success', 'Carrito actualizado.');
        }

        if ($data['cantidad'] > $item->producto->stock) {
            return back()->with(
                'error',
                "No hay stock suficiente de {$item->producto->nombre}: disponible {$item->producto->stock}."
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
