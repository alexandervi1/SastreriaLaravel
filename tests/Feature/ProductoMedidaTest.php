<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Tela;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductoMedidaTest extends TestCase
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
        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Ternos']]);
        $this->activarVentaPorTalla();

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
            'nombre' => 'Terno a medida',
            'precio' => 250,
            'categoria_id' => 1,
            'vende_por_talla' => '0',
            'vende_a_medida' => '1',
            'precio_medida' => 300,
            'dias_confeccion' => 10,
        ], $overrides);
    }

    public function test_un_modelo_a_medida_exige_precio_y_dias(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.store'), $this->datos([
                'precio_medida' => '',
                'dias_confeccion' => '',
            ]))
            ->assertSessionHasErrors(['precio_medida', 'dias_confeccion']);

        $this->assertDatabaseCount('producto', 0);
    }

    public function test_un_modelo_debe_tener_al_menos_un_modo_de_venta(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.store'), $this->datos([
                'vende_por_talla' => '0',
                'vende_a_medida' => '0',
            ]))
            ->assertSessionHasErrors('vende_por_talla');
    }

    public function test_la_ficha_muestra_la_pestana_a_medida_con_telas_y_fecha(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno a medida',
            'precio' => 250,
            'categoria_id' => 1,
            'vende_por_talla' => false,
            'vende_a_medida' => true,
            'precio_medida' => 300,
            'dias_confeccion' => 10,
        ]);

        Tela::create([
            'nombre' => 'Lana merino azul',
            'composicion' => '100 % lana merino',
            'color' => 'Azul noche',
            'recargo' => 25,
            'activo' => true,
        ]);

        $response = $this->actingAs(Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
        ]))->get(route('productos.show', $producto->slug));

        $response->assertOk();
        $response->assertSee('Encargar a medida');
        $response->assertSee('Lana merino azul');
        $response->assertSee('Recargo: $25.00');
        $response->assertSee('Entrega estimada:');
        $response->assertSee('Encargar a medida');
    }

    public function test_una_tela_inactiva_no_se_ofrece(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno a medida',
            'precio' => 250,
            'categoria_id' => 1,
            'vende_por_talla' => false,
            'vende_a_medida' => true,
            'precio_medida' => 300,
            'dias_confeccion' => 10,
        ]);

        Tela::create([
            'nombre' => 'Tela retirada',
            'color' => 'Gris',
            'recargo' => 25,
            'activo' => false,
        ]);

        $this->get(route('productos.show', $producto->slug))
            ->assertOk()
            ->assertDontSee('Tela retirada');
    }
}
