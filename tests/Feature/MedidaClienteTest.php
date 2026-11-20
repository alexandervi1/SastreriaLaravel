<?php

namespace Tests\Feature;

use App\Models\MedidaCliente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MedidaClienteTest extends TestCase
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

        $this->cliente = $this->usuario('cliente1', 2);
        $this->otro = $this->usuario('cliente2', 2);
        $this->admin = $this->usuario('admin1', 1);
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

    private function datos(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Mis medidas',
            'patron' => 'caballero',
            'pecho' => 100.5,
            'cintura' => 90,
            'hombros' => 45,
            'largo_saco' => 75,
            'largo_manga' => 62,
            'entrepierna' => 80,
            'largo_pantalon' => 105,
            'estatura' => 180,
        ], $overrides);
    }

    public function test_el_cliente_crea_su_perfil(): void
    {
        $this->actingAs($this->cliente)
            ->post(route('medidas.store'), $this->datos())
            ->assertRedirect(route('medidas.index'));

        $this->assertDatabaseHas('medida_cliente', [
            'usuario_id' => $this->cliente->usuario_id,
            'nombre' => 'Mis medidas',
            'pecho' => 100.5,
        ]);
    }

    public function test_el_perfil_propio_se_edita_y_elimina(): void
    {
        $medida = MedidaCliente::create($this->datos() + ['usuario_id' => $this->cliente->usuario_id]);

        $this->actingAs($this->cliente)
            ->put(route('medidas.update', $medida->medida_id), $this->datos(['nombre' => 'Actualizado']))
            ->assertRedirect(route('medidas.index'));

        $this->assertSame('Actualizado', $medida->fresh()->nombre);

        $this->actingAs($this->cliente)
            ->delete(route('medidas.destroy', $medida->medida_id))
            ->assertRedirect(route('medidas.index'));

        $this->assertDatabaseMissing('medida_cliente', ['medida_id' => $medida->medida_id]);
    }

    public function test_un_cliente_no_ve_ni_edita_medidas_ajenas(): void
    {
        $ajena = MedidaCliente::create($this->datos() + ['usuario_id' => $this->otro->usuario_id]);

        $this->actingAs($this->cliente)
            ->get(route('medidas.edit', $ajena->medida_id))
            ->assertNotFound();

        $this->actingAs($this->cliente)
            ->put(route('medidas.update', $ajena->medida_id), $this->datos(['nombre' => 'Robado']))
            ->assertNotFound();

        $this->assertSame('Mis medidas', $ajena->fresh()->nombre);
    }

    public function test_la_estatura_fuera_de_rango_es_rechazada(): void
    {
        $this->actingAs($this->cliente)
            ->post(route('medidas.store'), $this->datos(['estatura' => 300]))
            ->assertSessionHasErrors('estatura');

        $this->assertDatabaseCount('medida_cliente', 0);
    }

    public function test_el_admin_puede_marcar_medidas_tomadas_en_taller(): void
    {
        $medida = MedidaCliente::create($this->datos() + ['usuario_id' => $this->cliente->usuario_id]);

        $this->actingAs($this->admin)
            ->put(route('admin.clientes.medidas.update', [$this->cliente->usuario_id, $medida->medida_id]), $this->datos([
                'tomadas_en_taller' => '1',
            ]))
            ->assertRedirect(route('admin.clientes.edit', $this->cliente->usuario_id));

        $this->assertTrue($medida->fresh()->tomadas_en_taller);
    }

    public function test_el_admin_no_edita_medidas_de_otro_cliente_por_la_ruta_equivocada(): void
    {
        $medida = MedidaCliente::create($this->datos() + ['usuario_id' => $this->otro->usuario_id]);

        $this->actingAs($this->admin)
            ->put(route('admin.clientes.medidas.update', [$this->cliente->usuario_id, $medida->medida_id]), $this->datos())
            ->assertNotFound();
    }

    public function test_tiene_medidas_minimas(): void
    {
        $completa = MedidaCliente::create($this->datos() + ['usuario_id' => $this->cliente->usuario_id]);
        $incompleta = MedidaCliente::create(['usuario_id' => $this->cliente->usuario_id, 'nombre' => 'Parcial', 'pecho' => 100]);

        $this->assertTrue($completa->tieneMedidasMinimas());
        $this->assertFalse($incompleta->tieneMedidasMinimas());
    }
}
