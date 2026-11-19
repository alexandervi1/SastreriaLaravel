<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([['rol_id' => 2, 'nombre' => 'Cliente']]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);

        Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'admin1@example.com',
        ]);

        config(['app.name' => '<NOMBRE_NEGOCIO>']);

        $this->producto = Producto::create([
            'nombre' => 'Terno Slim Fit',
            'descripcion' => 'Un terno de lana para ocasiones formales.',
            'precio' => 250,
            'categoria_id' => 1,
            'imagen_url' => 'img/placeholder.svg',
        ]);
    }

    public function test_la_home_lleva_descripcion_canonical_y_open_graph(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:image"', false);
    }

    public function test_la_ficha_usa_el_nombre_y_la_imagen_del_producto(): void
    {
        $response = $this->get(route('productos.show', $this->producto->slug));

        $response->assertOk();
        $response->assertSee('<meta property="og:title" content="Terno Slim Fit">', false);
        $response->assertSee('property="og:image" content="' . asset('img/placeholder.svg') . '"', false);
        $response->assertSee('Terno Slim Fit ·', false);
    }

    public function test_la_categoria_tiene_su_propia_descripcion(): void
    {
        $response = $this->get(route('categorias.show', 1));

        $response->assertOk();
        $response->assertSee('Ternos y accesorios de la categoría Ternos.', false);
    }
}
