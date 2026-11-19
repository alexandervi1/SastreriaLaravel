<?php

namespace Tests\Unit;

use App\Models\Pedido;
use PHPUnit\Framework\TestCase;

class PedidoEstadoTest extends TestCase
{
    private function pedido(string $estado): Pedido
    {
        $pedido = new Pedido();
        $pedido->estado = $estado;

        return $pedido;
    }

    public function test_estados_declarados(): void
    {
        $this->assertSame(
            ['Pendiente', 'Confirmado', 'En confección', 'Listo para entrega', 'Entregado', 'Cancelado'],
            Pedido::ESTADOS
        );
    }

    public function test_pendiente_solo_pasa_a_confirmado_o_cancelado(): void
    {
        $pedido = $this->pedido(Pedido::ESTADO_PENDIENTE);

        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_CONFIRMADO));
        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_CANCELADO));
        $this->assertFalse($pedido->puedeCambiarA(Pedido::ESTADO_LISTO));
        $this->assertFalse($pedido->puedeCambiarA(Pedido::ESTADO_ENTREGADO));
    }

    public function test_confirmado_puede_pasar_a_confeccion_listo_o_cancelado(): void
    {
        $pedido = $this->pedido(Pedido::ESTADO_CONFIRMADO);

        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_EN_CONFECCION));
        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_LISTO));
        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_CANCELADO));
        $this->assertFalse($pedido->puedeCambiarA(Pedido::ESTADO_PENDIENTE));
    }

    public function test_confeccion_pasa_a_listo_y_listo_a_entregado(): void
    {
        $enConfeccion = $this->pedido(Pedido::ESTADO_EN_CONFECCION);
        $this->assertTrue($enConfeccion->puedeCambiarA(Pedido::ESTADO_LISTO));
        $this->assertFalse($enConfeccion->puedeCambiarA(Pedido::ESTADO_CANCELADO));

        $listo = $this->pedido(Pedido::ESTADO_LISTO);
        $this->assertTrue($listo->puedeCambiarA(Pedido::ESTADO_ENTREGADO));
    }

    public function test_entregado_y_cancelado_son_terminales(): void
    {
        foreach ([Pedido::ESTADO_ENTREGADO, Pedido::ESTADO_CANCELADO] as $estado) {
            $pedido = $this->pedido($estado);

            foreach (Pedido::ESTADOS as $destino) {
                $this->assertSame($destino === $estado, $pedido->puedeCambiarA($destino));
            }
        }
    }
}
