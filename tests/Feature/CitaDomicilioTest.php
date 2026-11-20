<?php

namespace Tests\Feature;

use App\Models\Categoria;
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

class CitaDomicilioTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    private Usuario $otro;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Sastrería']]);

        foreach ([
            'taller_dias' => '1,2,3,4,5,6',
            'taller_hora_inicio' => '09:00',
            'taller_hora_fin' => '18:00',
            'cita_duracion_min' => '45',
            'cita_anticipacion_max_dias' => '30',
            'cita_cancelacion_min_horas' => '24',
            'zona_horaria' => 'America/Guayaquil',
            'cita_domicilio_activa' => '1',
            'cita_domicilio_zona' => 'Solo Riobamba urbano',
            'negocio_direccion' => 'Calle Secreta 123',
            'negocio_direccion_publica' => 'Riobamba Centro',
            'negocio_whatsapp' => '593999999999',
        ] as $clave => $valor) {
            Configuracion::create(['clave' => $clave, 'valor' => $valor]);
        }

        $this->cliente = $this->usuario('cliente1', 2);
        $this->otro = $this->usuario('cliente2', 2);
        $this->admin = $this->usuario('admin1', 1);

        Carbon::setTestNow(Carbon::parse('2026-11-16 08:00:00', 'America/Guayaquil'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function usuario(string $nombre, int $rol): Usuario
    {
        return Usuario::create([
            'nombre_usuario' => $nombre,
            'nombre_completo' => ucfirst($nombre),
            'password' => Hash::make('secret123'),
            'rol_id' => $rol,
            'direccion' => 'Calle 1',
            'correo' => $nombre . '@example.com',
            'telefono' => '0991112222',
        ]);
    }

    private function cita(array $overrides = []): Cita
    {
        return Cita::create(array_merge([
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'medidas',
            'lugar' => 'taller',
            'inicio' => Carbon::parse('2026-11-17 09:00:00', 'America/Guayaquil'),
            'fin' => Carbon::parse('2026-11-17 09:45:00', 'America/Guayaquil'),
            'estado' => Cita::ESTADO_SOLICITADA,
        ], $overrides));
    }

    public function test_una_cita_a_domicilio_ocupa_dos_huecos(): void
    {
        $this->actingAs($this->cliente)
            ->post(route('citas.store'), [
                'tipo' => 'medidas',
                'lugar' => 'domicilio',
                'direccion_cliente' => 'Av. Siempre Viva 742',
                'inicio' => '2026-11-17 09:00:00',
            ])
            ->assertRedirect(route('citas.index'));

        $cita = Cita::firstOrFail();

        $this->assertSame('domicilio', $cita->lugar);
        $this->assertSame('2026-11-17 09:00:00', $cita->inicio->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-17 10:30:00', $cita->fin->format('Y-m-d H:i:s'));
    }

    public function test_una_cita_a_domicilio_exige_direccion(): void
    {
        $this->actingAs($this->cliente)
            ->post(route('citas.store'), [
                'tipo' => 'medidas',
                'lugar' => 'domicilio',
                'inicio' => '2026-11-17 09:00:00',
            ])
            ->assertSessionHasErrors('direccion_cliente');

        $this->assertDatabaseCount('cita', 0);
    }

    public function test_no_se_reserva_a_domicilio_si_esta_desactivado(): void
    {
        Configuracion::updateOrCreate(['clave' => 'cita_domicilio_activa'], ['valor' => '0']);
        Configuracion::flush();

        $this->actingAs($this->cliente)
            ->post(route('citas.store'), [
                'tipo' => 'medidas',
                'lugar' => 'domicilio',
                'direccion_cliente' => 'Av. Siempre Viva 742',
                'inicio' => '2026-11-17 09:00:00',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cita', 0);
    }

    public function test_el_segundo_hueco_debe_estar_libre(): void
    {
        $this->cita([
            'inicio' => Carbon::parse('2026-11-17 09:45:00', 'America/Guayaquil'),
            'fin' => Carbon::parse('2026-11-17 10:30:00', 'America/Guayaquil'),
        ]);

        $this->actingAs($this->cliente)
            ->post(route('citas.store'), [
                'tipo' => 'medidas',
                'lugar' => 'domicilio',
                'direccion_cliente' => 'Av. Siempre Viva 742',
                'inicio' => '2026-11-17 09:00:00',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cita', 1);
    }

    public function test_la_direccion_exacta_no_aparece_en_paginas_publicas_ni_en_citas_solicitadas(): void
    {
        $this->cita();

        foreach (['home', 'quienes.somos', 'ayuda', 'politicas', 'terminos'] as $ruta) {
            $this->get(route($ruta))->assertOk()->assertDontSee('Calle Secreta 123');
        }

        $this->actingAs($this->cliente)->get(route('citas.index'))
            ->assertOk()
            ->assertDontSee('Calle Secreta 123')
            ->assertSee('se comparte al confirmar');
    }

    public function test_la_direccion_exacta_solo_la_ve_el_dueno_con_cita_confirmada(): void
    {
        $cita = $this->cita(['estado' => Cita::ESTADO_CONFIRMADA]);

        $this->actingAs($this->cliente)->get(route('citas.index'))
            ->assertOk()
            ->assertSee('Calle Secreta 123')
            ->assertSee(urlencode('Calle Secreta 123'));

        $this->actingAs($this->otro)->get(route('citas.index'))
            ->assertOk()
            ->assertDontSee('Calle Secreta 123');

        $this->actingAs($this->admin)->get(route('admin.citas.index', ['dia' => $cita->inicio->toDateString()]))
            ->assertOk()
            ->assertSee('Calle Secreta 123');
    }

    public function test_el_recibo_usa_la_direccion_publica(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno',
            'precio' => 90,
            'precio_medida' => 90,
            'dias_confeccion' => 10,
            'categoria_id' => 1,
        ]);

        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now(),
            'subtotal' => 90,
            'iva' => 0,
            'iva_pct' => 0,
            'total' => 90,
            'estado' => 'Pendiente',
        ]);

        PedidoLinea::create([
            'pedido_id' => $pedido->pedido_id,
            'producto_id' => $producto->producto_id,
            'tipo' => 'medida',
            'producto_nombre' => 'Terno',
            'cantidad' => 1,
            'precio_unitario' => 90,
            'subtotal' => 90,
        ]);

        $this->actingAs($this->cliente)->get(route('pedidos.receipt', $pedido->pedido_id))
            ->assertOk()
            ->assertSee('Riobamba Centro')
            ->assertDontSee('Calle Secreta 123');
    }
}
