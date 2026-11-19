<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('lineas')->where('usuario_id', auth()->id())->orderBy('fecha_pedido', 'desc')->get();
        return view('pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['lineas'])->where('usuario_id', auth()->id())->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }

    public function process(\App\Services\OrderService $orderService)
    {
        $data = request()->validate([
            'metodo_entrega'  => 'nullable|in:retiro,envio',
            'direccion_envio' => 'nullable|required_if:metodo_entrega,envio|string|max:255',
        ]);

        try {
            $pedido = $orderService->processOrder(
                auth()->user(),
                $data['metodo_entrega'] ?? 'retiro',
                $data['direccion_envio'] ?? null
            );

            return redirect()->route('pedidos.show', $pedido->pedido_id)
                ->with('success', 'Pedido procesado con éxito. Gracias por tu compra.');
        } catch (\PDOException $e) {
            // QueryException hereda de PDOException y a su vez de RuntimeException:
            // se atrapa antes para no filtrar SQL al cliente.
            report($e);

            return back()->with('error', 'No pudimos procesar tu pedido. Inténtalo de nuevo en unos minutos.');
        } catch (\RuntimeException $e) {
            // Errores de negocio pensados para el cliente (stock, carrito vacío…).
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No pudimos procesar tu pedido. Inténtalo de nuevo en unos minutos.');
        }
    }

    public function receipt($id)
    {
        $pedido = Pedido::with(['lineas', 'usuario'])->where('usuario_id', auth()->id())->findOrFail($id);

        return view('pedidos.receipt', compact('pedido'));
    }
}
