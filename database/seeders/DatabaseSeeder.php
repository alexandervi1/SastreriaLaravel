<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Configuracion;
use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Se valida antes de tocar la base: si falta configuración en producción,
        // el error es claro y no queda un sembrado a medias.
        $admin = $this->credencialesAdmin();

        // ── 1. Roles ──
        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);

        // ── 2. Configuración ──
        Configuracion::create(['clave' => 'iva_actual', 'valor' => '15']);
        Configuracion::create(['clave' => 'negocio_nombre', 'valor' => '<NOMBRE_NEGOCIO>']);
        Configuracion::create(['clave' => 'negocio_ruc', 'valor' => '']);
        Configuracion::create(['clave' => 'negocio_direccion', 'valor' => '']);
        Configuracion::create(['clave' => 'negocio_direccion_publica', 'valor' => 'Riobamba, sector [COMPLETAR]']);
        Configuracion::create(['clave' => 'negocio_telefono', 'valor' => '']);
        Configuracion::create(['clave' => 'negocio_leyenda', 'valor' => '']);
        Configuracion::create(['clave' => 'negocio_logo', 'valor' => 'img/negocio/logo-placeholder.svg']);
        Configuracion::create(['clave' => 'venta_por_talla', 'valor' => '0']);

        // Taller y citas
        Configuracion::create(['clave' => 'taller_dias', 'valor' => '1,2,3,4,5,6']);
        Configuracion::create(['clave' => 'taller_hora_inicio', 'valor' => '09:00']);
        Configuracion::create(['clave' => 'taller_hora_fin', 'valor' => '18:00']);
        Configuracion::create(['clave' => 'cita_duracion_min', 'valor' => '45']);
        Configuracion::create(['clave' => 'cita_anticipacion_max_dias', 'valor' => '30']);
        Configuracion::create(['clave' => 'cita_cancelacion_min_horas', 'valor' => '24']);
        Configuracion::create(['clave' => 'cita_domicilio_activa', 'valor' => '1']);
        Configuracion::create(['clave' => 'cita_domicilio_zona', 'valor' => 'Solo dentro de Riobamba urbano']);
        Configuracion::create(['clave' => 'capacidad_semanal', 'valor' => '3']);
        Configuracion::create(['clave' => 'negocio_whatsapp', 'valor' => '']);

        // ── 3. Categorías ──
        Categoria::insert([
            ['categoria_id' => 1, 'nombre' => 'Ternos'],
            ['categoria_id' => 2, 'nombre' => 'Smokings'],
            ['categoria_id' => 3, 'nombre' => 'Chalecos'],
            ['categoria_id' => 4, 'nombre' => 'Accesorios'],
        ]);

        // ── 4. Usuario Admin ──
        // En producción se define desde el entorno para no arrancar con credenciales conocidas.
        Usuario::create([
            'nombre_usuario'      => $admin['usuario'],
            'nombre_completo'     => $admin['nombre_completo'],
            'password'            => Hash::make($admin['password']),
            'rol_id'              => 1,
            'direccion'           => $admin['direccion'],
            'correo'              => $admin['correo'],
            'telefono'            => $admin['telefono'],
            'password_changed_at' => $admin['password_changed_at'],
        ]);

        // ── 5. Catálogo de demostración ──
        if (filter_var(env('SEED_DEMO', ! app()->environment('production')), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(DemoCatalogoSeeder::class);
        }
    }

    /**
     * Credenciales del administrador inicial, validadas cuando el entorno es producción.
     *
     * @return array{usuario: string, password: string, correo: string, nombre_completo: string, direccion: string, telefono: ?string, password_changed_at: ?\Illuminate\Support\Carbon}
     */
    private function credencialesAdmin(): array
    {
        $usuario = trim((string) env('INITIAL_ADMIN_USER', ''));
        $password = trim((string) env('INITIAL_ADMIN_PASSWORD', ''));
        $correo = trim((string) env('INITIAL_ADMIN_EMAIL', ''));

        if (app()->environment('production')) {
            if ($usuario === '' || $password === '' || $correo === '') {
                throw new \RuntimeException(
                    'Faltan credenciales del administrador inicial: define INITIAL_ADMIN_USER, '.
                    'INITIAL_ADMIN_PASSWORD e INITIAL_ADMIN_EMAIL antes de sembrar en producción.'
                );
            }

            if (strlen($password) < 12 || $password === 'admin123') {
                throw new \RuntimeException(
                    'La contraseña del administrador inicial debe tener al menos 12 caracteres '.
                    'y no puede ser la contraseña de ejemplo.'
                );
            }

            if (! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException(
                    'INITIAL_ADMIN_EMAIL no contiene un correo electrónico válido.'
                );
            }

            $nombre = trim((string) env('INITIAL_ADMIN_NAME', ''));

            return [
                'usuario'             => $usuario,
                'password'            => $password,
                'correo'              => $correo,
                'nombre_completo'     => $nombre !== '' ? $nombre : 'Administrador',
                'direccion'           => '',
                'telefono'            => null,
                'password_changed_at' => now(),
            ];
        }

        return [
            'usuario'             => $usuario !== '' ? $usuario : 'admin123',
            'password'            => $password !== '' ? $password : 'admin123',
            'correo'              => $correo !== '' ? $correo : 'admin@example.com',
            'nombre_completo'     => 'Administrador',
            'direccion'           => '',
            'telefono'            => null,
            'password_changed_at' => null,
        ];
    }
}
