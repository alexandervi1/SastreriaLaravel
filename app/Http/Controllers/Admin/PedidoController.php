<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $estados = Pedido::ESTADOS;
        $estado = $request->query('estado');
        $estado = in_array($estado, $estados, true) ? $estado : null;

        $desde = null;
        if ($request->filled('desde')) {
            try {
                $desde = \Carbon\Carbon::parse((string) $request->query('desde'))->startOfDay();
            } catch (\Exception $e) {
                $desde = null;
            }
        }
        $desde = $desde ?? now()->subDays(90)->startOfDay();

        $pedidos = Pedido::with('usuario')
                    ->when($estado, fn ($query) => $query->where('estado', $estado))
                    ->where('fecha_pedido', '>=', $desde)
                    ->orderBy('fecha_pedido', 'desc')
                    ->get();

        // Mismo rango que el listado: los contadores de los filtros deben cuadrar con lo que se ve.
        $conteos = Pedido::selectRaw('estado, count(*) as pedidos')
                    ->where('fecha_pedido', '>=', $desde)
                    ->groupBy('estado')
                    ->pluck('pedidos', 'estado');

        return view('admin.pedidos.index', compact('pedidos', 'estado', 'estados', 'conteos', 'desde'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['usuario', 'lineas'])->findOrFail($id);
        return view('admin.pedidos.show', compact('pedido'));
    }

    public function ordenTrabajo($id)
    {
        $pedido = Pedido::with(['usuario', 'lineas.producto'])->findOrFail($id);

        return view('admin.pedidos.orden_trabajo', compact('pedido'));
    }

    public function update(Request $request, $id, OrderService $orderService)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(Pedido::ESTADOS)],
        ]);

        $pedido = Pedido::findOrFail($id);
        $nuevoEstado = $data['estado'];

        try {
            if ($nuevoEstado === Pedido::ESTADO_CANCELADO) {
                $orderService->cancelOrder($pedido);

                return redirect()->route('admin.pedidos.show', $pedido->pedido_id)
                                 ->with('success', 'Pedido cancelado y stock repuesto.');
            }

            $orderService->changeStatus($pedido, $nuevoEstado);

            return redirect()->route('admin.pedidos.show', $pedido->pedido_id)
                             ->with('success', 'Estado del pedido actualizado correctamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
