<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Tela;

/**
 * Única autoridad para desglosar importes: subtotal, IVA y total.
 */
class PricingService
{
    /**
     * Precio unitario de una línea: el de talla o el de medida más el recargo de la tela.
     */
    public function precioLinea(Producto $producto, ?Tela $tela, string $tipo): float
    {
        if ($tipo === 'medida') {
            $base = (float) ($producto->precio_medida ?? $producto->precio);
            $recargo = $tela ? (float) $tela->recargo : 0.0;

            return round($base + $recargo, 2);
        }

        return (float) $producto->precio;
    }

    /**
     * @return array{subtotal: float, iva: float, total: float, iva_pct: float}
     */
    public function desglosar(float $subtotal): array
    {
        $subtotal = round($subtotal, 2);
        $ivaPct = Configuracion::obtenerIva();
        $iva = round($subtotal * $ivaPct / 100, 2);

        return [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => round($subtotal + $iva, 2),
            'iva_pct' => $ivaPct,
        ];
    }
}
