<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\PedidoLinea;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([['rol_id' => 2, 'nombre' => 'Cliente']]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
        ]);
    }

    public function test_el_enlace_codifica_tildes_y_saltos_de_linea(): void
    {
        Configuracion::create(['clave' => 'negocio_whatsapp', 'valor' => '593999999999']);

        $enlace = WhatsApp::enlace("Hola señor\n¿confirmamos?");

        $this->assertNotNull($enlace);
        $this->assertStringStartsWith('https://wa.me/593999999999?text=', $enlace);
        $this->assertStringContainsString('%C3%B1', $enlace);
        $this->assertStringContainsString('%0A', $enlace);
    }

    public function test_sin_numero_no_hay_enlace(): void
    {
        Configuracion::create(['clave' => 'negocio_whatsapp', 'valor' => '']);

        $this->assertNull(WhatsApp::enlace('Hola'));
    }

    public function test_el_boton_de_pago_solo_aparece_con_numero(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno',
            'precio' => 100,
            'categoria_id' => 1,
        ]);

        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 100,
            'iva' => 15,
            'total' => 115,
            'estado' => 'Pendiente',
            'numero_comprobante' => '001-001-000000001',
        ]);

        PedidoLinea::create([
            'pedido_id' => $pedido->pedido_id,
            'producto_id' => $producto->producto_id,
            'tipo' => 'talla',
            'producto_nombre' => $producto->nombre,
            'talla' => '40',
            'cantidad' => 1,
            'precio_unitario' => 100,
            'subtotal' => 100,
        ]);

        Configuracion::create(['clave' => 'negocio_whatsapp', 'valor' => '']);

        $this->actingAs($this->cliente)
            ->get(route('pedidos.show', $pedido->pedido_id))
            ->assertOk()
            ->assertDontSee('Coordinar pago por WhatsApp');

        Configuracion::where('clave', 'negocio_whatsapp')->update(['valor' => '593999999999']);
        Configuracion::flush();

        $this->actingAs($this->cliente)
            ->get(route('pedidos.show', $pedido->pedido_id))
            ->assertOk()
            ->assertSee('Coordinar pago por WhatsApp');
    }
}
