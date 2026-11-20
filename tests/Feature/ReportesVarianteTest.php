<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PedidoLinea;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportesVarianteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([['rol_id' => 1, 'nombre' => 'Administrador']]);
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

        $this->producto = Producto::create([
            'nombre' => 'Terno Slim Fit Azul Noche',
            'precio' => 250,
            'categoria_id' => 1,
        ]);
    }

    private function variante(string $talla, int $stock, bool $activo = true): ProductoVariante
    {
        return ProductoVariante::create([
            'producto_id' => $this->producto->producto_id,
            'talla' => $talla,
            'color' => 'Azul noche',
            'sku' => 'SKU-' . $talla . '-' . ($activo ? 'A' : 'I'),
            'stock' => $stock,
            'activo' => $activo,
        ]);
    }

    public function test_stock_por_categoria_suma_solo_variantes_activas(): void
    {
        $this->variante('40', 3);
        $this->variante('42', 5);
        $this->variante('44', 9, false);

        $response = $this->actingAs($this->admin)->get(route('admin.reportes.stock-categoria'));
        $response->assertOk();

        $fila = collect($response->viewData('stockCategorias'))->first();
        $this->assertSame(8, (int) $fila->total_stock);
    }

    public function test_mas_vendidos_desglosa_por_talla(): void
    {
        $cuarenta = $this->variante('40', 10);
        $cuarentaYDos = $this->variante('42', 10);

        $pedido = Pedido::create([
            'usuario_id' => $this->admin->usuario_id,
            'subtotal' => 750,
            'iva' => 112.5,
            'total' => 862.5,
            'estado' => 'Entregado',
        ]);

        foreach ([[$cuarenta, 2], [$cuarentaYDos, 1]] as [$variante, $cantidad]) {
            PedidoLinea::create([
                'pedido_id' => $pedido->pedido_id,
                'producto_id' => $this->producto->producto_id,
                'variante_id' => $variante->variante_id,
                'producto_nombre' => $this->producto->nombre,
                'talla' => $variante->talla,
                'color' => $variante->color,
                'cantidad' => $cantidad,
                'precio_unitario' => 250,
                'subtotal' => 250 * $cantidad,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.reportes.mas-vendidos'));
        $response->assertOk();

        $producto = collect($response->viewData('productos'))->first();
        $this->assertSame(3, $producto->cantidad_vendida);
        $this->assertSame(['40' => 2, '42' => 1], $producto->tallas->pluck('cantidad', 'talla')->all());
    }

    public function test_bajo_stock_lista_variantes_bajo_el_umbral(): void
    {
        $this->variante('40', 2);
        $this->variante('42', 50);

        $response = $this->actingAs($this->admin)->get(route('admin.reportes.bajo-stock'));
        $response->assertOk();

        $variantes = $response->viewData('variantes');
        $this->assertCount(1, $variantes);
        $this->assertSame('40', $variantes->first()->talla);
    }
}
