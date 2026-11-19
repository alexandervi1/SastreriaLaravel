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
}
