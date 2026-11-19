<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'producto_id';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'imagen_url',
        'categoria_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'integer',
    ];

    // ── Relaciones ──

    public function categoria(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', 'categoria_id');
    }

    public function pedidos(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedidoproducto', 'producto_id', 'pedido_id')
                    ->withPivot('cantidad');
    }

    // ── Helpers ──

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
                "Stock insuficiente para {$this->nombre}: disponible {$this->stock}, solicitado {$cantidad}."
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
     * Productos por debajo del umbral configurado de bajo stock.
     */
    public function scopeBajoStock($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('stock', '<', Configuracion::obtenerUmbralBajoStock());
    }
}
