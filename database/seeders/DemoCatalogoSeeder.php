<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Tela;
use Illuminate\Database\Seeder;

/**
 * Catálogo de demostración, solo a medida. No se siembra en producción salvo SEED_DEMO=true.
 *
 * Las portadas viven en public/img/productos/modelos; sus fuentes y licencias
 * están en documentacion/CREDITOS_IMAGENES.md. Son material de demo: la dueña
 * debe sustituirlas por fotos reales del taller antes de publicar.
 */
class DemoCatalogoSeeder extends Seeder
{
    public function run()
    {
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
                'imagen' => 'terno-2p-caballero.jpg',
                'precio' => 90, 'dias' => 12, 'categoria_id' => 1, 'destacado' => true,
                'color' => '#1F2A44', 'descripcion' => 'Saco y pantalón en corte recto, con hombro natural y forro ligero.',
            ],
            [
                'nombre' => 'Terno de 3 piezas', 'tipo_prenda' => 'terno_3p', 'publico' => 'caballero',
                'imagen' => 'terno-3p-caballero.jpg',
                'precio' => 100, 'dias' => 15, 'categoria_id' => 1, 'destacado' => true,
                'color' => '#2A2622', 'descripcion' => 'Saco, pantalón y chaleco coordinados para ceremonia.',
            ],
            [
                'nombre' => 'Traje de noche', 'tipo_prenda' => 'terno_2p', 'publico' => 'dama',
                'imagen' => 'traje-noche-dama.jpg',
                'precio' => 90, 'dias' => 14, 'categoria_id' => 1, 'destacado' => true,
                'color' => '#5B2A32', 'descripcion' => 'Saco entallado y pantalón de vestir con caída limpia.',
            ],
            [
                'nombre' => 'Abrigo de paño', 'tipo_prenda' => 'abrigo', 'publico' => 'dama',
                'imagen' => 'abrigo-pano-dama.jpg',
                'precio' => 100, 'dias' => 18, 'categoria_id' => 5, 'destacado' => true,
                'color' => '#3E4248', 'descripcion' => 'Abrigo largo de paño, con solapa y bolsillos de ojal.',
            ],
            [
                'nombre' => 'Saco de sastre', 'tipo_prenda' => 'saco', 'publico' => 'caballero',
                'imagen' => 'saco-sastre-caballero.jpg',
                'precio' => 85, 'dias' => 10, 'categoria_id' => 5, 'destacado' => false,
                'color' => '#4A5568', 'descripcion' => 'Saco de dos botones, corte semi-entallado y manga funcional.',
            ],
            [
                'nombre' => 'Terno de niño', 'tipo_prenda' => 'terno_2p', 'publico' => 'nino',
                'imagen' => 'terno-nino.jpg',
                'precio' => 80, 'dias' => 8, 'categoria_id' => 1, 'destacado' => false,
                'color' => '#1F2A44', 'descripcion' => 'Conjunto de saco y pantalón para ocasión, con holguras para crecer.',
            ],
            [
                'nombre' => 'Smoking Midnight', 'tipo_prenda' => 'terno_3p', 'publico' => 'caballero',
                'imagen' => 'smoking-midnight.jpg',
                'precio' => 100, 'dias' => 18, 'categoria_id' => 2, 'destacado' => false,
                'color' => '#10131C', 'descripcion' => 'Solapa de pico en satén, chaleco y pantalón con cinta lateral.',
            ],
            [
                'nombre' => 'Chaleco de gala', 'tipo_prenda' => 'chaleco', 'publico' => 'caballero',
                'imagen' => 'chaleco-gala.jpg',
                'precio' => 80, 'dias' => 7, 'categoria_id' => 3, 'destacado' => false,
                'color' => '#2B2D31', 'descripcion' => 'Chaleco ajustado al torso, con espalda de forro y botones de nácar.',
            ],
            [
                'nombre' => 'Falda de sastre', 'tipo_prenda' => 'falda', 'publico' => 'dama',
                'imagen' => 'falda-sastre-dama.jpg',
                'precio' => 80, 'dias' => 8, 'categoria_id' => 6, 'destacado' => false,
                'color' => '#5B2A32', 'descripcion' => 'Falda de talle alto con pinzas y largo a la rodilla.',
            ],
            [
                'nombre' => 'Chaqueta de sastre', 'tipo_prenda' => 'chaqueta', 'publico' => 'dama',
                'imagen' => 'chaqueta-sastre-dama.jpg',
                'precio' => 85, 'dias' => 9, 'categoria_id' => 5, 'destacado' => false,
                'color' => '#3E4248', 'descripcion' => 'Chaqueta corta de hombro marcado y manga larga.',
            ],
            [
                'nombre' => 'Pantalón de vestir', 'tipo_prenda' => 'pantalon', 'publico' => 'caballero',
                'imagen' => 'pantalon-vestir-caballero.jpg',
                'precio' => 80, 'dias' => 7, 'categoria_id' => 6, 'destacado' => false,
                'color' => '#2A2622', 'descripcion' => 'Pantalón de pinza con caída recta y cintura ajustada.',
            ],
        ];

        foreach ($modelos as $modelo) {
            Producto::create([
                'nombre' => $modelo['nombre'],
                'tipo_prenda' => $modelo['tipo_prenda'],
                'publico' => $modelo['publico'],
                'descripcion' => $modelo['descripcion'],
                'precio' => $modelo['precio'],
                'imagen_url' => 'img/productos/modelos/' . $modelo['imagen'],
                'categoria_id' => $modelo['categoria_id'],
                'destacado' => $modelo['destacado'],
                'vende_por_talla' => false,
                'vende_a_medida' => true,
                'precio_medida' => $modelo['precio'],
                'dias_confeccion' => $modelo['dias'],
            ]);
        }
    }
}
