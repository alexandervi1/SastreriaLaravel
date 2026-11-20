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
        return $this->desglosarLinea($producto, $tela, $tipo)['total'];
    }

    /**
     * Desglose de una línea en hechura y tela, antes de IVA.
     *
     * @return array{hechura: float, tela: float, total: float}
     */
    public function desglosarLinea(Producto $producto, ?Tela $tela, string $tipo): array
    {
        $hechura = $tipo === 'medida'
            ? (float) ($producto->precio_medida ?? $producto->precio)
            : (float) $producto->precio;

        $recargo = $tipo === 'medida' && $tela ? (float) $tela->recargo : 0.0;

        return [
            'hechura' => round($hechura, 2),
            'tela' => round($recargo, 2),
            'total' => round($hechura + $recargo, 2),
        ];
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
