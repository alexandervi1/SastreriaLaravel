<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Catálogo de demostración. No se siembra en producción salvo que SEED_DEMO=true.
 */
class DemoCatalogoSeeder extends Seeder
{
    public function run()
    {
        $productos = [
            ['nombre' => 'Albacora Limpia',     'descripcion' => 'Albacora sin vísceras ni piel.',        'precio' => 3.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Albacora Entera',     'descripcion' => 'Pez albacora entero',                  'precio' => 3.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Pulpo',               'descripcion' => 'Pulpo fresco en porciones.',            'precio' => 4.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 3],
            ['nombre' => 'Camarón Mar',         'descripcion' => 'Camarón de mar grande.',                'precio' => 4.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 2],
            ['nombre' => 'Albacora por libra',  'descripcion' => 'Porciones de albacora por libra.',      'precio' => 4.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Albacora negocio',    'descripcion' => 'Precio especial por volumen.',          'precio' => 2.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Calamar',             'descripcion' => 'Calamar fresco por libra.',             'precio' => 4.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 3],
            ['nombre' => 'Langostino',          'descripcion' => 'Langostinos grandes de mar.',           'precio' => 4.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 2],
            ['nombre' => 'Pez Espada',          'descripcion' => 'Filetes de pescado espada.',            'precio' => 4.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Almeja',              'descripcion' => 'Almejas frescas empacadas.',            'precio' => 4.00, 'stock' => 500,  'imagen_url' => null, 'categoria_id' => 2],
            ['nombre' => 'Mejillones',          'descripcion' => 'Mejillones limpios en funda.',          'precio' => 4.00, 'stock' => 1000, 'imagen_url' => null, 'categoria_id' => 2],
            ['nombre' => 'Conchas',             'descripcion' => 'Conchas enteras empacadas (100 unds).', 'precio' => 4.00, 'stock' => 2000, 'imagen_url' => null, 'categoria_id' => 2],
            ['nombre' => 'Pez Rayado',          'descripcion' => 'Pescado rayado fileteado.',             'precio' => 5.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Pez Cache',           'descripcion' => 'Cache fresco en cortes por libra.',     'precio' => 3.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Tilapia',             'descripcion' => 'Tilapia limpia en porciones.',          'precio' => 3.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Picudo',              'descripcion' => 'Picudo fresco fileteado.',              'precio' => 5.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 1],
            ['nombre' => 'Camarón Piscina',     'descripcion' => 'Camarón de cultivo.',                   'precio' => 4.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 2],
            ['nombre' => 'Camarón Río',         'descripcion' => 'Camarón silvestre de río.',             'precio' => 6.00, 'stock' => 1,    'imagen_url' => null, 'categoria_id' => 2],
        ];

        foreach ($productos as $p) {
            Producto::create($p);
        }
    }
}
