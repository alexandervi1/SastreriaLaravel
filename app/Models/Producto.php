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
        'imagen_url',
        'categoria_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'destacado' => 'boolean',
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
