<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Tela;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Catálogo de demostración. No se siembra en producción salvo que SEED_DEMO=true.
 */
class DemoCatalogoSeeder extends Seeder
{
    public function run()
    {
        $carpeta = public_path('img/productos/demo');
        File::ensureDirectoryExists($carpeta);

        $telas = [
            ['nombre' => 'Lana merino azul noche', 'composicion' => '100 % lana merino', 'color' => 'Azul noche', 'recargo' => 90],
            ['nombre' => 'Lana fría carbón', 'composicion' => '90 % lana, 10 % poliéster', 'color' => 'Carbón', 'recargo' => 60],
            ['nombre' => 'Mezcla ligera marfil', 'composicion' => '70 % lana, 30 % seda', 'color' => 'Marfil', 'recargo' => 40],
            ['nombre' => 'Twill clásico gris', 'composicion' => '100 % lana', 'color' => 'Gris perla', 'recargo' => 25],
            ['nombre' => 'Lino fresco arena', 'composicion' => '100 % lino', 'color' => 'Arena', 'recargo' => 0],
        ];
        foreach ($telas as $tela) {
            Tela::create($tela + ['activo' => true]);
        }

        $modelos = [
            [
                'nombre' => 'Terno Slim Fit Azul Noche', 'precio' => 280, 'precio_medida' => 340, 'dias' => 12,
                'categoria_id' => 1, 'destacado' => true, 'color' => '#1F2A44', 'talla' => ['38', '40', '42', '44', '46'], 'stock' => [4, 0, 7, 2, 10],
            ],
            [
                'nombre' => 'Terno Clásico Carbón', 'precio' => 260, 'precio_medida' => 320, 'dias' => 14,
                'categoria_id' => 1, 'destacado' => true, 'color' => '#3E4248', 'talla' => ['40', '42', '44', '46'], 'stock' => [12, 1, 0, 6],
            ],
            [
                'nombre' => 'Smoking Midnight', 'precio' => 390, 'precio_medida' => 460, 'dias' => 18,
                'categoria_id' => 2, 'destacado' => true, 'color' => '#10131C', 'talla' => ['40', '42', '44'], 'stock' => [3, 8, 5],
            ],
            [
                'nombre' => 'Terno Marfil Ceremonia', 'precio' => 310, 'precio_medida' => 370, 'dias' => 15,
                'categoria_id' => 1, 'destacado' => false, 'color' => '#D9D2C3', 'talla' => ['36', '38', '40', '42', '44', '46'], 'stock' => [0, 2, 0, 6, 9, 1],
            ],
            [
                'nombre' => 'Terno a Medida Lana Merino', 'precio' => 350, 'precio_medida' => 420, 'dias' => 20,
                'categoria_id' => 1, 'destacado' => true, 'color' => '#4A5568', 'talla' => [], 'stock' => [],
                'solo_medida' => true,
            ],
            [
                'nombre' => 'Chaleco de Gala', 'precio' => 90, 'precio_medida' => null, 'dias' => null,
                'categoria_id' => 3, 'destacado' => false, 'color' => '#2B2D31', 'talla' => ['S', 'M', 'L'], 'stock' => [5, 9, 0],
                'solo_talla' => true,
            ],
        ];

        foreach ($modelos as $i => $modelo) {
            $archivo = 'demo-' . ($i + 1) . '.svg';
            File::put($carpeta . '/' . $archivo, $this->svg($modelo['nombre'], $modelo['color']));

            $producto = Producto::create([
                'nombre' => $modelo['nombre'],
                'descripcion' => 'Modelo de demostración para la tienda de ternos.',
                'precio' => $modelo['precio'],
                'imagen_url' => 'img/productos/demo/' . $archivo,
                'categoria_id' => $modelo['categoria_id'],
                'destacado' => $modelo['destacado'],
                'vende_por_talla' => ! ($modelo['solo_medida'] ?? false),
                'vende_a_medida' => ! ($modelo['solo_talla'] ?? false),
                'precio_medida' => $modelo['precio_medida'],
                'dias_confeccion' => $modelo['dias'],
            ]);

            foreach ($modelo['talla'] as $indice => $talla) {
                ProductoVariante::create([
                    'producto_id' => $producto->producto_id,
                    'talla' => $talla,
                    'color' => $modelo['color'] === '#D9D2C3' ? 'Marfil' : 'Azul noche',
                    'sku' => 'DEMO-' . $producto->producto_id . '-' . $talla,
                    'stock' => $modelo['stock'][$indice],
                    'activo' => true,
                ]);
            }
        }
    }

    private function svg(string $nombre, string $color): string
    {
        $texto = htmlspecialchars($nombre, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800" viewBox="0 0 600 800" role="img" aria-label="{$texto}">
  <rect width="600" height="800" fill="#F7F4EE"/>
  <rect x="60" y="60" width="480" height="680" fill="none" stroke="#B08D57" stroke-width="2"/>
  <path d="M300 150 L200 190 L150 300 L180 340 L210 320 L210 640 L390 640 L390 320 L420 340 L450 300 L400 190 Z" fill="{$color}"/>
  <path d="M300 150 L260 300 L300 340 L340 300 Z" fill="#F7F4EE" opacity="0.9"/>
  <path d="M260 300 L300 340 L250 380 Z" fill="{$color}" stroke="#B08D57" stroke-width="4"/>
  <path d="M340 300 L300 340 L350 380 Z" fill="{$color}" stroke="#B08D57" stroke-width="4"/>
  <circle cx="300" cy="430" r="6" fill="#B08D57"/>
  <circle cx="300" cy="470" r="6" fill="#B08D57"/>
  <text x="300" y="710" fill="#1C1C1E" font-family="Georgia, serif" font-size="26" text-anchor="middle">{$texto}</text>
</svg>
SVG;
    }
}
