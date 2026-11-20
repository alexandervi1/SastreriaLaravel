<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogoTipoPublicoTest extends TestCase
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
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Sastrería']]);

        $this->admin = Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 1',
            'correo' => 'admin1@example.com',
        ]);
    }

    private function producto(string $nombre, string $publico, string $tipo): Producto
    {
        return Producto::create([
            'nombre' => $nombre,
            'precio' => 90,
            'precio_medida' => 90,
            'dias_confeccion' => 10,
            'categoria_id' => 1,
            'publico' => $publico,
            'tipo_prenda' => $tipo,
        ]);
    }

    public function test_las_filas_existentes_quedan_como_terno_de_dos_piezas_de_caballero(): void
    {
        $producto = Producto::create([
            'nombre' => 'Modelo heredado',
            'precio' => 90,
            'categoria_id' => 1,
        ]);

        $producto->refresh();

        $this->assertSame('terno_2p', $producto->tipo_prenda);
        $this->assertSame('caballero', $producto->publico);
    }

    public function test_el_catalogo_filtra_por_publico(): void
    {
        $this->producto('Terno de dama', 'dama', 'terno_2p');
        $this->producto('Terno de caballero', 'caballero', 'terno_2p');
        $this->producto('Terno de niño', 'nino', 'terno_2p');

        $response = $this->get(route('home', ['publico' => 'dama']));

        $response->assertOk();
        $response->assertSee('Terno de dama');
        $response->assertDontSee('Terno de caballero');
        $response->assertDontSee('Terno de niño');
    }

    public function test_el_catalogo_filtra_por_tipo_de_prenda(): void
    {
        $this->producto('Abrigo de dama', 'dama', 'abrigo');
        $this->producto('Saco de caballero', 'caballero', 'saco');

        $response = $this->get(route('home', ['tipo_prenda' => 'abrigo']));

        $response->assertOk();
        $response->assertSee('Abrigo de dama');
        $response->assertDontSee('Saco de caballero');
    }

    public function test_la_home_enlaza_a_los_tres_publicos(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('publico=caballero', false);
        $response->assertSee('publico=dama', false);
        $response->assertSee('publico=nino', false);
    }

    public function test_el_admin_rechaza_tipo_o_publico_fuera_del_catalogo(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.store'), [
                'nombre' => 'Modelo inválido',
                'precio' => 90,
                'categoria_id' => 1,
                'tipo_prenda' => 'traje_espacial',
                'publico' => 'marciano',
                'vende_a_medida' => 1,
                'precio_medida' => 90,
                'dias_confeccion' => 10,
            ])
            ->assertSessionHasErrors(['tipo_prenda', 'publico']);

        $this->assertDatabaseMissing('producto', ['nombre' => 'Modelo inválido']);
    }
}
