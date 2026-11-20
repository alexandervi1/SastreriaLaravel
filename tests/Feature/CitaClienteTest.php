<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\PedidoLinea;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CitaClienteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    private Usuario $otro;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);
        \App\Models\Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);

        foreach ([
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

        $this->cliente = $this->usuario('cliente1');
        $this->otro = $this->usuario('cliente2');

        Carbon::setTestNow(Carbon::parse('2026-11-16 08:00:00', 'America/Guayaquil'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function usuario(string $nombre): Usuario
    {
        return Usuario::create([
            'nombre_usuario' => $nombre,
            'nombre_completo' => ucfirst($nombre),
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => $nombre . '@example.com',
        ]);
    }

    private function inicio(): string
    {
        return '2026-11-17 09:00:00';
    }

    public function test_el_cliente_reserva_una_cita(): void
    {
        $this->actingAs($this->cliente)
            ->post(route('citas.store'), [
                'tipo' => 'medidas',
                'inicio' => $this->inicio(),
                'notas' => 'Primera visita',
            ])
            ->assertRedirect(route('citas.index'));

        $this->assertDatabaseHas('cita', [
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'medidas',
            'estado' => 'Solicitada',
        ]);
    }

    public function test_la_cita_se_cancela_con_antelacion_suficiente(): void
    {
        $cita = Cita::create([
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'consulta',
            'inicio' => now()->addDays(3),
            'fin' => now()->addDays(3)->addMinutes(45),
            'estado' => Cita::ESTADO_SOLICITADA,
        ]);

        $this->actingAs($this->cliente)
            ->delete(route('citas.destroy', $cita->cita_id))
            ->assertSessionHas('success');

        $this->assertSame(Cita::ESTADO_CANCELADA, $cita->fresh()->estado);
    }

    public function test_no_se_cancela_con_menos_de_24_horas(): void
    {
        $cita = Cita::create([
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'consulta',
            'inicio' => now()->addHours(3),
            'fin' => now()->addHours(3)->addMinutes(45),
            'estado' => Cita::ESTADO_SOLICITADA,
        ]);

        $this->actingAs($this->cliente)
            ->delete(route('citas.destroy', $cita->cita_id))
            ->assertSessionHas('error');

        $this->assertSame(Cita::ESTADO_SOLICITADA, $cita->fresh()->estado);
    }

    public function test_una_cita_ajena_no_se_puede_cancelar(): void
    {
        $cita = Cita::create([
            'usuario_id' => $this->otro->usuario_id,
            'tipo' => 'consulta',
            'inicio' => now()->addDays(3),
            'fin' => now()->addDays(3)->addMinutes(45),
            'estado' => Cita::ESTADO_SOLICITADA,
        ]);

        $this->actingAs($this->cliente)
            ->delete(route('citas.destroy', $cita->cita_id))
            ->assertNotFound();
    }

    public function test_el_pedido_con_medidas_enlaza_a_la_cita(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno a medida',
            'precio' => 250,
            'categoria_id' => 1,
            'vende_por_talla' => false,
            'vende_a_medida' => true,
            'precio_medida' => 300,
            'dias_confeccion' => 10,
        ]);

        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 300,
            'iva' => 45,
            'total' => 345,
            'estado' => 'Confirmado',
        ]);

        PedidoLinea::create([
            'pedido_id' => $pedido->pedido_id,
            'producto_id' => $producto->producto_id,
            'tipo' => 'medida',
            'producto_nombre' => $producto->nombre,
            'requiere_cita' => true,
            'cantidad' => 1,
            'precio_unitario' => 300,
            'subtotal' => 300,
        ]);

        $this->actingAs($this->cliente)
            ->get(route('pedidos.show', $pedido->pedido_id))
            ->assertOk()
            ->assertSee('Agenda tu cita de medidas');
    }

    public function test_el_post_de_citas_esta_limitado_por_usuario(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($this->cliente)->post(route('citas.store'), [
                'tipo' => 'consulta',
                'inicio' => '2026-11-17 0' . ($i % 2 === 0 ? '9' : '9') . ':45:00',
            ]);
        }

        $this->actingAs($this->cliente)
            ->post(route('citas.store'), ['tipo' => 'consulta', 'inicio' => '2026-11-17 11:00:00'])
            ->assertStatus(429);
    }
}
