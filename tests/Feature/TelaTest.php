<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Tela;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TelaTest extends TestCase
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
            'nombre' => 'Lana merino azul',
            'composicion' => '100 % lana merino',
            'color' => 'Azul noche',
            'recargo' => 25,
            'activo' => '1',
        ], $overrides);
    }

    public function test_admin_crea_una_tela(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.telas.store'), $this->datos())
            ->assertRedirect(route('admin.telas.index'));

        $this->assertDatabaseHas('tela', [
            'nombre' => 'Lana merino azul',
            'color' => 'Azul noche',
            'recargo' => 25,
            'activo' => true,
        ]);
    }

    public function test_una_tela_se_puede_desactivar(): void
    {
        $tela = Tela::create($this->datos());

        $this->actingAs($this->admin)
            ->put(route('admin.telas.update', $tela->tela_id), $this->datos([
                'activo' => '0',
                'recargo' => 40,
            ]))
            ->assertRedirect(route('admin.telas.index'));

        $this->assertFalse($tela->fresh()->activo);
        $this->assertEquals(40, (float) $tela->fresh()->recargo);
    }

    public function test_una_tela_sin_color_es_rechazada(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.telas.store'), $this->datos(['color' => '']))
            ->assertSessionHasErrors('color');

        $this->assertDatabaseCount('tela', 0);
    }

    public function test_una_tela_se_puede_eliminar(): void
    {
        $tela = Tela::create($this->datos());

        $this->actingAs($this->admin)
            ->delete(route('admin.telas.destroy', $tela->tela_id))
            ->assertRedirect(route('admin.telas.index'));

        $this->assertDatabaseMissing('tela', ['tela_id' => $tela->tela_id]);
    }
}
