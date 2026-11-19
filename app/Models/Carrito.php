<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table = 'carrito';
    protected $primaryKey = 'carrito_id';
    public $timestamps = false;

    protected $fillable = ['usuario_id', 'producto_id', 'variante_id', 'tipo', 'tela_id', 'medida_id', 'requiere_cita', 'notas', 'cantidad'];

    protected $casts = [
        'requiere_cita' => 'boolean',
        'cantidad' => 'integer',
    ];

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

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Tela, $this> */
    public function tela(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tela::class, 'tela_id', 'tela_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<MedidaCliente, $this> */
    public function medida(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MedidaCliente::class, 'medida_id', 'medida_id');
    }

    /**
     * Subtotal de este item del carrito.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->cantidad * $this->producto->precio;
    }
}
