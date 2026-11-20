<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    protected $primaryKey = 'clave';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['clave', 'valor'];

    /**
     * Memo por petición: evita una lectura de caché por cada clave consultada.
     *
     * @var array<string, string|null>|null
     */
    private static ?array $memoria = null;

    protected static function booted()
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());
    }

    /**
     * Todos los valores en una sola consulta cacheada.
     *
     * @return array<string, string|null>
     */
    public static function valores(): array
    {
        if (self::$memoria !== null) {
            return self::$memoria;
        }

        return self::$memoria = Cache::rememberForever('configuracion', function () {
            return self::query()->pluck('valor', 'clave')->all();
        });
    }

    public static function flush(): void
    {
        self::$memoria = null;
        Cache::forget('configuracion');
    }

    /**
     * Obtener el valor de una configuración por su clave.
     */
    public static function obtenerValor(string $clave, $default = null)
    {
        return self::valores()[$clave] ?? $default;
    }

    /**
     * Obtener el IVA actual.
     */
    public static function obtenerIva(): float
    {
        return (float) self::obtenerValor('iva_actual', 15);
    }

    public static function obtenerUmbralBajoStock(): int
    {
        return (int) self::obtenerValor('umbral_bajo_stock', 50);
    }

    public static function obtenerRotacionDias(): int
    {
        return (int) self::obtenerValor('password_rotacion_dias', 90);
    }

    public static function obtenerCapacidadSemanal(): int
    {
        return max(1, (int) self::obtenerValor('capacidad_semanal', 3));
    }

    public static function obtenerZonaHoraria(): string
    {
        return (string) self::obtenerValor('zona_horaria', 'America/Guayaquil');
    }

    public static function obtenerSerieComprobante(): string
    {
        return (string) self::obtenerValor('comprobante_serie', '001-001');
    }

    public static function obtenerLogo(): string
    {
        return (string) self::obtenerValor('negocio_logo', 'img/placeholder.svg');
    }

    public static function ventaPorTallaActiva(): bool
    {
        return (string) self::obtenerValor('venta_por_talla', '0') === '1';
    }

    public static function citaDomicilioActiva(): bool
    {
        return (string) self::obtenerValor('cita_domicilio_activa', '1') === '1';
    }

    public static function obtenerZonaDomicilio(): string
    {
        return (string) self::obtenerValor('cita_domicilio_zona', '');
    }

    public static function obtenerDireccionPublica(): string
    {
        return (string) self::obtenerValor('negocio_direccion_publica', '');
    }

    public static function obtenerDireccionExacta(): string
    {
        return (string) self::obtenerValor('negocio_direccion', '');
    }

    /**
     * Días ISO en que abre el taller.
     *
     * @return array<int, int>
     */
    public static function obtenerDiasTaller(): array
    {
        $csv = (string) self::obtenerValor('taller_dias', '1,2,3,4,5,6');
        $dias = array_filter(array_map('intval', explode(',', $csv)), fn (int $dia) => $dia >= 1 && $dia <= 7);

        return array_values(array_unique($dias));
    }

    public static function obtenerHoraInicio(): string
    {
        return (string) self::obtenerValor('taller_hora_inicio', '09:00');
    }

    public static function obtenerHoraFin(): string
    {
        return (string) self::obtenerValor('taller_hora_fin', '18:00');
    }

    public static function obtenerDuracionCita(): int
    {
        return (int) self::obtenerValor('cita_duracion_min', 45);
    }

    public static function obtenerAnticipacionMaxima(): int
    {
        return (int) self::obtenerValor('cita_anticipacion_max_dias', 30);
    }

    public static function obtenerCancelacionMinima(): int
    {
        return (int) self::obtenerValor('cita_cancelacion_min_horas', 24);
    }

    public static function obtenerWhatsapp(): string
    {
        return (string) self::obtenerValor('negocio_whatsapp', '');
    }
}
