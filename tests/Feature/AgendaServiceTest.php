<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\CitaBloqueo;
use App\Models\Configuracion;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\AgendaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgendaServiceTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);

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

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-16 08:00:00', 'America/Guayaquil')); // lunes
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function dia(string $fecha = '2026-11-16'): Carbon
    {
        return Carbon::parse($fecha, 'America/Guayaquil');
    }

    public function test_un_dia_normal_ofrece_huecos_de_la_duracion_configurada(): void
    {
        $huecos = app(AgendaService::class)->huecosDisponibles($this->dia());

        $this->assertCount(12, $huecos);
        $this->assertSame('09:00', $huecos[0]['inicio']->format('H:i'));
        $this->assertSame('09:45', $huecos[0]['fin']->format('H:i'));
        $this->assertSame('America/Guayaquil', $huecos[0]['inicio']->timezoneName);
    }

    public function test_el_domingo_no_hay_huecos(): void
    {
        $this->assertCount(0, app(AgendaService::class)->huecosDisponibles($this->dia('2026-11-22')));
    }

    public function test_un_bloqueo_excluye_los_huecos_que_toca(): void
    {
        CitaBloqueo::create([
            'inicio' => $this->dia()->setTime(10, 0),
            'fin' => $this->dia()->setTime(11, 0),
            'motivo' => 'Entrega de pedidos',
        ]);

        $huecos = collect(app(AgendaService::class)->huecosDisponibles($this->dia()))
            ->pluck('inicio')
            ->map(fn (Carbon $inicio) => $inicio->format('H:i'))
            ->all();

        $this->assertNotContains('09:45', $huecos);
        $this->assertNotContains('10:30', $huecos);
        $this->assertContains('11:15', $huecos);
    }

    public function test_una_cita_existente_excluye_su_hueco(): void
    {
        Cita::create([
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'consulta',
            'inicio' => $this->dia()->setTime(9, 0),
            'fin' => $this->dia()->setTime(9, 45),
            'estado' => Cita::ESTADO_SOLICITADA,
        ]);

        $huecos = collect(app(AgendaService::class)->huecosDisponibles($this->dia()))
            ->pluck('inicio')
            ->map(fn (Carbon $inicio) => $inicio->format('H:i'))
            ->all();

        $this->assertNotContains('09:00', $huecos);
        $this->assertContains('09:45', $huecos);
    }

    public function test_las_horas_pasadas_no_se_ofrecen(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00', 'America/Guayaquil'));

        $huecos = collect(app(AgendaService::class)->huecosDisponibles($this->dia()))
            ->pluck('inicio')
            ->map(fn (Carbon $inicio) => $inicio->format('H:i'))
            ->all();

        $this->assertNotContains('09:00', $huecos);
        $this->assertNotContains('09:45', $huecos);
        $this->assertContains('10:30', $huecos);
    }

    public function test_no_se_ofrece_mas_alla_de_la_anticipacion_maxima(): void
    {
        $this->assertCount(0, app(AgendaService::class)->huecosDisponibles($this->dia('2026-12-18')));
    }

    public function test_dos_reservas_del_mismo_hueco_solo_permiten_una(): void
    {
        $servicio = app(AgendaService::class);
        $inicio = $this->dia()->setTime(9, 0);

        $servicio->reservar($this->cliente, 'medidas', $inicio);

        $this->expectException(\RuntimeException::class);

        $servicio->reservar($this->cliente, 'prueba', $inicio);
    }

    public function test_no_se_puede_reservar_un_hueco_que_no_existe(): void
    {
        $this->expectException(\RuntimeException::class);

        app(AgendaService::class)->reservar($this->cliente, 'medidas', $this->dia()->setTime(9, 10));
    }
}
