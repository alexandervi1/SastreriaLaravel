<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\MedidaCliente;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Tela;
use App\Models\Usuario;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PedidoConfeccionTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Usuario $cliente;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);
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
            'telefono' => '0999999999',
        ]);

        $this->producto = Producto::create([
            'nombre' => 'Terno a medida',
            'precio' => 250,
            'categoria_id' => 1,
            'vende_por_talla' => false,
            'vende_a_medida' => true,
            'precio_medida' => 300,
            'dias_confeccion' => 10,
        ]);
    }

    private function pedidoAMedida(): \App\Models\Pedido
    {
        $tela = Tela::create([
            'nombre' => 'Lana merino azul',
            'color' => 'Azul noche',
            'recargo' => 25,
            'activo' => true,
        ]);

        $medida = MedidaCliente::create([
            'usuario_id' => $this->cliente->usuario_id,
            'nombre' => 'Mis medidas',
            'patron' => 'caballero',
            'pecho' => 101.5,
            'cintura' => 90,
            'hombros' => 45,
            'largo_saco' => 75,
            'largo_manga' => 62,
            'entrepierna' => 80,
            'largo_pantalon' => 105,
        ]);

        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $this->producto->producto_id,
            'tipo' => 'medida',
            'tela_id' => $tela->tela_id,
            'medida_id' => $medida->medida_id,
            'cantidad' => 1,
        ]);

        return app(OrderService::class)->processOrder($this->cliente);
    }

    public function test_un_pedido_a_medida_no_puede_saltar_de_confirmado_a_listo(): void
    {
        $pedido = $this->pedidoAMedida();
        $service = app(OrderService::class);
        $service->changeStatus($pedido, 'Confirmado');

        $this->expectException(\RuntimeException::class);

        $service->changeStatus($pedido->fresh(), 'Listo para entrega');
    }

    public function test_el_flujo_completo_de_confeccion(): void
    {
        $pedido = $this->pedidoAMedida();
        $service = app(OrderService::class);

        $this->assertSame('Confirmado', $service->changeStatus($pedido, 'Confirmado')->estado);
        $this->assertSame('En confección', $service->changeStatus($pedido->fresh(), 'En confección')->estado);
        $this->assertSame('Listo para entrega', $service->changeStatus($pedido->fresh(), 'Listo para entrega')->estado);
        $this->assertSame('Entregado', $service->changeStatus($pedido->fresh(), 'Entregado')->estado);
    }

    public function test_al_confirmar_se_calcula_la_fecha_de_entrega_en_dias_habiles(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00')); // lunes

        $pedido = $this->pedidoAMedida();
        $confirmado = app(OrderService::class)->changeStatus($pedido, 'Confirmado');

        $this->assertNotNull($confirmado->fecha_entrega_estimada);
        $this->assertSame('2026-11-30', $confirmado->fecha_entrega_estimada->toDateString());

        Carbon::setTestNow();
    }

    public function test_un_pedido_solo_por_talla_puede_confirmarse_y_quedar_listo(): void
    {
        $productoTalla = Producto::create([
            'nombre' => 'Terno por talla',
            'precio' => 200,
            'categoria_id' => 1,
            'vende_por_talla' => true,
        ]);

        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $productoTalla->producto_id,
            'variante_id' => \App\Models\ProductoVariante::create([
                'producto_id' => $productoTalla->producto_id,
                'talla' => '40',
                'sku' => 'SKU-CONF',
                'stock' => 5,
                'activo' => true,
            ])->variante_id,
            'tipo' => 'talla',
            'cantidad' => 1,
        ]);

        $pedido = app(OrderService::class)->processOrder($this->cliente);
        $service = app(OrderService::class);

        $service->changeStatus($pedido, 'Confirmado');
        $listo = $service->changeStatus($pedido->fresh(), 'Listo para entrega');

        $this->assertSame('Listo para entrega', $listo->estado);
        $this->assertNull($listo->fecha_entrega_estimada);
    }

    public function test_la_orden_de_trabajo_muestra_las_medidas_copiadas(): void
    {
        $pedido = $this->pedidoAMedida();

        $this->actingAs($this->admin)
            ->get(route('admin.pedidos.orden-trabajo', $pedido->pedido_id))
            ->assertOk()
            ->assertSee('Orden de trabajo')
            ->assertSee('101.5')
            ->assertSee('Lana merino azul')
            ->assertSee('Cliente Uno');
    }
}
