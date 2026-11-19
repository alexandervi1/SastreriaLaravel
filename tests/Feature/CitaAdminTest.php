<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\CitaBloqueo;
use App\Models\Configuracion;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CitaAdminTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

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
            'password_rotacion_dias' => '90',
        ] as $clave => $valor) {
            Configuracion::create(['clave' => $clave, 'valor' => $valor]);
        }

        $this->admin = $this->usuario('admin1', 1);
        $this->cliente = $this->usuario('cliente1', 2);

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
        ]);
    }

    private function cita(string $estado = Cita::ESTADO_SOLICITADA): Cita
    {
        return Cita::create([
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'consulta',
            'inicio' => now()->addDay()->setTime(10, 0),
            'fin' => now()->addDay()->setTime(10, 45),
            'estado' => $estado,
        ]);
    }

    public function test_el_admin_confirma_una_cita_solicitada(): void
    {
        $cita = $this->cita();

        $this->actingAs($this->admin)
            ->put(route('admin.citas.update', $cita->cita_id), ['estado' => Cita::ESTADO_CONFIRMADA])
            ->assertSessionHas('success');

        $this->assertSame(Cita::ESTADO_CONFIRMADA, $cita->fresh()->estado);
    }

    public function test_una_cita_cancelada_no_vuelve_a_confirmada(): void
    {
        $cita = $this->cita(Cita::ESTADO_CANCELADA);

        $this->actingAs($this->admin)
            ->put(route('admin.citas.update', $cita->cita_id), ['estado' => Cita::ESTADO_CONFIRMADA])
            ->assertSessionHas('error');

        $this->assertSame(Cita::ESTADO_CANCELADA, $cita->fresh()->estado);
    }

    public function test_el_admin_crea_una_cita_para_un_cliente(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.citas.store'), [
                'usuario_id' => $this->cliente->usuario_id,
                'tipo' => 'medidas',
                'inicio' => '2026-11-17 09:00:00',
            ])
            ->assertRedirect(route('admin.citas.index', ['dia' => '2026-11-17']));

        $this->assertDatabaseHas('cita', [
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'medidas',
        ]);
    }

    public function test_los_bloqueos_se_crean_y_eliminan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.citas.bloqueos.store'), [
                'inicio' => '2026-11-17 12:00:00',
                'fin' => '2026-11-17 14:00:00',
                'motivo' => 'Feriado',
            ])
            ->assertSessionHas('success');

        $bloqueo = CitaBloqueo::firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('admin.citas.bloqueos.destroy', $bloqueo->bloqueo_id))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('cita_bloqueo', 0);
    }

    public function test_el_dashboard_muestra_las_citas_de_hoy(): void
    {
        Cita::create([
            'usuario_id' => $this->cliente->usuario_id,
            'tipo' => 'consulta',
            'inicio' => now()->setTime(15, 0),
            'fin' => now()->setTime(15, 45),
            'estado' => Cita::ESTADO_CONFIRMADA,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Citas de hoy')
            ->assertSee('badge-menu', false);
    }
}
