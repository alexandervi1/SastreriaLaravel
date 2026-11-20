<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\MedidaCliente;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Tela;
use App\Models\Usuario;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrecioDesglosadoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    private Usuario $admin;

    private Producto $producto;

    private Tela $tela;

    private MedidaCliente $medida;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Sastrería']]);

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
        ]);

        $this->admin = Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 2',
            'correo' => 'admin1@example.com',
        ]);

        $this->producto = Producto::create([
            'nombre' => 'Terno de gala',
            'precio' => 200,
            'precio_medida' => 300,
            'dias_confeccion' => 10,
            'categoria_id' => 1,
            'publico' => 'dama',
            'tipo_prenda' => 'abrigo',
        ]);

        $this->tela = Tela::create([
            'nombre' => 'Lana merino',
            'composicion' => '100 % lana',
            'color' => 'Azul noche',
            'recargo' => 25,
            'activo' => true,
        ]);

        $this->medida = MedidaCliente::create([
            'usuario_id' => $this->cliente->usuario_id,
            'nombre' => 'Mis medidas',
            'patron' => 'dama',
            'busto' => 92,
            'cintura' => 74,
            'cadera' => 98,
            'hombros' => 39,
            'largo_manga' => 58,
            'talle_delantero' => 40,
            'talle_espalda' => 39,
            'largo_saco' => 68,
        ]);
    }

    public function test_el_servicio_desglosa_hechura_y_tela(): void
    {
        $desglose = app(PricingService::class)->desglosarLinea($this->producto, $this->tela, 'medida');

        $this->assertSame(300.0, $desglose['hechura']);
        $this->assertSame(25.0, $desglose['tela']);
        $this->assertSame(325.0, $desglose['total']);
    }

    public function test_la_ficha_muestra_hechura_desde_y_el_desglose(): void
    {
        $this->actingAs($this->cliente)
            ->get(route('productos.show', $this->producto->slug))
            ->assertOk()
            ->assertSee('Hechura desde')
            ->assertSee('300.00')
            ->assertSee('id="hechura-medida"', false)
            ->assertSee('id="tela-medida"', false)
            ->assertSee('Hechura')
            ->assertSee('Tela');
    }

    public function test_el_pedido_congela_hechura_y_tela_y_los_muestra(): void
    {
        $this->actingAs($this->cliente)->post(route('carrito.agregar'), [
            'tipo' => 'medida',
            'producto_id' => $this->producto->producto_id,
            'tela_id' => $this->tela->tela_id,
            'medida_id' => (string) $this->medida->medida_id,
        ])->assertSessionHas('success');

        $this->actingAs($this->cliente)->get(route('carrito.index'))
            ->assertOk()
            ->assertSee('Hechura $300.00')
            ->assertSee('tela $25.00')
            ->assertSee('$325.00');

        $pedido = app(OrderService::class)->processOrder($this->cliente);
        $linea = $pedido->lineas()->firstOrFail();

        $this->assertSame('300.00', $linea->precio_hechura);
        $this->assertSame('25.00', $linea->precio_tela);

        $this->actingAs($this->cliente)->get(route('pedidos.receipt', $pedido->pedido_id))
            ->assertOk()
            ->assertSee('Hechura $300.00')
            ->assertSee('tela $25.00')
            ->assertSee('$325.00');

        $this->actingAs($this->admin)->get(route('admin.pedidos.orden-trabajo', $pedido->pedido_id))
            ->assertOk()
            ->assertSee('Hechura:')
            ->assertSee('$300.00')
            ->assertSee('$25.00')
            ->assertSee('$325.00');
    }
}
