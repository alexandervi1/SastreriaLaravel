<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminFixesTest extends TestCase
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

        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Pescados']]);
        Configuracion::create(['clave' => 'iva_actual', 'valor' => '15']);
        $this->activarVentaPorTalla();

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

    private function productoConPedido(int $cantidad = 2): array
    {
        $producto = Producto::create([
            'nombre' => 'Terno de prueba',
            'precio' => 10,
            'categoria_id' => 1,
            'vende_por_talla' => true,
        ]);

        $variante = \App\Models\ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '40',
            'color' => 'Azul noche',
            'sku' => 'SKU-TEST',
            'stock' => 100,
            'activo' => true,
        ]);

        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'variante_id' => $variante->variante_id,
            'cantidad' => $cantidad,
        ]);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        return [$producto->fresh(), $variante->fresh(), $pedido];
    }

    public function test_cancelling_an_order_restores_stock(): void
    {
        [$producto, $variante, $pedido] = $this->productoConPedido(2);
        $this->assertEquals(98, $variante->stock);

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado'])
            ->assertSessionHas('success');

        $this->assertEquals('Cancelado', $pedido->fresh()->estado);
        $this->assertEquals(100, $variante->fresh()->stock);
    }

    public function test_cancelling_twice_does_not_duplicate_the_restock(): void
    {
        [$producto, $variante, $pedido] = $this->productoConPedido(2);

        $this->actingAs($this->admin)->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);
        $this->actingAs($this->admin)->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);

        $this->assertEquals(100, $variante->fresh()->stock);
    }

    public function test_cancelled_order_cannot_be_reactivated(): void
    {
        [$producto, $variante, $pedido] = $this->productoConPedido(2);

        $this->actingAs($this->admin)->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Entregado'])
            ->assertSessionHas('error');

        $this->assertEquals('Cancelado', $pedido->fresh()->estado);
    }

    public function test_product_with_order_lines_cannot_be_deleted(): void
    {
        [$producto, $variante, $pedido] = $this->productoConPedido(2);

        $this->actingAs($this->admin)
            ->delete(route('admin.productos.destroy', $producto->producto_id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('producto', ['producto_id' => $producto->producto_id]);
        $this->assertDatabaseHas('pedido_linea', ['pedido_id' => $pedido->pedido_id]);
    }

    public function test_username_change_logs_the_admin_out(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.seguridad'), [
                'nombre_completo' => 'Admin Uno',
                'nombre_usuario' => 'admin_renombrado',
                'correo' => 'admin1@example.com',
                'current_password' => 'secret123',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('usuario', ['usuario_id' => $this->admin->usuario_id, 'nombre_usuario' => 'admin_renombrado']);
    }

    public function test_password_change_sets_rotation_timestamp(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.seguridad'), [
                'nombre_completo' => 'Admin Uno',
                'nombre_usuario' => 'admin1',
                'correo' => 'admin1@example.com',
                'current_password' => 'secret123',
                'password' => 'nuevaClave123',
                'password_confirmation' => 'nuevaClave123',
            ]);

        $this->assertNotNull($this->admin->fresh()->password_changed_at);
    }

    public function test_security_update_requires_the_current_password(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.seguridad'), [
                'nombre_completo' => 'Admin Uno',
                'nombre_usuario' => 'admin1',
                'correo' => 'admin1@example.com',
                'current_password' => 'incorrecta',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('usuario', ['usuario_id' => $this->admin->usuario_id, 'nombre_usuario' => 'admin1']);
    }

    public function test_admin_cannot_edit_another_admin_as_a_client(): void
    {
        $otroAdmin = Usuario::create([
            'nombre_usuario' => 'admin2',
            'nombre_completo' => 'Admin Dos',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 3',
            'correo' => 'admin2@example.com',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clientes.edit', $otroAdmin->usuario_id))
            ->assertNotFound();
    }

    public function test_sales_report_excludes_cancelled_and_previous_years(): void
    {
        $producto = Producto::create([
            'nombre' => 'Albacora',
            'precio' => 10,
            'categoria_id' => 1,
        ]);

        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->startOfYear()->addMonth(),
            'subtotal' => 100,
            'iva' => 15,
            'total' => 115,
            'estado' => 'Entregado',
        ]);

        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->startOfYear()->addMonth(),
            'subtotal' => 50,
            'iva' => 7.5,
            'total' => 57.5,
            'estado' => 'Cancelado',
        ]);

        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->subYear()->startOfYear()->addMonth(),
            'subtotal' => 999,
            'iva' => 149.85,
            'total' => 1148.85,
            'estado' => 'Entregado',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reportes.ventas'));
        $response->assertOk();

        $ventas = collect($response->viewData('ventasMes'));
        $total = $ventas->sum(fn ($fila) => (float) $fila->total_ingresos);

        $this->assertEquals(115.0, $total, 'Solo debe sumar pedidos del año actual no cancelados.');
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 10,
            'iva' => 1.5,
            'total' => 11.5,
            'estado' => 'Pendiente',
        ]);

        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 20,
            'iva' => 3,
            'total' => 23,
            'estado' => 'Entregado',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pedidos.index', ['estado' => 'Entregado']));

        $response->assertOk();

        $pedidos = $response->viewData('pedidos');
        $this->assertCount(1, $pedidos);
        $this->assertSame('Entregado', $pedidos->first()->estado);
    }

    public function test_pedidos_se_acotan_a_los_ultimos_90_dias_por_defecto(): void
    {
        $antiguo = Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->subDays(120),
            'subtotal' => 10,
            'iva' => 1.5,
            'total' => 11.5,
            'estado' => 'Pendiente',
        ]);

        $reciente = Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->subDays(1),
            'subtotal' => 10,
            'iva' => 1.5,
            'total' => 11.5,
            'estado' => 'Pendiente',
        ]);

        $porDefecto = $this->actingAs($this->admin)->get(route('admin.pedidos.index'));
        $porDefecto->assertOk();
        $this->assertSame([$reciente->pedido_id], $porDefecto->viewData('pedidos')->pluck('pedido_id')->all());
        // Los contadores de los filtros usan el mismo rango que el listado.
        $this->assertSame(1, (int) $porDefecto->viewData('conteos')->sum());

        $ampliado = $this->actingAs($this->admin)->get(route('admin.pedidos.index', [
            'desde' => now()->subDays(180)->toDateString(),
        ]));
        $ampliado->assertOk();
        $this->assertEqualsCanonicalizing(
            [$antiguo->pedido_id, $reciente->pedido_id],
            $ampliado->viewData('pedidos')->pluck('pedido_id')->all()
        );
        $this->assertSame(2, (int) $ampliado->viewData('conteos')->sum());
    }

    public function test_invalid_order_status_filter_falls_back_to_all(): void
    {
        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 10,
            'iva' => 1.5,
            'total' => 11.5,
            'estado' => 'Pendiente',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pedidos.index', ['estado' => 'Cualquiera']));

        $response->assertOk();
        $this->assertNull($response->viewData('estado'));
        $this->assertCount(1, $response->viewData('pedidos'));
    }

    public function test_admin_menu_shows_pending_orders_badge(): void
    {
        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 10,
            'iva' => 1.5,
            'total' => 11.5,
            'estado' => 'Pendiente',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('badge-menu', false);
        $response->assertSee('1 pedido pendiente', false);
    }

    public function test_configuration_opens_the_security_tab_from_the_query(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.configuracion.edit', ['tab' => 'seguridad']));

        $response->assertOk();
        $this->assertSame('seguridad', $response->viewData('tab'));
        $response->assertSee('id="seguridad"', false);
    }

    public function test_footer_uses_the_business_configuration(): void
    {
        Configuracion::updateOrCreate(['clave' => 'negocio_direccion'], ['valor' => 'Calle Configurada 123']);
        Configuracion::updateOrCreate(['clave' => 'negocio_telefono'], ['valor' => '0990001111']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Calle Configurada 123');
        $response->assertSee('0990001111');
    }

    public function test_low_stock_threshold_is_configurable(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno de prueba',
            'precio' => 10,
            'categoria_id' => 1,
        ]);

        \App\Models\ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '40',
            'color' => null,
            'sku' => 'SKU-BAJO',
            'stock' => 1,
            'activo' => true,
        ]);

        Configuracion::updateOrCreate(['clave' => 'umbral_bajo_stock'], ['valor' => '100']);

        $response = $this->actingAs($this->admin)->get(route('admin.reportes.bajo-stock'));
        $this->assertCount(1, $response->viewData('variantes'));

        Configuracion::updateOrCreate(['clave' => 'umbral_bajo_stock'], ['valor' => '0']);

        $response = $this->actingAs($this->admin)->get(route('admin.reportes.bajo-stock'));
        $this->assertCount(0, $response->viewData('variantes'));
    }

    public function test_receipt_uses_the_tax_rate_stored_on_the_order(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno de prueba',
            'precio' => 10,
            'categoria_id' => 1,
            'vende_por_talla' => true,
        ]);

        $variante = \App\Models\ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '40',
            'color' => null,
            'sku' => 'SKU-IVA',
            'stock' => 10,
            'activo' => true,
        ]);

        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'variante_id' => $variante->variante_id,
            'cantidad' => 2,
        ]);

        $pedido = app(OrderService::class)->processOrder($this->cliente);
        $this->assertEquals(15.0, (float) $pedido->iva_pct);

        Configuracion::updateOrCreate(['clave' => 'iva_actual'], ['valor' => '20']);

        $response = $this->actingAs($this->cliente)->get(route('pedidos.receipt', $pedido->pedido_id));

        $response->assertOk();
        $response->assertSee('IVA (15.00%)');
    }

    public function test_configuration_changes_are_logged(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.update'), [
                'iva_actual' => 12,
                'umbral_bajo_stock' => 50,
                'comprobante_serie' => '001-001',
                'password_rotacion_dias' => 90,
                'zona_horaria' => 'America/Guayaquil',
                'negocio_nombre' => 'Sastrería Test',
                'negocio_ruc' => '1301234567001',
                'negocio_direccion' => 'Manta',
                'negocio_telefono' => '0999999999',
                'negocio_leyenda' => 'RIMPE',
            ])
            ->assertRedirect(route('admin.configuracion.edit'));

        $this->assertDatabaseHas('configuracion_log', [
            'clave' => 'iva_actual',
            'valor_nuevo' => '12',
        ]);
    }

    public function test_configuration_cache_is_invalidated_on_save(): void
    {
        Configuracion::updateOrCreate(['clave' => 'iva_actual'], ['valor' => '15']);
        $this->assertEquals(15.0, Configuracion::obtenerIva());

        Configuracion::updateOrCreate(['clave' => 'iva_actual'], ['valor' => '20']);
        $this->assertEquals(20.0, Configuracion::obtenerIva());
    }

    public function test_iva_pct_backfill_uses_real_division_on_integer_totals(): void
    {
        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 100,
            'iva' => 15,
            'iva_pct' => 0,
            'total' => 115,
            'estado' => 'Pendiente',
        ]);

        $migration = require database_path('migrations/2026_11_18_000001_backfill_iva_pct_on_pedido_table.php');
        $migration->up();

        $this->assertEquals(15.0, (float) Pedido::first()->iva_pct);
    }
}
