<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Tela;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Catálogo de demostración, solo a medida. No se siembra en producción salvo SEED_DEMO=true.
 */
class DemoCatalogoSeeder extends Seeder
{
    public function run()
    {
        $carpeta = public_path('img/productos/demo');
        File::ensureDirectoryExists($carpeta);

        $telas = [
            ['nombre' => 'Lana merino azul noche', 'composicion' => '100 % lana merino', 'color' => 'Azul noche', 'recargo' => 40],
            ['nombre' => 'Lana fría carbón', 'composicion' => '90 % lana, 10 % poliéster', 'color' => 'Carbón', 'recargo' => 35],
            ['nombre' => 'Mezcla ligera marfil', 'composicion' => '70 % lana, 30 % seda', 'color' => 'Marfil', 'recargo' => 30],
            ['nombre' => 'Twill clásico gris', 'composicion' => '100 % lana', 'color' => 'Gris perla', 'recargo' => 20],
            ['nombre' => 'Lino fresco arena', 'composicion' => '100 % lino', 'color' => 'Arena', 'recargo' => 15],
        ];
        foreach ($telas as $tela) {
            Tela::create($tela + ['activo' => true]);
        }

        $modelos = [
            [
                'nombre' => 'Terno clásico de 2 piezas', 'tipo_prenda' => 'terno_2p', 'publico' => 'caballero',
                'precio' => 90, 'dias' => 12, 'categoria_id' => 1, 'destacado' => true,
                'color' => '#1F2A44', 'descripcion' => 'Saco y pantalón en corte recto, con hombro natural y forro ligero.',
            ],
            [
                'nombre' => 'Terno de 3 piezas', 'tipo_prenda' => 'terno_3p', 'publico' => 'caballero',
                'precio' => 100, 'dias' => 15, 'categoria_id' => 1, 'destacado' => true,
                'color' => '#2A2622', 'descripcion' => 'Saco, pantalón y chaleco coordinados para ceremonia.',
            ],
            [
                'nombre' => 'Traje de noche', 'tipo_prenda' => 'terno_2p', 'publico' => 'dama',
                'precio' => 90, 'dias' => 14, 'categoria_id' => 1, 'destacado' => true,
                'color' => '#5B2A32', 'descripcion' => 'Saco entallado y pantalón de vestir con caída limpia.',
            ],
            [
                'nombre' => 'Abrigo de paño', 'tipo_prenda' => 'abrigo', 'publico' => 'dama',
                'precio' => 100, 'dias' => 18, 'categoria_id' => 5, 'destacado' => true,
                'color' => '#3E4248', 'descripcion' => 'Abrigo largo de paño, con solapa y bolsillos de ojal.',
            ],
            [
                'nombre' => 'Saco de sastre', 'tipo_prenda' => 'saco', 'publico' => 'caballero',
                'precio' => 85, 'dias' => 10, 'categoria_id' => 5, 'destacado' => false,
                'color' => '#4A5568', 'descripcion' => 'Saco de dos botones, corte semi-entallado y manga funcional.',
            ],
            [
                'nombre' => 'Terno de niño', 'tipo_prenda' => 'terno_2p', 'publico' => 'nino',
                'precio' => 80, 'dias' => 8, 'categoria_id' => 1, 'destacado' => false,
                'color' => '#1F2A44', 'descripcion' => 'Conjunto de saco y pantalón para ocasión, con holguras para crecer.',
            ],
            [
                'nombre' => 'Smoking Midnight', 'tipo_prenda' => 'terno_3p', 'publico' => 'caballero',
                'precio' => 100, 'dias' => 18, 'categoria_id' => 2, 'destacado' => false,
                'color' => '#10131C', 'descripcion' => 'Solapa de pico en satén, chaleco y pantalón con cinta lateral.',
            ],
            [
                'nombre' => 'Chaleco de gala', 'tipo_prenda' => 'chaleco', 'publico' => 'caballero',
                'precio' => 80, 'dias' => 7, 'categoria_id' => 3, 'destacado' => false,
                'color' => '#2B2D31', 'descripcion' => 'Chaleco ajustado al torso, con espalda de forro y botones de nácar.',
            ],
            [
                'nombre' => 'Falda de sastre', 'tipo_prenda' => 'falda', 'publico' => 'dama',
                'precio' => 80, 'dias' => 8, 'categoria_id' => 6, 'destacado' => false,
                'color' => '#5B2A32', 'descripcion' => 'Falda de talle alto con pinzas y largo a la rodilla.',
            ],
            [
                'nombre' => 'Chaqueta de sastre', 'tipo_prenda' => 'chaqueta', 'publico' => 'dama',
                'precio' => 85, 'dias' => 9, 'categoria_id' => 5, 'destacado' => false,
                'color' => '#3E4248', 'descripcion' => 'Chaqueta corta de hombro marcado y manga larga.',
            ],
            [
                'nombre' => 'Pantalón de vestir', 'tipo_prenda' => 'pantalon', 'publico' => 'caballero',
                'precio' => 80, 'dias' => 7, 'categoria_id' => 6, 'destacado' => false,
                'color' => '#2A2622', 'descripcion' => 'Pantalón de pinza con caída recta y cintura ajustada.',
            ],
        ];

        foreach ($modelos as $i => $modelo) {
            $archivo = 'demo-' . ($i + 1) . '.svg';
            File::put($carpeta . '/' . $archivo, $this->svg($modelo['nombre'], $modelo['color']));

            Producto::create([
                'nombre' => $modelo['nombre'],
                'tipo_prenda' => $modelo['tipo_prenda'],
                'publico' => $modelo['publico'],
                'descripcion' => $modelo['descripcion'],
                'precio' => $modelo['precio'],
                'imagen_url' => 'img/productos/demo/' . $archivo,
                'categoria_id' => $modelo['categoria_id'],
                'destacado' => $modelo['destacado'],
                'vende_por_talla' => false,
                'vende_a_medida' => true,
                'precio_medida' => $modelo['precio'],
                'dias_confeccion' => $modelo['dias'],
            ]);
        }
    }

    private function svg(string $nombre, string $color): string
    {
        $texto = htmlspecialchars($nombre, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800" viewBox="0 0 600 800" role="img" aria-label="{$texto}">
  <rect width="600" height="800" fill="#F3EFE6"/>
  <rect x="60" y="60" width="480" height="680" fill="none" stroke="#A88E5C" stroke-width="2"/>
  <path d="M300 150 L200 190 L150 300 L180 340 L210 320 L210 640 L390 640 L390 320 L420 340 L450 300 L400 190 Z" fill="{$color}"/>
  <path d="M300 150 L260 300 L300 340 L340 300 Z" fill="#F3EFE6" opacity="0.9"/>
  <path d="M260 300 L300 340 L250 380 Z" fill="{$color}" stroke="#A88E5C" stroke-width="4"/>
  <path d="M340 300 L300 340 L350 380 Z" fill="{$color}" stroke="#A88E5C" stroke-width="4"/>
  <circle cx="300" cy="430" r="6" fill="#A88E5C"/>
  <circle cx="300" cy="470" r="6" fill="#A88E5C"/>
  <text x="300" y="710" fill="#2A2622" font-family="Georgia, serif" font-size="26" text-anchor="middle">{$texto}</text>
</svg>
SVG;
    }
}
