<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    public const ESTADO_PENDIENTE = 'Pendiente';
    public const ESTADO_CONFIRMADO = 'Confirmado';
    public const ESTADO_EN_CONFECCION = 'En confección';
    public const ESTADO_LISTO = 'Listo para entrega';
    public const ESTADO_ENTREGADO = 'Entregado';
    public const ESTADO_CANCELADO = 'Cancelado';

    public const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADO,
        self::ESTADO_EN_CONFECCION,
        self::ESTADO_LISTO,
        self::ESTADO_ENTREGADO,
        self::ESTADO_CANCELADO,
    ];

    /** Transiciones permitidas; Entregado y Cancelado son terminales. */
    public const TRANSICIONES = [
        self::ESTADO_PENDIENTE => [self::ESTADO_CONFIRMADO, self::ESTADO_CANCELADO],
        self::ESTADO_CONFIRMADO => [self::ESTADO_EN_CONFECCION, self::ESTADO_LISTO, self::ESTADO_CANCELADO],
        self::ESTADO_EN_CONFECCION => [self::ESTADO_LISTO],
        self::ESTADO_LISTO => [self::ESTADO_ENTREGADO],
        self::ESTADO_ENTREGADO => [],
        self::ESTADO_CANCELADO => [],
    ];

    protected $table = 'pedido';
    protected $primaryKey = 'pedido_id';
    public $timestamps = false;

    protected $fillable = ['usuario_id', 'fecha_pedido', 'subtotal', 'iva', 'iva_pct', 'total', 'numero_comprobante', 'estado', 'fecha_entrega_estimada', 'metodo_entrega', 'direccion_envio'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'iva_pct' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_pedido' => 'datetime',
        'fecha_entrega_estimada' => 'date',
    ];

    public function puedeCambiarA(string $estado): bool
    {
        if ($estado === $this->estado) {
            return true;
        }

        return in_array($estado, self::TRANSICIONES[$this->estado] ?? [], true);
    }

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    public function lineas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PedidoLinea::class, 'pedido_id', 'pedido_id');
    }
}
