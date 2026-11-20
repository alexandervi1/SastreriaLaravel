<?php

namespace App\Support;

use App\Models\Configuracion;

class WhatsApp
{
    /**
     * Enlace wa.me con el número configurado, o null si no hay número.
     */
    public static function enlace(string $texto): ?string
    {
        $numero = preg_replace('/\D+/', '', Configuracion::obtenerWhatsapp()) ?? '';

        if ($numero === '') {
            return null;
        }

        return 'https://wa.me/' . $numero . '?text=' . urlencode($texto);
    }
}
