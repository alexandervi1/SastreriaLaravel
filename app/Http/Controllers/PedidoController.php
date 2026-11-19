<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('productos')->where('usuario_id', auth()->id())->orderBy('fecha_pedido', 'desc')->get();
        return view('pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['productos'])->where('usuario_id', auth()->id())->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }

    public function process(\App\Services\OrderService $orderService)
    {
        try {
            $pedido = $orderService->processOrder(auth()->user());

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
        $pedido = Pedido::with(['productos', 'usuario'])->where('usuario_id', auth()->id())->findOrFail($id);

        return view('pedidos.receipt', compact('pedido'));
    }
}
