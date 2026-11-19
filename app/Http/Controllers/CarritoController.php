<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\MedidaCliente;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Tela;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index(PricingService $pricing)
    {
        $items = Carrito::with('producto.categoria', 'variante', 'tela', 'medida')
            ->where('usuario_id', auth()->id())
            ->get();

        $precios = $items->mapWithKeys(fn (Carrito $item) => [
            $item->carrito_id => $pricing->precioLinea($item->producto, $item->tela, $item->tipo),
        ]);

        $subtotales = $items->mapWithKeys(fn (Carrito $item) => [
            $item->carrito_id => round($item->cantidad * (float) $precios[$item->carrito_id], 2),
        ]);

        $totales = $pricing->desglosar((float) $subtotales->sum());

        return view('carrito.index', compact('items', 'precios', 'subtotales', 'totales'));
    }

    public function agregar(Request $request)
    {
        $data = $request->validate([
            'tipo'        => 'nullable|in:talla,medida',
            'producto_id' => 'nullable|exists:producto,producto_id',
            'variante_id' => 'nullable|exists:producto_variante,variante_id',
            'tela_id'     => 'nullable|exists:tela,tela_id',
            'medida_id'   => 'nullable',
            'notas'       => 'nullable|string|max:500',
            'cantidad'    => 'nullable|integer|min:1',
        ]);

        $tipo = $data['tipo'] ?? 'talla';

        if ($tipo === 'medida') {
            $producto = Producto::find($data['producto_id'] ?? null);

            if (! $producto) {
                return back()->with('error', 'Elige un modelo válido.');
            }

            return $this->agregarMedida($request, $producto);
        }

        return $this->agregarTalla($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function agregarTalla(array $data)
    {
        $variante = ProductoVariante::with('producto')->find($data['variante_id'] ?? null);

        if (! $variante || ! $variante->producto) {
            return back()->with('error', 'Elige una talla válida.');
        }

        $producto = $variante->producto;

        if (! $producto->vende_por_talla) {
            return back()->with('error', 'Este modelo no se vende por talla.');
        }

        if (! $variante->activo) {
            return back()->with('error', 'Esa talla ya no está disponible.');
        }

        $cantidad = (int) ($data['cantidad'] ?? 1);

        $item = Carrito::firstOrNew([
            'usuario_id'  => auth()->id(),
            'variante_id' => $variante->variante_id,
            'tipo'        => 'talla',
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

    private function agregarMedida(Request $request, Producto $producto)
    {
        if (! $producto->vende_a_medida) {
            return back()->with('error', 'Este modelo no se confecciona a medida.');
        }

        $data = $request->validate([
            'tela_id'   => 'required|exists:tela,tela_id',
            'medida_id' => 'required|string',
            'notas'     => 'nullable|string|max:500',
        ]);

        $tela = Tela::whereKey($data['tela_id'])->where('activo', true)->first();

        if (! $tela) {
            return back()->with('error', 'La tela elegida ya no está disponible.');
        }

        $entrada = $data['medida_id'];
        $requiereCita = $entrada === 'taller';
        $medida = null;

        if (! $requiereCita) {
            $medida = MedidaCliente::whereKey($entrada)
                ->where('usuario_id', auth()->id())
                ->first();

            if (! $medida) {
                return back()->with('error', 'Ese perfil de medidas no existe o no es tuyo.');
            }

            if (! $medida->tieneMedidasMinimas()) {
                return back()->with('error', 'El perfil elegido no tiene las medidas mínimas. Complétalo o pide tomar medidas en el taller.');
            }
        }

        Carrito::create([
            'usuario_id'   => auth()->id(),
            'producto_id'  => $producto->producto_id,
            'tipo'         => 'medida',
            'tela_id'      => $tela->tela_id,
            'medida_id'    => $medida?->medida_id,
            'requiere_cita' => $requiereCita,
            'notas'        => $data['notas'] ?? null,
            'cantidad'     => 1,
        ]);

        return back()->with('success', 'Encargo a medida agregado al carrito.');
    }

    public function actualizar(Request $request)
    {
        $data = $request->validate([
            'carrito_id' => 'required|exists:carrito,carrito_id',
            'cantidad'   => 'required|integer|min:0',
        ]);

        $item = Carrito::with('producto', 'variante')
            ->where('carrito_id', $data['carrito_id'])
            ->where('usuario_id', auth()->id())
            ->firstOrFail();

        if ($data['cantidad'] <= 0) {
            $item->delete();

            return back()->with('success', 'Carrito actualizado.');
        }

        if ($item->tipo === 'medida') {
            return back()->with('error', 'Los encargos a medida son de una unidad. Elimina la línea para quitarla.');
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
            ->where('usuario_id', auth()->id())
            ->delete();

        return back()->with('success', 'Producto eliminado del carrito.');
    }
}
