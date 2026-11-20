<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FlujoExtremoAExtremoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);

        foreach ([
            'iva_actual' => '15',
            'taller_dias' => '1,2,3,4,5,6',
            'taller_hora_inicio' => '09:00',
            'taller_hora_fin' => '18:00',
            'cita_duracion_min' => '45',
            'cita_anticipacion_max_dias' => '30',
            'cita_cancelacion_min_horas' => '24',
            'zona_horaria' => 'America/Guayaquil',
        ] as $clave => $valor) {
            Configuracion::create(['clave' => $clave, 'valor' => $valor]);
        }

        $this->activarVentaPorTalla();

        $this->admin = Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 1',
            'correo' => 'admin1@example.com',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-16 08:00:00', 'America/Guayaquil'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_flujo_de_talla_de_extremo_a_extremo(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno Slim Fit',
            'precio' => 200,
            'categoria_id' => 1,
            'vende_por_talla' => true,
            'vende_a_medida' => false,
        ]);

        $variante = ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '40',
            'color' => 'Azul noche',
            'sku' => 'SKU-E2E',
            'stock' => 5,
            'activo' => true,
        ]);

        $this->post(route('register'), [
            'nombre_completo' => 'Cliente E2E',
            'identificacion' => '1312345678',
            'nombre_usuario' => 'clientee2e',
            'password' => 'ClaveE2E123!',
            'password_confirmation' => 'ClaveE2E123!',
            'direccion' => 'Calle 3',
            'correo' => 'e2e@example.com',
        ])->assertRedirect(route('login'));

        $this->post(route('login'), [
            'nombre_usuario' => 'clientee2e',
            'password' => 'ClaveE2E123!',
        ]);

        $this->post(route('carrito.agregar'), [
            'variante_id' => $variante->variante_id,
            'cantidad' => 2,
        ])->assertSessionHas('success');

        $this->post(route('pedidos.procesar'), ['metodo_entrega' => 'retiro'])
            ->assertRedirect();

        $this->assertEquals(3, $variante->fresh()->stock);
        $pedido = Pedido::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado'])
            ->assertSessionHas('success');

        $this->assertEquals(5, $variante->fresh()->stock);
        $this->assertSame('Cancelado', $pedido->fresh()->estado);
    }

    public function test_flujo_de_cita_de_extremo_a_extremo(): void
    {
        $clienteA = Usuario::create([
            'nombre_usuario' => 'clientea',
            'nombre_completo' => 'Cliente A',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle A',
            'correo' => 'a@example.com',
        ]);

        $clienteB = Usuario::create([
            'nombre_usuario' => 'clienteb',
            'nombre_completo' => 'Cliente B',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle B',
            'correo' => 'b@example.com',
        ]);

        $inicio = '2026-11-17 09:00:00';

        $this->actingAs($clienteA)
            ->post(route('citas.store'), ['tipo' => 'medidas', 'inicio' => $inicio])
            ->assertSessionHas('success');

        $this->actingAs($clienteB)
            ->post(route('citas.store'), ['tipo' => 'consulta', 'inicio' => $inicio])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cita', 1);

        $cita = \App\Models\Cita::firstOrFail();

        $this->actingAs($clienteA)
            ->delete(route('citas.destroy', $cita->cita_id))
            ->assertSessionHas('success');

        $this->assertSame('Cancelada', $cita->fresh()->estado);
    }
}
