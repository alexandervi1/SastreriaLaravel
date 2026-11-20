<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductoImagenTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR4nGNgYGD4DwABBAEAX+XDSwAAAABJRU5ErkJggg==';

    private Usuario $admin;

    private Producto $producto;

    /** @var array<int, string> */
    private array $archivosPrevios = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->archivosPrevios = glob(public_path('img/productos/*')) ?: [];

        Rol::insert([['rol_id' => 1, 'nombre' => 'Administrador']]);
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);

        $this->admin = Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 1',
            'correo' => 'admin1@example.com',
        ]);

        $this->producto = Producto::create([
            'nombre' => 'Terno Azul Noche',
            'precio' => 250,
            'categoria_id' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        foreach (array_diff(glob(public_path('img/productos/*')) ?: [], $this->archivosPrevios) as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);
            }
        }

        parent::tearDown();
    }

    private function png(string $nombre = 'foto.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nombre, base64_decode(self::PNG_1X1));
    }

    private function imagen(string $alt = 'Vista frontal', int $orden = 0): ProductoImagen
    {
        return $this->producto->imagenes()->create([
            'url' => 'img/productos/' . uniqid() . '.png',
            'orden' => $orden,
            'alt' => $alt,
        ]);
    }

    public function test_se_suben_varias_imagenes_con_alt(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.imagenes.store', $this->producto->producto_id), [
                'imagenes' => [$this->png('a.png'), $this->png('b.png')],
                'alt' => 'Terno azul, vista frontal',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('producto_imagen', 2);
        $this->assertSame('Terno azul, vista frontal', ProductoImagen::first()->alt);
    }

    public function test_no_se_superan_las_ocho_imagenes(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $this->imagen('Alt ' . $i, $i);
        }

        $this->actingAs($this->admin)
            ->post(route('admin.productos.imagenes.store', $this->producto->producto_id), [
                'imagenes' => [$this->png('a.png'), $this->png('b.png')],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('producto_imagen', 7);
    }

    public function test_reordenar_intercambia_el_orden(): void
    {
        $primera = $this->imagen('Primera', 1);
        $segunda = $this->imagen('Segunda', 2);

        $this->actingAs($this->admin)
            ->put(route('admin.productos.imagenes.mover', [$this->producto->producto_id, $segunda->imagen_id, 'subir']))
            ->assertSessionHas('success');

        $this->assertSame(2, $primera->fresh()->orden);
        $this->assertSame(1, $segunda->fresh()->orden);
    }

    public function test_elegir_portada_actualiza_el_producto_y_la_ficha(): void
    {
        $imagen = $this->imagen('Portada elegida');

        $this->actingAs($this->admin)
            ->put(route('admin.productos.imagenes.portada', [$this->producto->producto_id, $imagen->imagen_id]))
            ->assertSessionHas('success');

        $this->assertSame($imagen->url, $this->producto->fresh()->imagen_url);

        $this->get(route('productos.show', $this->producto->slug))
            ->assertOk()
            ->assertSee('Portada elegida');
    }

    public function test_el_alt_se_actualiza_y_es_obligatorio(): void
    {
        $imagen = $this->imagen();

        $this->actingAs($this->admin)
            ->put(route('admin.productos.imagenes.update', [$this->producto->producto_id, $imagen->imagen_id]), ['alt' => ''])
            ->assertSessionHasErrors('alt');

        $this->actingAs($this->admin)
            ->put(route('admin.productos.imagenes.update', [$this->producto->producto_id, $imagen->imagen_id]), ['alt' => 'Terno de perfil'])
            ->assertSessionHas('success');

        $this->assertSame('Terno de perfil', $imagen->fresh()->alt);
    }

    public function test_una_imagen_no_se_borra_desde_otro_producto(): void
    {
        $imagen = $this->imagen();
        $otro = Producto::create(['nombre' => 'Otro terno', 'precio' => 100, 'categoria_id' => 1]);

        $this->actingAs($this->admin)
            ->delete(route('admin.productos.imagenes.destroy', [$otro->producto_id, $imagen->imagen_id]))
            ->assertNotFound();

        $this->assertDatabaseHas('producto_imagen', ['imagen_id' => $imagen->imagen_id]);
    }
}
