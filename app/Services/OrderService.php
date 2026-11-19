<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\PedidoLinea;
use App\Models\ProductoVariante;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(private PricingService $pricing)
    {
    }

    /**
     * Procesa la creación de un pedido a partir del carrito del usuario.
     *
     * Todo el trabajo ocurre en una transacción: o se crea el pedido completo
     * con su stock descontado, o no cambia nada.
     *
     * @throws \RuntimeException con un mensaje apto para el cliente.
     */
    public function processOrder(Usuario $usuario): Pedido
    {
        return DB::transaction(function () use ($usuario) {
            $items = Carrito::with('producto')->where('usuario_id', $usuario->usuario_id)->get();

            if ($items->isEmpty()) {
                throw new RuntimeException('El carrito está vacío.');
            }

            $ids = $items->pluck('variante_id')->filter()->unique();

            if ($ids->count() !== $items->count()) {
                throw new RuntimeException('Una de las tallas del carrito ya no está disponible. Vuelve a elegirla.');
            }

            // Bloquea las variantes para que dos checkouts simultáneos no sobrevendan.
            $variantes = ProductoVariante::whereIn('variante_id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('variante_id');

            $subtotal = 0.0;

            foreach ($items as $item) {
                $variante = $variantes->get($item->variante_id);
                $producto = $item->producto;

                if (! $variante || ! $producto || ! $variante->activo) {
                    throw new RuntimeException('Una de las tallas del carrito ya no está disponible. Vuelve a elegirla.');
                }

                if ($item->cantidad > $variante->stock) {
                    throw new RuntimeException(
                        "Stock insuficiente para {$producto->nombre} talla {$variante->talla}: disponible {$variante->stock}, solicitado {$item->cantidad}."
                    );
                }

                $subtotal += $item->cantidad * (float) $producto->precio;
            }

            $totales = $this->pricing->desglosar($subtotal);

            $pedido = Pedido::create([
                'usuario_id' => $usuario->usuario_id,
                'subtotal' => $totales['subtotal'],
                'iva' => $totales['iva'],
                'iva_pct' => $totales['iva_pct'],
                'total' => $totales['total'],
                'estado' => 'Pendiente',
            ]);

            // Secuencial a partir del ID insertado: seguro ante concurrencia.
            $serie = Configuracion::obtenerSerieComprobante();
            $pedido->numero_comprobante = $serie . '-' . str_pad((string) $pedido->pedido_id, 9, '0', STR_PAD_LEFT);
            $pedido->save();

            foreach ($items as $item) {
                $variante = $variantes->get($item->variante_id);
                $producto = $item->producto;
                $precio = (float) $producto->precio;

                PedidoLinea::create([
                    'pedido_id' => $pedido->pedido_id,
                    'producto_id' => $producto->producto_id,
                    'variante_id' => $variante->variante_id,
                    'producto_nombre' => $producto->nombre,
                    'talla' => $variante->talla,
                    'color' => $variante->color,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => round($item->cantidad * $precio, 2),
                ]);

                $variante->descontarStock((int) $item->cantidad);
            }

            Carrito::where('usuario_id', $usuario->usuario_id)->delete();

            return $pedido->fresh();
        });
    }

    /**
     * Cancela un pedido reponiendo el stock de sus líneas.
     *
     * @throws \RuntimeException con un mensaje apto para el cliente.
     */
    public function cancelOrder(Pedido $pedido): Pedido
    {
        return DB::transaction(function () use ($pedido) {
            $bloqueado = Pedido::whereKey($pedido->pedido_id)->lockForUpdate()->firstOrFail();

            if ($bloqueado->estado === Pedido::ESTADO_CANCELADO) {
                throw new RuntimeException('El pedido ya está cancelado.');
            }

            if (! $bloqueado->puedeCambiarA(Pedido::ESTADO_CANCELADO)) {
                throw new RuntimeException("No se puede cancelar un pedido en estado {$bloqueado->estado}.");
            }

            $lineas = PedidoLinea::where('pedido_id', $bloqueado->pedido_id)->get();

            // Mismo bloqueo que processOrder: evita que un checkout simultáneo pise la reposición.
            $variantes = ProductoVariante::whereIn('variante_id', $lineas->pluck('variante_id')->filter()->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('variante_id');

            foreach ($lineas as $linea) {
                $variante = $linea->variante_id ? $variantes->get($linea->variante_id) : null;

                // La variante pudo borrarse; la cancelación no debe fallar por eso.
                if ($variante) {
                    $variante->reponerStock((int) $linea->cantidad);
                }
            }

            $bloqueado->estado = Pedido::ESTADO_CANCELADO;
            $bloqueado->save();

            return $bloqueado->fresh();
        });
    }

    /**
     * Cambia el estado de un pedido a otra transición distinta de la cancelación.
     *
     * @throws \RuntimeException con un mensaje apto para el cliente.
     */
    public function changeStatus(Pedido $pedido, string $estado): Pedido
    {
        return DB::transaction(function () use ($pedido, $estado) {
            $bloqueado = Pedido::whereKey($pedido->pedido_id)->lockForUpdate()->firstOrFail();

            if (! in_array($estado, Pedido::ESTADOS, true)) {
                throw new RuntimeException("Estado desconocido: {$estado}.");
            }

            if ($estado === Pedido::ESTADO_CANCELADO) {
                throw new RuntimeException('La cancelación se procesa con cancelOrder.');
            }

            if (! $bloqueado->puedeCambiarA($estado)) {
                throw new RuntimeException("No se puede pasar un pedido de {$bloqueado->estado} a {$estado}.");
            }

            $bloqueado->estado = $estado;
            $bloqueado->save();

            return $bloqueado->fresh();
        });
    }
}
