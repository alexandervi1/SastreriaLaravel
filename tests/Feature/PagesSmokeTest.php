<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Usuario $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->activarVentaPorTalla();

        $this->admin = Usuario::where('nombre_usuario', 'admin123')->firstOrFail();

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'identificacion' => '1312345678',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
        ]);
    }

    public function test_home_publica_la_marca_entretela(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Entretela')
            ->assertDontSee('<NOMBRE_NEGOCIO>', false);
    }

    public function test_admin_pages_render(): void
    {
        $rutas = [
            route('admin.dashboard'),
            route('admin.productos.index'),
            route('admin.productos.create'),
            route('admin.categorias.index'),
            route('admin.telas.index'),
            route('admin.telas.create'),
            route('admin.citas.index'),
            route('admin.citas.create'),
            route('admin.clientes.index'),
            route('admin.clientes.create'),
            route('admin.carritos.index'),
            route('admin.administradores.index'),
            route('admin.administradores.create'),
            route('admin.pedidos.index'),
            route('admin.configuracion.edit'),
            route('admin.reportes.ventas'),
            route('admin.reportes.mas-vendidos'),
            route('admin.reportes.bajo-stock'),
            route('admin.reportes.stock-categoria'),
        ];

        foreach ($rutas as $ruta) {
            $this->actingAs($this->admin)->get($ruta)->assertOk();
        }
    }

    public function test_public_and_client_pages_render(): void
    {
        $producto = Producto::firstOrFail();

        $this->get(route('home'))->assertOk();
        $this->get(route('ayuda'))->assertOk();
        $this->get(route('politicas'))->assertOk();
        $this->get(route('terminos'))->assertOk();
        $this->get(route('productos.show', $producto->slug))->assertOk();
        $this->get(route('catalogo'))->assertOk();
        $this->get('/categorias/' . $producto->categoria_id)->assertRedirect(route('catalogo'));

        $this->actingAs($this->cliente)->get(route('carrito.index'))->assertOk();
        $this->actingAs($this->cliente)->get(route('pedidos.index'))->assertOk();
    }

    public function test_receipt_shows_persisted_totals(): void
    {
        $producto = Producto::firstOrFail();

        $variante = \App\Models\ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '40',
            'color' => null,
            'sku' => 'SKU-SMOKE',
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

        $this->actingAs($this->cliente)
            ->get(route('pedidos.receipt', $pedido->pedido_id))
            ->assertOk()
            ->assertSee(number_format((float) $pedido->total, 2))
            ->assertSee(number_format((float) $pedido->iva, 2))
            ->assertSee('PENDIENTE');
    }
}
