<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table = 'carrito';
    protected $primaryKey = 'carrito_id';
    public $timestamps = false;

    protected $fillable = ['usuario_id', 'producto_id', 'variante_id', 'cantidad'];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Usuario, $this> */
    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Producto, $this> */
    public function producto(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ProductoVariante, $this> */
    public function variante(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id', 'variante_id');
    }

    /**
     * Subtotal de este item del carrito.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->cantidad * $this->producto->precio;
    }
}
