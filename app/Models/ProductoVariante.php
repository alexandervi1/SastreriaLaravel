<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoVariante extends Model
{
    protected $table = 'producto_variante';
    protected $primaryKey = 'variante_id';

    protected $fillable = [
        'producto_id',
        'talla',
        'color',
        'sku',
        'stock',
        'activo',
    ];

    protected $casts = [
        'stock' => 'integer',
        'activo' => 'boolean',
    ];

    public function producto(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    /**
     * Descontar stock al registrar un pedido.
     *
     * @throws \RuntimeException cuando la cantidad supera la existencia disponible.
     */
    public function descontarStock(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a descontar debe ser mayor que cero.');
        }

        if ($cantidad > $this->stock) {
            throw new \RuntimeException(
                "Stock insuficiente para la talla {$this->talla}: disponible {$this->stock}, solicitado {$cantidad}."
            );
        }

        $this->stock -= $cantidad;
        $this->save();
    }

    /**
     * Devolver stock al anular un pedido.
     */
    public function reponerStock(int $cantidad): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $this->stock += $cantidad;
        $this->save();
    }

    /**
     * Variantes activas por debajo del umbral configurado.
     */
    public function scopeBajoStock($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('activo', true)
            ->where('stock', '<', Configuracion::obtenerUmbralBajoStock());
    }
}
