<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagen';
    protected $primaryKey = 'imagen_id';

    protected $fillable = ['producto_id', 'url', 'orden', 'alt'];

    protected $casts = ['orden' => 'integer'];

    public function producto(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }
}
