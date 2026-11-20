<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VentaPorTallaTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    private Usuario $admin;

    private Producto $producto;

    private ProductoVariante $variante;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);

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
            'nombre' => 'Terno de prueba',
            'precio' => 250,
            'precio_medida' => 90,
            'dias_confeccion' => 10,
            'categoria_id' => 1,
            'vende_por_talla' => true,
            'vende_a_medida' => true,
        ]);

        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->producto_id,
            'talla' => '40',
            'sku' => 'TEST-40',
            'stock' => 5,
            'activo' => true,
        ]);
    }

    public function test_la_ficha_no_muestra_la_pestana_de_talla_con_el_interruptor_apagado(): void
    {
        $response = $this->actingAs($this->cliente)->get(route('productos.show', $this->producto->slug));

        $response->assertOk();
        $response->assertDontSee('Por talla');
        $response->assertSee('Encargar a medida');
    }

    public function test_el_carrito_rechaza_una_variante_con_el_interruptor_apagado(): void
    {
        $response = $this->actingAs($this->cliente)->post(route('carrito.agregar'), [
            'variante_id' => $this->variante->variante_id,
            'cantidad' => 1,
        ]);

        $response->assertSessionHasErrors('variante_id');
        $this->assertDatabaseCount('carrito', 0);
    }

    public function test_el_checkout_rechaza_una_linea_de_talla_con_el_interruptor_apagado(): void
    {
        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $this->producto->producto_id,
            'variante_id' => $this->variante->variante_id,
            'tipo' => 'talla',
            'cantidad' => 1,
        ]);

        $response = $this->actingAs($this->cliente)->post(route('pedidos.procesar'));

        $response->assertSessionHas('error', 'La venta por talla no está disponible.');
        $this->assertDatabaseCount('pedido', 0);
    }

    public function test_los_reportes_de_stock_no_aparecen_ni_responden_con_el_interruptor_apagado(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.reportes.bajo-stock'));

        $this->actingAs($this->admin)->get(route('admin.reportes.bajo-stock'))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.reportes.stock-categoria'))->assertNotFound();
    }
}
