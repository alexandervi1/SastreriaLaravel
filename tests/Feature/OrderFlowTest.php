<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);

        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Pescados']]);

        Configuracion::create(['clave' => 'iva_actual', 'valor' => '15']);

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
        ]);
    }

    private function producto(array $overrides = []): Producto
    {
        return Producto::create(array_merge([
            'nombre' => 'Albacora',
            'descripcion' => 'Fresca',
            'precio' => 10,
            'stock' => 100,
            'categoria_id' => 1,
        ], $overrides));
    }

    public function test_process_order_persists_subtotal_iva_and_total(): void
    {
        $producto = $this->producto();
        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'cantidad' => 2,
        ]);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->assertEquals(20.00, (float) $pedido->subtotal);
        $this->assertEquals(3.00, (float) $pedido->iva);
        $this->assertEquals(23.00, (float) $pedido->total);
        $this->assertEquals('Pendiente', $pedido->estado);
    }

    public function test_process_order_snapshots_unit_price(): void
    {
        $producto = $this->producto();
        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'cantidad' => 2,
        ]);

        $pedido = app(OrderService::class)->processOrder($this->cliente);
        $producto->update(['precio' => 99]);

        $linea = $pedido->fresh()->productos->first();
        $this->assertEquals(10.00, (float) $linea->pivot->precio_unitario);
        $this->assertEquals(20.00, (float) $linea->pivot->subtotal);
        $this->assertEquals(20.00, (float) $pedido->fresh()->subtotal);
        $this->assertEquals(23.00, (float) $pedido->fresh()->total);
    }

    public function test_process_order_rejects_quantity_above_stock(): void
    {
        $producto = $this->producto(['stock' => 1]);
        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'cantidad' => 2,
        ]);

        try {
            app(OrderService::class)->processOrder($this->cliente);
            $this->fail('Se esperaba un rechazo por stock insuficiente.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('stock', mb_strtolower($e->getMessage()));
        }

        $this->assertDatabaseCount('pedido', 0);
        $this->assertEquals(1, $producto->fresh()->stock);
        $this->assertDatabaseCount('carrito', 1);
    }

    public function test_process_order_rolls_back_every_item_when_one_is_short(): void
    {
        $suficiente = $this->producto(['nombre' => 'Suficiente', 'stock' => 10]);
        $corto = $this->producto(['nombre' => 'Corto', 'stock' => 1]);

        Carrito::insert([
            [
                'usuario_id' => $this->cliente->usuario_id,
                'producto_id' => $suficiente->producto_id,
                'cantidad' => 2,
            ],
            [
                'usuario_id' => $this->cliente->usuario_id,
                'producto_id' => $corto->producto_id,
                'cantidad' => 5,
            ],
        ]);

        try {
            app(OrderService::class)->processOrder($this->cliente);
            $this->fail('Se esperaba un rechazo por stock insuficiente.');
        } catch (\RuntimeException $e) {
            // esperado
        }

        $this->assertDatabaseCount('pedido', 0);
        $this->assertDatabaseCount('pedidoproducto', 0);
        $this->assertEquals(10, $suficiente->fresh()->stock);
        $this->assertEquals(1, $corto->fresh()->stock);
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $producto = $this->producto(['stock' => 3]);

        $response = $this->actingAs($this->cliente)->post(route('carrito.agregar'), [
            'producto_id' => $producto->producto_id,
            'cantidad' => 5,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('carrito', 0);
    }

    public function test_cart_accepts_quantity_within_stock(): void
    {
        $producto = $this->producto(['stock' => 3]);

        $this->actingAs($this->cliente)->post(route('carrito.agregar'), [
            'producto_id' => $producto->producto_id,
            'cantidad' => 2,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('carrito', [
            'producto_id' => $producto->producto_id,
            'cantidad' => 2,
        ]);
    }

    public function test_checkout_no_expone_errores_internos_al_cliente(): void
    {
        $this->mock(OrderService::class, function ($mock) {
            $mock->shouldReceive('processOrder')->once()->andThrow(
                new \Illuminate\Database\QueryException('sqlite', 'select * from usuario', [], new \Exception('boom'))
            );
        });

        $response = $this->actingAs($this->cliente)->post(route('pedidos.procesar'));

        $response->assertSessionHas('error', 'No pudimos procesar tu pedido. Inténtalo de nuevo en unos minutos.');
        $this->assertStringNotContainsString('select', (string) session('error'));
    }

    public function test_order_uses_the_configured_receipt_series(): void
    {
        Configuracion::updateOrCreate(['clave' => 'comprobante_serie'], ['valor' => '002-002']);

        $producto = $this->producto();
        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'cantidad' => 1,
        ]);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->assertStringStartsWith('002-002-', $pedido->numero_comprobante);
    }
}
