<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
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
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);

        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Pescados']]);
        Configuracion::create(['clave' => 'iva_actual', 'valor' => '15']);

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

    public function test_admin_list_only_shows_administrators(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.administradores.index'));

        $response->assertOk();
        $response->assertSee('admin1');
        $response->assertDontSee('cliente1');
    }

    public function test_administrator_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.administradores.store'), [
                'nombre_completo' => 'Admin Dos',
                'nombre_usuario' => 'admin2',
                'correo' => 'admin2@example.com',
                'telefono' => '0999999999',
                'direccion' => 'Calle 3',
                'password' => 'secret123',
            ])
            ->assertRedirect(route('admin.administradores.index'));

        $this->assertDatabaseHas('usuario', [
            'nombre_usuario' => 'admin2',
            'rol_id' => 1,
        ]);
    }

    public function test_admin_self_password_change_requires_current_password(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.administradores.update', $this->admin->usuario_id), [
                'nombre_completo' => 'Admin Uno',
                'nombre_usuario' => 'admin1',
                'correo' => 'admin1@example.com',
                'direccion' => 'Calle 1',
                'password' => 'nuevaClave123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('secret123', $this->admin->fresh()->password));
    }

    public function test_admin_self_password_change_succeeds_with_current_password(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.administradores.update', $this->admin->usuario_id), [
                'nombre_completo' => 'Admin Uno',
                'nombre_usuario' => 'admin1',
                'correo' => 'admin1@example.com',
                'direccion' => 'Calle 1',
                'current_password' => 'secret123',
                'password' => 'nuevaClave123',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertTrue(Hash::check('nuevaClave123', $this->admin->fresh()->password));
        $this->assertDatabaseHas('configuracion_log', ['clave' => 'password', 'valor_nuevo' => 'actualizada']);
    }

    public function test_changing_another_administrator_password_needs_no_current_password(): void
    {
        $otroAdmin = Usuario::create([
            'nombre_usuario' => 'admin2',
            'nombre_completo' => 'Admin Dos',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 3',
            'correo' => 'admin2@example.com',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.administradores.update', $otroAdmin->usuario_id), [
                'nombre_completo' => 'Admin Dos',
                'nombre_usuario' => 'admin2',
                'correo' => 'admin2@example.com',
                'direccion' => 'Calle 3',
                'password' => 'otraClave123',
            ])
            ->assertRedirect(route('admin.administradores.index'));

        $this->assertTrue(Hash::check('otraClave123', $otroAdmin->fresh()->password));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_administrator_email_must_be_unique(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.administradores.store'), [
                'nombre_completo' => 'Admin Duplicado',
                'nombre_usuario' => 'admin_dup',
                'correo' => 'admin1@example.com',
                'direccion' => 'Calle 4',
                'password' => 'secret123',
            ])
            ->assertSessionHasErrors('correo');

        $this->assertDatabaseMissing('usuario', ['nombre_usuario' => 'admin_dup']);
    }

    public function test_administrator_cannot_delete_self(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.administradores.destroy', $this->admin->usuario_id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('usuario', ['usuario_id' => $this->admin->usuario_id]);
    }

    public function test_administrator_can_be_deleted_when_another_remains(): void
    {
        $otroAdmin = Usuario::create([
            'nombre_usuario' => 'admin2',
            'nombre_completo' => 'Admin Dos',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 3',
            'correo' => 'admin2@example.com',
        ]);

        // El admin autenticado intenta borrar al otro; queda 1 solo tras borrar, pero hay 2.
        $this->actingAs($this->admin)
            ->delete(route('admin.administradores.destroy', $otroAdmin->usuario_id))
            ->assertRedirect(route('admin.administradores.index'));

        $this->assertDatabaseMissing('usuario', ['usuario_id' => $otroAdmin->usuario_id]);
    }

    public function test_administrator_with_orders_cannot_be_deleted(): void
    {
        Pedido::create([
            'usuario_id' => $this->admin->usuario_id,
            'subtotal' => 10,
            'iva' => 1.5,
            'total' => 11.5,
            'estado' => 'Pendiente',
        ]);

        $otroAdmin = Usuario::create([
            'nombre_usuario' => 'admin2',
            'nombre_completo' => 'Admin Dos',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 3',
            'correo' => 'admin2@example.com',
        ]);

        $this->actingAs($otroAdmin)
            ->delete(route('admin.administradores.destroy', $this->admin->usuario_id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('usuario', ['usuario_id' => $this->admin->usuario_id]);
    }

    public function test_abandoned_carts_are_listed(): void
    {
        $producto = Producto::create([
            'nombre' => 'Terno de prueba',
            'precio' => 10,
            'categoria_id' => 1,
        ]);

        $variante = \App\Models\ProductoVariante::create([
            'producto_id' => $producto->producto_id,
            'talla' => '40',
            'color' => null,
            'sku' => 'SKU-CARRITO',
            'stock' => 5,
            'activo' => true,
        ]);

        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'variante_id' => $variante->variante_id,
            'cantidad' => 2,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.carritos.index'));

        $response->assertOk();
        $response->assertSee('cliente1');
        $response->assertSee('Terno de prueba');
        $response->assertSee('$20.00');
    }

    public function test_client_cannot_access_management_pages(): void
    {
        foreach (['admin.productos.index', 'admin.administradores.index', 'admin.carritos.index'] as $route) {
            $this->actingAs($this->cliente)->get(route($route))->assertRedirect(route('login'));
        }
    }
}
