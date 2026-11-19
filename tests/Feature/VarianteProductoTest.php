<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VarianteProductoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
        ]);

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
            'nombre' => 'Terno Slim Fit Azul Noche',
            'precio' => 250,
            'stock' => 0,
            'categoria_id' => 1,
        ]);
    }

    private function datos(array $overrides = []): array
    {
        return array_merge([
            'talla' => '40',
            'color' => 'Azul noche',
            'sku' => 'TER-AZUL-40',
            'stock' => 5,
            'activo' => '1',
        ], $overrides);
    }

    private function crearVariante(array $overrides = []): ProductoVariante
    {
        return ProductoVariante::create(array_merge([
            'producto_id' => $this->producto->producto_id,
            'talla' => '40',
            'color' => 'Azul noche',
            'sku' => 'TER-AZUL-40',
            'stock' => 5,
            'activo' => true,
        ], $overrides));
    }

    public function test_admin_crea_una_variante(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.variantes.store', $this->producto->producto_id), $this->datos())
            ->assertRedirect(route('admin.productos.edit', $this->producto->producto_id));

        $this->assertDatabaseHas('producto_variante', [
            'producto_id' => $this->producto->producto_id,
            'talla' => '40',
            'color' => 'Azul noche',
            'sku' => 'TER-AZUL-40',
            'stock' => 5,
        ]);
    }

    public function test_sku_duplicado_es_rechazado(): void
    {
        $this->crearVariante();

        $this->actingAs($this->admin)
            ->post(route('admin.productos.variantes.store', $this->producto->producto_id), $this->datos([
                'talla' => '42',
                'color' => 'Negro',
            ]))
            ->assertSessionHasErrors('sku');

        $this->assertDatabaseCount('producto_variante', 1);
    }

    public function test_talla_y_color_duplicados_en_el_mismo_modelo_son_rechazados(): void
    {
        $this->crearVariante();

        $this->actingAs($this->admin)
            ->post(route('admin.productos.variantes.store', $this->producto->producto_id), $this->datos([
                'sku' => 'TER-AZUL-40-B',
            ]))
            ->assertSessionHasErrors('talla');

        $this->assertDatabaseCount('producto_variante', 1);
    }

    public function test_misma_talla_y_color_en_otro_modelo_es_valido(): void
    {
        $this->crearVariante();

        $otro = Producto::create([
            'nombre' => 'Terno Clásico Negro',
            'precio' => 300,
            'stock' => 0,
            'categoria_id' => 1,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.productos.variantes.store', $otro->producto_id), $this->datos([
                'sku' => 'TER-AZUL-40-OTRO',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('producto_variante', 2);
    }

    public function test_una_variante_puede_desactivarse(): void
    {
        $variante = $this->crearVariante();

        $this->actingAs($this->admin)
            ->put(route('admin.productos.variantes.update', [
                $this->producto->producto_id,
                $variante->variante_id,
            ]), $this->datos([
                'stock' => 0,
                'activo' => '0',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertFalse($variante->fresh()->activo);
        $this->assertSame(0, $variante->fresh()->stock);
    }

    public function test_stock_total_suma_solo_las_variantes_activas(): void
    {
        $this->crearVariante(['talla' => '40', 'sku' => 'A', 'stock' => 3]);
        $this->crearVariante(['talla' => '42', 'sku' => 'B', 'stock' => 5]);
        $this->crearVariante(['talla' => '44', 'sku' => 'C', 'stock' => 9, 'activo' => false]);

        $this->assertSame(8, $this->producto->fresh()->stock_total);
    }

    public function test_el_slug_se_genera_y_no_se_repite(): void
    {
        $otro = Producto::create([
            'nombre' => 'Terno Slim Fit Azul Noche',
            'precio' => 250,
            'stock' => 0,
            'categoria_id' => 1,
        ]);

        $this->assertSame('terno-slim-fit-azul-noche', $this->producto->slug);
        $this->assertSame('terno-slim-fit-azul-noche-2', $otro->slug);
    }
}
