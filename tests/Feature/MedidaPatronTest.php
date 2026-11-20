<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\MedidaCliente;
use App\Models\Pedido;
use App\Models\PedidoLinea;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MedidaPatronTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Usuario $cliente;

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

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 2',
            'correo' => 'cliente1@example.com',
        ]);
    }

    private function caballero(array $overrides = []): MedidaCliente
    {
        return new MedidaCliente(array_merge([
            'patron' => 'caballero',
            'pecho' => 100,
            'cintura' => 90,
            'hombros' => 45,
            'largo_manga' => 62,
            'largo_saco' => 75,
        ], $overrides));
    }

    private function dama(array $overrides = []): MedidaCliente
    {
        return new MedidaCliente(array_merge([
            'patron' => 'dama',
            'busto' => 92,
            'cintura' => 74,
            'cadera' => 98,
            'hombros' => 39,
            'largo_manga' => 58,
            'talle_delantero' => 40,
            'talle_espalda' => 39,
            'largo_saco' => 68,
        ], $overrides));
    }

    public function test_un_abrigo_de_caballero_no_exige_medidas_de_pantalon(): void
    {
        $this->assertTrue($this->caballero()->tieneMedidasMinimas('abrigo'));
    }

    public function test_un_terno_de_caballero_exige_entrepierna_y_largo_de_pantalon(): void
    {
        $medida = $this->caballero();

        $this->assertFalse($medida->tieneMedidasMinimas('terno_2p'));

        $medida->entrepierna = 80;
        $medida->largo_pantalon = 105;

        $this->assertTrue($medida->tieneMedidasMinimas('terno_2p'));
    }

    public function test_un_abrigo_de_dama_no_exige_largo_de_falda(): void
    {
        $this->assertTrue($this->dama()->tieneMedidasMinimas('abrigo'));
        $this->assertFalse($this->dama()->tieneMedidasMinimas('falda'));

        $this->assertTrue($this->dama(['largo_falda' => 70])->tieneMedidasMinimas('falda'));
    }

    public function test_la_dama_exige_talle_delantero_y_espalda(): void
    {
        $medida = $this->dama(['talle_delantero' => null]);

        $this->assertFalse($medida->tieneMedidasMinimas('abrigo'));
    }

    public function test_el_nino_exige_largo_de_pantalon_para_un_terno(): void
    {
        $medida = new MedidaCliente([
            'patron' => 'nino',
            'pecho' => 70,
            'cintura' => 65,
            'largo_manga' => 45,
            'largo_saco' => 50,
        ]);

        $this->assertTrue($medida->tieneMedidasMinimas('abrigo'));
        $this->assertFalse($medida->tieneMedidasMinimas('terno_2p'));

        $medida->largo_pantalon = 80;
        $this->assertTrue($medida->tieneMedidasMinimas('terno_2p'));
    }

    public function test_la_copia_de_medidas_incluye_el_patron(): void
    {
        $medida = $this->dama();
        $medida->setAttribute('usuario_id', $this->cliente->usuario_id);

        $copia = $medida->comoArreglo();

        $this->assertSame('dama', $copia['patron']);
        $this->assertSame(92.0, $copia['busto']);
    }

    public function test_la_orden_de_trabajo_de_dama_muestra_busto_y_no_pecho(): void
    {
        $producto = Producto::create([
            'nombre' => 'Abrigo de dama',
            'precio' => 90,
            'precio_medida' => 90,
            'dias_confeccion' => 10,
            'categoria_id' => 1,
            'publico' => 'dama',
            'tipo_prenda' => 'abrigo',
        ]);

        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now(),
            'subtotal' => 90,
            'iva' => 0,
            'iva_pct' => 0,
            'total' => 90,
            'estado' => 'Pendiente',
        ]);

        PedidoLinea::create([
            'pedido_id' => $pedido->pedido_id,
            'producto_id' => $producto->producto_id,
            'tipo' => 'medida',
            'producto_nombre' => $producto->nombre,
            'cantidad' => 1,
            'precio_unitario' => 90,
            'subtotal' => 90,
            'medidas' => [
                'patron' => 'dama',
                'busto' => 92,
                'pecho' => 100,
                'cintura' => 74,
                'largo_manga' => 58,
                'largo_saco' => 68,
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pedidos.orden-trabajo', $pedido->pedido_id));

        $response->assertOk();
        $response->assertSee('Dama');
        $response->assertSee('Abrigo');
        $response->assertSee('Busto');
        $response->assertDontSee('Pecho');
    }
}
