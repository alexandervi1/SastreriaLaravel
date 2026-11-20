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
    public function processOrder(Usuario $usuario, string $metodoEntrega = 'retiro', ?string $direccionEnvio = null): Pedido
    {
        return DB::transaction(function () use ($usuario, $metodoEntrega, $direccionEnvio) {
            $items = Carrito::with(['producto', 'variante', 'tela', 'medida'])
                ->where('usuario_id', $usuario->usuario_id)
                ->get();

            if ($items->isEmpty()) {
                throw new RuntimeException('El carrito está vacío.');
            }

            if (! Configuracion::ventaPorTallaActiva() && $items->contains('tipo', 'talla')) {
                throw new RuntimeException('La venta por talla no está disponible.');
            }

            $idsTalla = $items->where('tipo', 'talla')->pluck('variante_id')->filter()->unique();

            if ($idsTalla->count() !== $items->where('tipo', 'talla')->count()) {
                throw new RuntimeException('Una de las tallas del carrito ya no está disponible. Vuelve a elegirla.');
            }

            // Bloquea las variantes para que dos checkouts simultáneos no sobrevendan.
            $variantes = ProductoVariante::whereIn('variante_id', $idsTalla)
                ->lockForUpdate()
                ->get()
                ->keyBy('variante_id');

            $subtotal = 0.0;
            $precios = [];

            foreach ($items as $item) {
                $producto = $item->producto;

                if (! $producto) {
                    throw new RuntimeException('Uno de los productos del carrito ya no está disponible.');
                }

                if ($item->tipo === 'medida') {
                    $this->validarMedida($item, $usuario);
                } else {
                    $variante = $variantes->get($item->variante_id);

                    if (! $variante || ! $variante->activo) {
                        throw new RuntimeException('Una de las tallas del carrito ya no está disponible. Vuelve a elegirla.');
                    }

                    if ($item->cantidad > $variante->stock) {
                        throw new RuntimeException(
                            "Stock insuficiente para {$producto->nombre} talla {$variante->talla}: disponible {$variante->stock}, solicitado {$item->cantidad}."
                        );
                    }
                }

                $precio = $this->pricing->precioLinea($producto, $item->tela, $item->tipo);
                $precios[$item->carrito_id] = $precio;
                $subtotal += $item->cantidad * $precio;
            }

            $totales = $this->pricing->desglosar($subtotal);

            $pedido = Pedido::create([
                'usuario_id' => $usuario->usuario_id,
                'subtotal' => $totales['subtotal'],
                'iva' => $totales['iva'],
                'iva_pct' => $totales['iva_pct'],
                'total' => $totales['total'],
                'estado' => 'Pendiente',
                'metodo_entrega' => $metodoEntrega,
                'direccion_envio' => $metodoEntrega === 'envio' ? $direccionEnvio : null,
            ]);

            // Secuencial a partir del ID insertado: seguro ante concurrencia.
            $serie = Configuracion::obtenerSerieComprobante();
            $pedido->numero_comprobante = $serie . '-' . str_pad((string) $pedido->pedido_id, 9, '0', STR_PAD_LEFT);
            $pedido->save();

            foreach ($items as $item) {
                $producto = $item->producto;
                $precio = $precios[$item->carrito_id];
                $variante = $item->tipo === 'talla' ? $variantes->get($item->variante_id) : null;

                PedidoLinea::create([
                    'pedido_id' => $pedido->pedido_id,
                    'producto_id' => $producto->producto_id,
                    'variante_id' => $variante?->variante_id,
                    'tipo' => $item->tipo,
                    'tela_id' => $item->tela_id,
                    'tela_nombre' => $item->tela?->nombre,
                    'medidas' => $item->tipo === 'medida' ? $item->medida?->comoArreglo() : null,
                    'requiere_cita' => $item->requiere_cita,
                    'notas' => $item->notas,
                    'producto_nombre' => $producto->nombre,
                    'talla' => $variante?->talla,
                    'color' => $variante?->color,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => round($item->cantidad * $precio, 2),
                ]);

                if ($variante) {
                    $variante->descontarStock((int) $item->cantidad);
                }
            }

            Carrito::where('usuario_id', $usuario->usuario_id)->delete();

            return $pedido->fresh();
        });
    }

    /**
     * Valida que el encargo a medida siga siendo realizable.
     *
     * @throws \RuntimeException
     */
    private function validarMedida(Carrito $item, Usuario $usuario): void
    {
        if (! $item->producto->vende_a_medida) {
            throw new RuntimeException("{$item->producto->nombre} ya no se confecciona a medida.");
        }

        if (! $item->tela || ! $item->tela->activo) {
            throw new RuntimeException('La tela elegida ya no está disponible. Elige otra.');
        }

        if ($item->requiere_cita) {
            return;
        }

        if (! $item->medida || $item->medida->usuario_id !== $usuario->usuario_id) {
            throw new RuntimeException('El perfil de medidas elegido ya no está disponible.');
        }

        if (! $item->medida->tieneMedidasMinimas()) {
            throw new RuntimeException('El perfil de medidas está incompleto. Completa las medidas o pide tomarlas en el taller.');
        }
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

            $lineas = PedidoLinea::where('pedido_id', $bloqueado->pedido_id)
                ->whereNotNull('variante_id')
                ->get();

            // Mismo bloqueo que processOrder: evita que un checkout simultáneo pise la reposición.
            $variantes = ProductoVariante::whereIn('variante_id', $lineas->pluck('variante_id')->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('variante_id');

            foreach ($lineas as $linea) {
                $variante = $variantes->get($linea->variante_id);

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

            $lineasMedida = PedidoLinea::where('pedido_id', $bloqueado->pedido_id)
                ->where('tipo', 'medida')
                ->count();

            if ($estado === Pedido::ESTADO_LISTO && $bloqueado->estado === Pedido::ESTADO_CONFIRMADO && $lineasMedida > 0) {
                throw new RuntimeException('Un pedido a medida debe pasar por «En confección» antes de estar listo.');
            }

            if ($estado === Pedido::ESTADO_CONFIRMADO && $bloqueado->fecha_entrega_estimada === null) {
                $dias = PedidoLinea::with('producto')
                    ->where('pedido_id', $bloqueado->pedido_id)
                    ->where('tipo', 'medida')
                    ->get()
                    ->max(fn (PedidoLinea $linea) => (int) ($linea->producto->dias_confeccion ?? 0));

                if ($dias > 0) {
                    $bloqueado->fecha_entrega_estimada = now()->addWeekdays($dias);
                }
            }

            $bloqueado->estado = $estado;
            $bloqueado->save();

            return $bloqueado->fresh();
        });
    }
}
