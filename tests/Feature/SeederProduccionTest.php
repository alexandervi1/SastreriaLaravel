<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederProduccionTest extends TestCase
{
    use RefreshDatabase;

    private const CLAVES = [
        'INITIAL_ADMIN_USER',
        'INITIAL_ADMIN_PASSWORD',
        'INITIAL_ADMIN_EMAIL',
        'INITIAL_ADMIN_NAME',
        'SEED_DEMO',
    ];

    /** @var array<string, string|false> */
    private array $originales = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::CLAVES as $clave) {
            $this->originales[$clave] = getenv($clave);
        }

        foreach (self::CLAVES as $clave) {
            if ($clave === 'SEED_DEMO') {
                putenv('SEED_DEMO');
            } else {
                putenv("{$clave}=");
            }
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->originales as $clave => $valor) {
            $valor === false ? putenv($clave) : putenv("{$clave}={$valor}");
        }

        parent::tearDown();
    }

    private function enProduccion(): void
    {
        $this->app['env'] = 'production';
    }

    private function sembrar(): void
    {
        $seeder = new DatabaseSeeder();
        $seeder->setContainer($this->app);
        $seeder->__invoke();
    }

    public function test_produccion_sin_credenciales_lanza_excepcion(): void
    {
        $this->enProduccion();

        $this->expectException(\RuntimeException::class);

        $this->sembrar();
    }

    public function test_produccion_con_password_por_defecto_lanza_excepcion(): void
    {
        $this->enProduccion();
        putenv('INITIAL_ADMIN_USER=admin');
        putenv('INITIAL_ADMIN_PASSWORD=admin123');
        putenv('INITIAL_ADMIN_EMAIL=admin@example.com');

        $this->expectException(\RuntimeException::class);

        $this->sembrar();
    }

    public function test_testing_sin_variables_crea_el_admin_por_defecto(): void
    {
        $this->sembrar();

        $admin = \App\Models\Usuario::where('rol_id', 1)->first();

        $this->assertNotNull($admin);
        $this->assertSame('admin123', $admin->nombre_usuario);
        $this->assertTrue(Hash::check('admin123', $admin->password));
    }

    public function test_produccion_sin_credenciales_no_deja_datos_a_medias(): void
    {
        $this->enProduccion();

        try {
            $this->sembrar();
            $this->fail('Se esperaba que el sembrado fallara sin credenciales.');
        } catch (\RuntimeException $e) {
            // Esperado: el seeder rechaza arrancar en producción sin credenciales.
        }

        $this->assertDatabaseCount('rol', 0);
        $this->assertDatabaseCount('configuracion', 0);
        $this->assertDatabaseCount('categoria', 0);
        $this->assertDatabaseCount('usuario', 0);
    }

    public function test_el_catalogo_demo_se_siembra_por_defecto(): void
    {
        $this->sembrar();

        $this->assertSame(11, \App\Models\Producto::count());
        $this->assertDatabaseCount('tela', 5);
        $this->assertDatabaseCount('producto_variante', 0);
        $this->assertSame(0, \App\Models\Producto::where('vende_por_talla', true)->count());
        $this->assertSame(11, \App\Models\Producto::where('vende_a_medida', true)->count());
        $this->assertSame(3, \App\Models\Producto::distinct()->count('publico'));
        $this->assertTrue(\App\Models\Producto::where('tipo_prenda', 'terno_2p')->where('publico', 'caballero')->exists());
        $this->assertTrue(\App\Models\Producto::where('tipo_prenda', 'terno_3p')->where('publico', 'caballero')->exists());
        $this->assertTrue(\App\Models\Producto::where('publico', 'dama')->where('tipo_prenda', 'abrigo')->exists());
        $this->assertTrue(\App\Models\Producto::where('publico', 'nino')->exists());
        foreach (\App\Models\Producto::all() as $producto) {
            $this->assertStringStartsWith('img/productos/modelos/', $producto->imagen_url);
            $this->assertFileExists(public_path($producto->imagen_url));
        }
    }

    public function test_los_precios_demo_son_reales_y_las_telas_no_se_disparan(): void
    {
        $this->sembrar();

        $this->assertDatabaseHas('producto', ['nombre' => 'Terno clásico de 2 piezas', 'precio' => 90]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Terno de 3 piezas', 'precio' => 100]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Abrigo de paño', 'precio' => 100]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Smoking Midnight', 'precio' => 100]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Saco de sastre', 'precio' => 85]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Chaqueta de sastre', 'precio' => 85]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Terno de niño', 'precio' => 80]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Chaleco de gala', 'precio' => 80]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Pantalón de vestir', 'precio' => 80]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Falda de sastre', 'precio' => 80]);

        $this->assertGreaterThanOrEqual(15, \App\Models\Tela::min('recargo'));
        $this->assertLessThanOrEqual(40, \App\Models\Tela::max('recargo'));
        $this->assertDatabaseHas('producto', ['nombre' => 'Abrigo de paño', 'categoria_id' => 5]);
        $this->assertDatabaseHas('producto', ['nombre' => 'Falda de sastre', 'categoria_id' => 6]);
    }

    public function test_seed_demo_false_omite_el_catalogo_demo(): void
    {
        putenv('SEED_DEMO=false');

        $this->sembrar();

        $this->assertSame(0, \App\Models\Producto::count());
        $this->assertDatabaseCount('rol', 3);
        $this->assertDatabaseCount('categoria', 6);
        $this->assertDatabaseCount('configuracion', 19);
    }

    public function test_produccion_siembra_admin_sin_datos_personales_ni_catalogo(): void
    {
        $this->enProduccion();
        putenv('INITIAL_ADMIN_USER=admin_prod');
        putenv('INITIAL_ADMIN_PASSWORD=claveSeguraDeProduccion');
        putenv('INITIAL_ADMIN_EMAIL=admin@sastreria.example');
        putenv('INITIAL_ADMIN_NAME=Encargado');

        $this->sembrar();

        $admin = \App\Models\Usuario::where('rol_id', 1)->firstOrFail();

        $this->assertSame('admin_prod', $admin->nombre_usuario);
        $this->assertSame('Encargado', $admin->nombre_completo);
        $this->assertSame('', $admin->direccion);
        $this->assertNull($admin->telefono);
        $this->assertNotNull($admin->password_changed_at);
        $this->assertSame(0, \App\Models\Producto::count());
    }
}
