<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sustituye las portadas SVG generadas por el catálogo demo con las fotografías
 * incluidas en public/img/productos/modelos. Solo toca filas que aún apuntan al
 * demo generado, nunca una imagen subida desde el panel.
 */
return new class extends Migration
{
    private const ANTERIORES = [
        'Terno clásico de 2 piezas' => 'img/productos/demo/demo-1.svg',
        'Terno de 3 piezas' => 'img/productos/demo/demo-2.svg',
        'Traje de noche' => 'img/productos/demo/demo-3.svg',
        'Abrigo de paño' => 'img/productos/demo/demo-4.svg',
        'Saco de sastre' => 'img/productos/demo/demo-5.svg',
        'Terno de niño' => 'img/productos/demo/demo-6.svg',
        'Smoking Midnight' => 'img/productos/demo/demo-7.svg',
        'Chaleco de gala' => 'img/productos/demo/demo-8.svg',
        'Falda de sastre' => 'img/productos/demo/demo-9.svg',
        'Chaqueta de sastre' => 'img/productos/demo/demo-10.svg',
        'Pantalón de vestir' => 'img/productos/demo/demo-11.svg',
    ];

    private const NUEVAS = [
        'Terno clásico de 2 piezas' => 'img/productos/modelos/terno-2p-caballero.jpg',
        'Terno de 3 piezas' => 'img/productos/modelos/terno-3p-caballero.jpg',
        'Traje de noche' => 'img/productos/modelos/traje-noche-dama.jpg',
        'Abrigo de paño' => 'img/productos/modelos/abrigo-pano-dama.jpg',
        'Saco de sastre' => 'img/productos/modelos/saco-sastre-caballero.jpg',
        'Terno de niño' => 'img/productos/modelos/terno-nino.jpg',
        'Smoking Midnight' => 'img/productos/modelos/smoking-midnight.jpg',
        'Chaleco de gala' => 'img/productos/modelos/chaleco-gala.jpg',
        'Falda de sastre' => 'img/productos/modelos/falda-sastre-dama.jpg',
        'Chaqueta de sastre' => 'img/productos/modelos/chaqueta-sastre-dama.jpg',
        'Pantalón de vestir' => 'img/productos/modelos/pantalon-vestir-caballero.jpg',
    ];

    public function up(): void
    {
        foreach (self::NUEVAS as $nombre => $nueva) {
            DB::table('producto')
                ->where('nombre', $nombre)
                ->where('imagen_url', self::ANTERIORES[$nombre])
                ->update(['imagen_url' => $nueva]);
        }
    }

    public function down(): void
    {
        foreach (self::NUEVAS as $nombre => $nueva) {
            DB::table('producto')
                ->where('nombre', $nombre)
                ->where('imagen_url', $nueva)
                ->update(['imagen_url' => self::ANTERIORES[$nombre]]);
        }
    }
};
