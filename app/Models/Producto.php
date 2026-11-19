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
        'slug',
        'activo',
        'destacado',
        'vende_por_talla',
        'vende_a_medida',
        'precio_medida',
        'dias_confeccion',
        'imagen_url',
        'categoria_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'destacado' => 'boolean',
        'vende_por_talla' => 'boolean',
        'vende_a_medida' => 'boolean',
        'precio_medida' => 'decimal:2',
        'dias_confeccion' => 'integer',
    ];

    // ── Relaciones ──

    public function categoria(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', 'categoria_id');
    }

    public function variantes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductoVariante::class, 'producto_id', 'producto_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductoImagen, $this> */
    public function imagenes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductoImagen::class, 'producto_id', 'producto_id')->orderBy('orden');
    }

    // ── Helpers ──

    /**
     * Stock sumado de las variantes activas.
     */
    public function getStockTotalAttribute(): int
    {
        return (int) $this->variantes()->where('activo', true)->sum('stock');
    }

    /**
     * Genera un slug único a partir del nombre.
     */
    protected static function booted()
    {
        static::saving(function (Producto $producto) {
            $base = \Illuminate\Support\Str::slug($producto->nombre);
            $base = $base !== '' ? $base : 'producto';
            $slug = $base;
            $sufijo = 2;

            while (self::where('slug', $slug)->whereKeyNot($producto->getKey())->exists()) {
                $slug = $base . '-' . $sufijo;
                $sufijo++;
            }

            $producto->slug = $slug;
        });
    }
}
