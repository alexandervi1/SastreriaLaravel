<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\ProductoVariante;
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

        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);

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

    /**
     * @return array{0: Producto, 1: ProductoVariante}
     */
    private function productoConVariante(int $stock = 100, array $overrides = [], string $talla = '40'): array
    {
        $producto = Producto::create(array_merge([
            'nombre' => 'Terno Slim Fit Azul Noche',
            'descripcion' => 'Terno de prueba',
            'precio' => 10,
            'categoria_id' => 1,
        ], $overrides));

        $variante = ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => $talla,
            'color' => 'Azul noche',
            'sku' => 'SKU-' . $producto->producto_id . '-' . $talla,
            'stock' => $stock,
            'activo' => true,
        ]);

        return [$producto, $variante];
    }

    private function agregarAlCarrito(ProductoVariante $variante, int $cantidad): void
    {
        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $variante->producto_id,
            'variante_id' => $variante->variante_id,
            'cantidad' => $cantidad,
        ]);
    }

    public function test_process_order_persists_subtotal_iva_and_total(): void
    {
        [$producto, $variante] = $this->productoConVariante();
        $this->agregarAlCarrito($variante, 2);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->assertEquals(20.00, (float) $pedido->subtotal);
        $this->assertEquals(3.00, (float) $pedido->iva);
        $this->assertEquals(23.00, (float) $pedido->total);
        $this->assertEquals('Pendiente', $pedido->estado);
        $this->assertEquals(98, $variante->fresh()->stock);
    }

    public function test_process_order_snapshots_unit_price(): void
    {
        [$producto, $variante] = $this->productoConVariante();
        $this->agregarAlCarrito($variante, 2);

        $pedido = app(OrderService::class)->processOrder($this->cliente);
        $producto->update(['precio' => 99]);

        $linea = $pedido->fresh()->lineas->first();
        $this->assertEquals(10.00, (float) $linea->precio_unitario);
        $this->assertEquals(20.00, (float) $linea->subtotal);
        $this->assertEquals('40', $linea->talla);
        $this->assertEquals('Azul noche', $linea->color);
        $this->assertEquals(20.00, (float) $pedido->fresh()->subtotal);
        $this->assertEquals(23.00, (float) $pedido->fresh()->total);
    }

    public function test_process_order_rejects_quantity_above_stock(): void
    {
        [, $variante] = $this->productoConVariante(1);
        $this->agregarAlCarrito($variante, 2);

        try {
            app(OrderService::class)->processOrder($this->cliente);
            $this->fail('Se esperaba un rechazo por stock insuficiente.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('stock', mb_strtolower($e->getMessage()));
        }

        $this->assertDatabaseCount('pedido', 0);
        $this->assertEquals(1, $variante->fresh()->stock);
        $this->assertDatabaseCount('carrito', 1);
    }

    public function test_process_order_rolls_back_every_item_when_one_is_short(): void
    {
        [, $suficiente] = $this->productoConVariante(10, ['nombre' => 'Terno Suficiente'], '40');
        [, $corto] = $this->productoConVariante(1, ['nombre' => 'Terno Corto'], '42');

        $this->agregarAlCarrito($suficiente, 2);
        $this->agregarAlCarrito($corto, 5);

        try {
            app(OrderService::class)->processOrder($this->cliente);
            $this->fail('Se esperaba un rechazo por stock insuficiente.');
        } catch (\RuntimeException $e) {
            // esperado
        }

        $this->assertDatabaseCount('pedido', 0);
        $this->assertDatabaseCount('pedido_linea', 0);
        $this->assertEquals(10, $suficiente->fresh()->stock);
        $this->assertEquals(1, $corto->fresh()->stock);
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        [, $variante] = $this->productoConVariante(3);

        $response = $this->actingAs($this->cliente)->post(route('carrito.agregar'), [
            'variante_id' => $variante->variante_id,
            'cantidad' => 5,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('carrito', 0);
    }

    public function test_cart_accepts_quantity_within_stock(): void
    {
        [, $variante] = $this->productoConVariante(3);

        $this->actingAs($this->cliente)->post(route('carrito.agregar'), [
            'variante_id' => $variante->variante_id,
            'cantidad' => 2,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('carrito', [
            'variante_id' => $variante->variante_id,
            'cantidad' => 2,
        ]);
    }

    public function test_se_pueden_comprar_dos_tallas_del_mismo_modelo(): void
    {
        [$producto, $cuarenta] = $this->productoConVariante(5, [], '40');
        $cuarentaYDos = ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '42',
            'color' => 'Azul noche',
            'sku' => 'SKU-42',
            'stock' => 4,
            'activo' => true,
        ]);

        $this->agregarAlCarrito($cuarenta, 1);
        $this->agregarAlCarrito($cuarentaYDos, 2);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->assertCount(2, $pedido->fresh()->lineas);
        $this->assertEquals(4, $cuarenta->fresh()->stock);
        $this->assertEquals(2, $cuarentaYDos->fresh()->stock);
    }

    public function test_cancelar_repone_la_variante_correcta(): void
    {
        [$producto, $cuarenta] = $this->productoConVariante(5, [], '40');
        $cuarentaYDos = ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '42',
            'color' => 'Azul noche',
            'sku' => 'SKU-42',
            'stock' => 4,
            'activo' => true,
        ]);

        $this->agregarAlCarrito($cuarenta, 1);
        $this->agregarAlCarrito($cuarentaYDos, 2);

        $service = app(OrderService::class);
        $pedido = $service->processOrder($this->cliente);
        $service->cancelOrder($pedido);

        $this->assertEquals(5, $cuarenta->fresh()->stock);
        $this->assertEquals(4, $cuarentaYDos->fresh()->stock);
    }

    public function test_el_recibo_muestra_talla_y_color(): void
    {
        [, $variante] = $this->productoConVariante();
        $this->agregarAlCarrito($variante, 1);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->actingAs($this->cliente)
            ->get(route('pedidos.receipt', $pedido->pedido_id))
            ->assertOk()
            ->assertSee('Talla 40')
            ->assertSee('Azul noche');
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

        [, $variante] = $this->productoConVariante();
        $this->agregarAlCarrito($variante, 1);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->assertStringStartsWith('002-002-', $pedido->numero_comprobante);
    }
}
