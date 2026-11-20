<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\MedidaCliente;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Rol;
use App\Models\Tela;
use App\Models\Usuario;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PedidoMedidaTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

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
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);
        Configuracion::create(['clave' => 'iva_actual', 'valor' => '15']);
        $this->activarVentaPorTalla();

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
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

        $this->tela = Tela::create([
            'nombre' => 'Lana merino azul',
            'composicion' => '100 % lana merino',
            'color' => 'Azul noche',
            'recargo' => 25,
            'activo' => true,
        ]);

        $this->medida = MedidaCliente::create([
            'usuario_id' => $this->cliente->usuario_id,
            'nombre' => 'Mis medidas',
            'pecho' => 100,
            'cintura' => 90,
            'largo_manga' => 62,
            'entrepierna' => 80,
            'largo_pantalon' => 105,
        ]);
    }

    private function agregarMedida(): void
    {
        $this->actingAs($this->cliente)
            ->post(route('carrito.agregar'), [
                'tipo' => 'medida',
                'producto_id' => $this->producto->producto_id,
                'tela_id' => $this->tela->tela_id,
                'medida_id' => (string) $this->medida->medida_id,
                'notas' => 'Solapa de pico',
            ])
            ->assertSessionHas('success');
    }

    public function test_pedido_solo_a_medida_no_toca_stock_y_congela_medidas(): void
    {
        $this->agregarMedida();
        $this->assertDatabaseHas('carrito', ['tipo' => 'medida']);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $linea = $pedido->fresh()->lineas->first();

        $this->assertSame('medida', $linea->tipo);
        $this->assertNull($linea->variante_id);
        $this->assertSame('Lana merino azul', $linea->tela_nombre);
        $this->assertSame(100.0, (float) $linea->medidas['pecho']);
        $this->assertSame('Solapa de pico', $linea->notas);
        $this->assertEquals(0, ProductoVariante::count());
    }

    public function test_la_copia_de_medidas_no_cambia_al_editar_el_perfil(): void
    {
        $this->agregarMedida();
        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->medida->update(['pecho' => 120]);
        $linea = $pedido->fresh()->lineas->first();

        $this->assertSame(100.0, (float) $linea->medidas['pecho']);
    }

    public function test_el_precio_suma_el_recargo_de_la_tela_y_el_iva(): void
    {
        $this->agregarMedida();

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        $this->assertEquals(325.00, (float) $pedido->subtotal);
        $this->assertEquals(48.75, (float) $pedido->iva);
        $this->assertEquals(373.75, (float) $pedido->total);
        $this->assertEquals(325.00, (float) $pedido->lineas->first()->precio_unitario);
    }

    public function test_una_tela_desactivada_entre_el_carrito_y_el_checkout_se_rechaza(): void
    {
        $this->agregarMedida();
        $this->tela->update(['activo' => false]);

        try {
            app(OrderService::class)->processOrder($this->cliente);
            $this->fail('Se esperaba el rechazo por tela inactiva.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('tela', mb_strtolower($e->getMessage()));
        }

        $this->assertDatabaseCount('pedido', 0);
        $this->assertDatabaseCount('carrito', 1);
    }

    public function test_un_pedido_mixto_revierte_todo_si_falla_la_linea_a_medida(): void
    {
        $productoTalla = Producto::create([
            'nombre' => 'Terno por talla',
            'precio' => 200,
            'categoria_id' => 1,
            'vende_por_talla' => true,
            'vende_a_medida' => false,
        ]);

        $variante = ProductoVariante::create([
            'producto_id' => $productoTalla->producto_id,
            'talla' => '40',
            'color' => 'Azul noche',
            'sku' => 'SKU-MIX',
            'stock' => 10,
            'activo' => true,
        ]);

        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $productoTalla->producto_id,
            'variante_id' => $variante->variante_id,
            'tipo' => 'talla',
            'cantidad' => 2,
        ]);

        $this->agregarMedida();
        $this->tela->update(['activo' => false]);

        try {
            app(OrderService::class)->processOrder($this->cliente);
            $this->fail('Se esperaba el rechazo por tela inactiva.');
        } catch (\RuntimeException $e) {
            // esperado
        }

        $this->assertDatabaseCount('pedido', 0);
        $this->assertDatabaseCount('pedido_linea', 0);
        $this->assertEquals(10, $variante->fresh()->stock);
    }

    public function test_tomar_medidas_en_el_taller_no_exige_perfil(): void
    {
        $this->actingAs($this->cliente)
            ->post(route('carrito.agregar'), [
                'tipo' => 'medida',
                'producto_id' => $this->producto->producto_id,
                'tela_id' => $this->tela->tela_id,
                'medida_id' => 'taller',
            ])
            ->assertSessionHas('success');

        $pedido = app(OrderService::class)->processOrder($this->cliente);
        $linea = $pedido->fresh()->lineas->first();

        $this->assertTrue($linea->requiere_cita);
        $this->assertNull($linea->medidas);
    }
}
