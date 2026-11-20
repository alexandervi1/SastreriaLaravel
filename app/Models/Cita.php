<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    public const TIPO_MEDIDAS = 'medidas';
    public const TIPO_PRUEBA = 'prueba';
    public const TIPO_CONSULTA = 'consulta';

    public const TIPOS = [self::TIPO_MEDIDAS, self::TIPO_PRUEBA, self::TIPO_CONSULTA];

    public const LUGAR_TALLER = 'taller';
    public const LUGAR_DOMICILIO = 'domicilio';

    public const LUGARES = [
        self::LUGAR_TALLER => 'En el taller',
        self::LUGAR_DOMICILIO => 'A domicilio',
    ];

    public const ESTADO_SOLICITADA = 'Solicitada';
    public const ESTADO_CONFIRMADA = 'Confirmada';
    public const ESTADO_ATENDIDA = 'Atendida';
    public const ESTADO_NO_ASISTIO = 'No asistió';
    public const ESTADO_CANCELADA = 'Cancelada';

    public const ESTADOS = [
        self::ESTADO_SOLICITADA,
        self::ESTADO_CONFIRMADA,
        self::ESTADO_ATENDIDA,
        self::ESTADO_NO_ASISTIO,
        self::ESTADO_CANCELADA,
    ];

    public const TRANSICIONES = [
        self::ESTADO_SOLICITADA => [self::ESTADO_CONFIRMADA, self::ESTADO_CANCELADA],
        self::ESTADO_CONFIRMADA => [self::ESTADO_ATENDIDA, self::ESTADO_NO_ASISTIO, self::ESTADO_CANCELADA],
        self::ESTADO_ATENDIDA => [],
        self::ESTADO_NO_ASISTIO => [],
        self::ESTADO_CANCELADA => [],
    ];

    /** Estados que ocupan un hueco de la agenda. */
    public const ESTADOS_ACTIVOS = [self::ESTADO_SOLICITADA, self::ESTADO_CONFIRMADA];

    protected $table = 'cita';
    protected $primaryKey = 'cita_id';

    protected $fillable = ['usuario_id', 'tipo', 'lugar', 'direccion_cliente', 'inicio', 'fin', 'estado', 'pedido_id', 'notas'];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    public function pedido(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'pedido_id');
    }

    public function puedeCambiarA(string $estado): bool
    {
        if ($estado === $this->estado) {
            return true;
        }

        return in_array($estado, self::TRANSICIONES[$this->estado] ?? [], true);
    }
}
