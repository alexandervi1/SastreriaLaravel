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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

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

        $this->admin = Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 1',
            'correo' => 'admin1@example.com',
        ]);

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 2',
            'correo' => 'cliente1@example.com',
        ]);
    }

    private function producto(string $nombre, float $precio, int $stock, ?Producto $en = null): ProductoVariante
    {
        $producto = $en ?? Producto::create([
            'nombre' => $nombre,
            'precio' => $precio,
            'categoria_id' => 1,
        ]);

        return ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '40',
            'color' => 'Azul noche',
            'sku' => 'SKU-' . $producto->producto_id . '-' . $nombre . '-' . $stock,
            'stock' => $stock,
            'activo' => true,
        ]);
    }

    /**
     * @param array<int, array{0: ProductoVariante, 1: int}> $lineas
     */
    private function pedidoCon(array $lineas): \App\Models\Pedido
    {
        foreach ($lineas as [$variante, $cantidad]) {
            Carrito::create([
                'usuario_id' => $this->cliente->usuario_id,
                'producto_id' => $variante->producto_id,
                'variante_id' => $variante->variante_id,
                'cantidad' => $cantidad,
            ]);
        }

        return app(OrderService::class)->processOrder($this->cliente);
    }

    private function cancelar(\App\Models\Pedido $pedido)
    {
        return $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);
    }

    public function test_pendiente_a_cancelado_repone_el_stock_exacto(): void
    {
        $albacora = $this->producto('Albacora', 10, 100);
        $almeja = $this->producto('Almeja', 5, 100);

        $pedido = $this->pedidoCon([[$albacora, 2], [$almeja, 3]]);

        $this->assertEquals(98, $albacora->fresh()->stock);
        $this->assertEquals(97, $almeja->fresh()->stock);

        $this->cancelar($pedido)->assertSessionHas('success');

        $this->assertSame('Cancelado', $pedido->fresh()->estado);
        $this->assertEquals(100, $albacora->fresh()->stock);
        $this->assertEquals(100, $almeja->fresh()->stock);
    }

    public function test_confirmado_a_cancelado_repone_el_stock(): void
    {
        $variante = $this->producto('Almeja', 5, 100);
        $pedido = $this->pedidoCon([[$variante, 4]]);
        app(OrderService::class)->changeStatus($pedido, 'Confirmado');

        $this->cancelar($pedido->fresh())->assertSessionHas('success');

        $this->assertSame('Cancelado', $pedido->fresh()->estado);
        $this->assertEquals(100, $variante->fresh()->stock);
    }

    public function test_entregado_a_cancelado_es_rechazado_y_no_toca_el_stock(): void
    {
        $variante = $this->producto('Almeja', 5, 100);
        $pedido = $this->pedidoCon([[$variante, 4]]);
        $service = app(OrderService::class);
        $service->changeStatus($pedido, 'Confirmado');
        $service->changeStatus($pedido->fresh(), 'En confección');
        $service->changeStatus($pedido->fresh(), 'Listo para entrega');
        $service->changeStatus($pedido->fresh(), 'Entregado');

        $this->cancelar($pedido->fresh())->assertSessionHas('error');

        $this->assertSame('Entregado', $pedido->fresh()->estado);
        $this->assertEquals(96, $variante->fresh()->stock);
    }

    public function test_cancelado_a_pendiente_es_rechazado(): void
    {
        $variante = $this->producto('Almeja', 5, 100);
        $pedido = $this->pedidoCon([[$variante, 4]]);
        $this->cancelar($pedido);

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Pendiente'])
            ->assertSessionHas('error');

        $this->assertSame('Cancelado', $pedido->fresh()->estado);
    }

    public function test_entregado_a_pendiente_es_rechazado(): void
    {
        $variante = $this->producto('Almeja', 5, 100);
        $pedido = $this->pedidoCon([[$variante, 4]]);
        $service = app(OrderService::class);
        $service->changeStatus($pedido, 'Confirmado');
        $service->changeStatus($pedido->fresh(), 'En confección');
        $service->changeStatus($pedido->fresh(), 'Listo para entrega');
        $service->changeStatus($pedido->fresh(), 'Entregado');

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Pendiente'])
            ->assertSessionHas('error');

        $this->assertSame('Entregado', $pedido->fresh()->estado);
    }

    public function test_cancelar_dos_veces_no_repone_dos_veces(): void
    {
        $variante = $this->producto('Almeja', 5, 100);
        $pedido = $this->pedidoCon([[$variante, 4]]);

        $this->cancelar($pedido)->assertSessionHas('success');
        $this->cancelar($pedido->fresh())->assertSessionHas('error');

        $this->assertEquals(100, $variante->fresh()->stock);
    }

    public function test_cancelar_un_pedido_cuyo_producto_fue_borrado_no_falla(): void
    {
        $variante = $this->producto('Almeja', 5, 100);
        $pedido = $this->pedidoCon([[$variante, 4]]);

        // La FK restrict impide el borrado normal; se fuerza el estado que el código debe tolerar.
        // En SQLite, foreign_keys=OFF no surte efecto dentro de la transacción de RefreshDatabase:
        // se difiere la comprobación al commit, que nunca llega porque la prueba hace rollback.
        DB::statement('PRAGMA defer_foreign_keys = ON');
        Producto::whereKey($variante->producto_id)->delete();

        $cancelado = app(OrderService::class)->cancelOrder($pedido);

        $this->assertSame('Cancelado', $cancelado->estado);
    }

    public function test_un_cliente_no_puede_cambiar_el_estado_de_un_pedido(): void
    {
        $variante = $this->producto('Almeja', 5, 100);
        $pedido = $this->pedidoCon([[$variante, 4]]);

        $this->actingAs($this->cliente)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado'])
            ->assertRedirect(route('login'));

        $this->assertSame('Pendiente', $pedido->fresh()->estado);
    }
}
