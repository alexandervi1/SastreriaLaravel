<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoLinea extends Model
{
    protected $table = 'pedido_linea';
    protected $primaryKey = 'pedido_linea_id';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'variante_id',
        'tipo',
        'tela_id',
        'tela_nombre',
        'medidas',
        'requiere_cita',
        'notas',
        'producto_nombre',
        'talla',
        'color',
        'cantidad',
        'precio_unitario',
        'precio_hechura',
        'precio_tela',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'precio_hechura' => 'decimal:2',
        'precio_tela' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'medidas' => 'array',
        'requiere_cita' => 'boolean',
    ];

    public function pedido(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'pedido_id');
    }

    public function producto(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    public function variante(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id', 'variante_id');
    }
}
