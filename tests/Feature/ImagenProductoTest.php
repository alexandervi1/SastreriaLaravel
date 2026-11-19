<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImagenProductoTest extends TestCase
{
    use RefreshDatabase;

    /** PNG 1×1 generado sin ext-gd (el entorno de pruebas no la trae). */
    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR4nGNgYGD4DwABBAEAX+XDSwAAAABJRU5ErkJggg==';

    /** PNG 5000×10 para superar `dimensions:max_width=4000`. */
    private const PNG_ANCHO_EXCESIVO = 'iVBORw0KGgoAAAANSUhEUgAAE4gAAAAKCAYAAABY1rRLAAAA8klEQVR4nO3OoQEAMAwCMP5/uhMcwUREfJLkAAAAAAAAAAAAAAAAAAAAAAAAAPjCPAAAAAAAAAAAAAAAAAAAAAAAAABAzQMAAAAAAAAAAAAAAAAAAAAAAAAA1DwAAAAAAAAAAAAAAAAAAAAAAAAAQM0DAAAAAAAAAAAAAAAAAAAAAAAAANQ8AAAAAAAAAAAAAAAAAAAAAAAAAEDNAwAAAAAAAAAAAAAAAAAAAAAAAADUPAAAAAAAAAAAAAAAAAAAAAAAAABAzQMAAAAAAAAAAAAAAAAAAAAAAAAA1DwAAAAAAAAAAAAAAAAAAAAAAAAAcOQeF5SYD3ZQOvEAAAAASUVORK5CYII=';

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);

        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Pescados']]);

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
            'nombre' => 'Producto de prueba',
            'descripcion' => 'Descripción',
            'precio' => 10,
            'categoria_id' => 1,
        ], $overrides);
    }

    private function png(string $base64, string $nombre = 'foto.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nombre, base64_decode($base64));
    }

    public function test_rechaza_un_archivo_php_disfrazado(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.store'), $this->datos([
                'imagen' => UploadedFile::fake()->create('x.php', 10, 'application/x-php'),
            ]))
            ->assertSessionHasErrors('imagen');

        $this->assertDatabaseMissing('producto', ['nombre' => 'Producto de prueba']);
    }

    public function test_rechaza_imagenes_demasiado_grandes_en_dimensiones(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.store'), $this->datos([
                'imagen' => $this->png(self::PNG_ANCHO_EXCESIVO, 'enorme.png'),
            ]))
            ->assertSessionHasErrors('imagen');

        $this->assertDatabaseMissing('producto', ['nombre' => 'Producto de prueba']);
    }

    public function test_acepta_una_imagen_valida_y_guarda_un_nombre_aleatorio(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.store'), $this->datos([
                'imagen' => $this->png(self::PNG_1X1),
            ]))
            ->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('nombre', 'Producto de prueba')->firstOrFail();

        $this->assertStringStartsWith('img/productos/', $producto->imagen_url);
        $this->assertStringEndsWith('.png', $producto->imagen_url);
        $this->assertStringNotContainsString('foto.png', $producto->imagen_url);

        File::delete(public_path($producto->imagen_url));
    }
}
