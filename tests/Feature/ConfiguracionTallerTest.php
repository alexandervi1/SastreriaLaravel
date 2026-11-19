<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConfiguracionTallerTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([['rol_id' => 1, 'nombre' => 'Administrador']]);

        $this->admin = Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 1',
            'correo' => 'admin1@example.com',
        ]);
    }

    private function datos(array $overrides = []): array
    {
        return array_merge([
            'taller_dias' => '1,2,3,4,5',
            'taller_hora_inicio' => '08:30',
            'taller_hora_fin' => '17:30',
            'cita_duracion_min' => 30,
            'cita_anticipacion_max_dias' => 20,
            'cita_cancelacion_min_horas' => 12,
            'negocio_whatsapp' => '593999999999',
        ], $overrides);
    }

    public function test_el_horario_del_taller_se_guarda_y_queda_en_la_bitacora(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.update'), $this->datos())
            ->assertRedirect(route('admin.configuracion.edit'));

        $this->assertSame('1,2,3,4,5', Configuracion::obtenerValor('taller_dias'));
        $this->assertSame('08:30', Configuracion::obtenerValor('taller_hora_inicio'));
        $this->assertSame(30, Configuracion::obtenerDuracionCita());
        $this->assertSame('593999999999', Configuracion::obtenerWhatsapp());
        $this->assertDatabaseHas('configuracion_log', ['clave' => 'taller_dias', 'valor_nuevo' => '1,2,3,4,5']);
    }

    public function test_dias_o_horas_invalidos_se_rechazan(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.update'), $this->datos([
                'taller_dias' => '1,9',
                'taller_hora_fin' => '08:00',
            ]))
            ->assertSessionHasErrors(['taller_dias', 'taller_hora_fin']);

        $this->assertNull(Configuracion::obtenerValor('taller_dias'));
    }

    public function test_el_whatsapp_debe_ser_solo_digitos(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.update'), $this->datos([
                'negocio_whatsapp' => '+593 999',
            ]))
            ->assertSessionHasErrors('negocio_whatsapp');
    }
}
