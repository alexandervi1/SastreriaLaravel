<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedidaCliente extends Model
{
    protected $table = 'medida_cliente';
    protected $primaryKey = 'medida_id';

    public const PATRONES = [
        'caballero' => 'Caballero',
        'dama' => 'Dama',
        'nino' => 'Niño',
    ];

    public const CAMPOS_PATRON = [
        'caballero' => ['pecho', 'cintura', 'cadera', 'hombros', 'espalda', 'largo_saco', 'largo_manga', 'cuello', 'cintura_pantalon', 'cadera_pantalon', 'tiro', 'muslo', 'entrepierna', 'largo_pantalon', 'bota', 'estatura'],
        'dama' => ['busto', 'cintura', 'cadera', 'hombros', 'espalda', 'talle_delantero', 'talle_espalda', 'largo_saco', 'largo_manga', 'cuello', 'cintura_pantalon', 'cadera_pantalon', 'tiro', 'muslo', 'entrepierna', 'largo_pantalon', 'largo_falda', 'bota', 'estatura'],
        'nino' => ['pecho', 'cintura', 'cadera', 'hombros', 'espalda', 'largo_saco', 'largo_manga', 'cuello', 'cintura_pantalon', 'cadera_pantalon', 'tiro', 'muslo', 'entrepierna', 'largo_pantalon', 'bota', 'estatura'],
    ];

    public const CAMPOS = [
        'pecho', 'busto', 'cintura', 'cadera', 'hombros', 'espalda', 'talle_delantero', 'talle_espalda',
        'largo_saco', 'largo_manga', 'cuello',
        'cintura_pantalon', 'cadera_pantalon', 'tiro', 'muslo', 'entrepierna', 'largo_pantalon', 'largo_falda', 'bota',
        'estatura',
    ];

    protected $fillable = [
        'usuario_id',
        'nombre',
        'patron',
        'pecho', 'busto', 'cintura', 'cadera', 'hombros', 'espalda', 'talle_delantero', 'talle_espalda',
        'largo_saco', 'largo_manga', 'cuello',
        'cintura_pantalon', 'cadera_pantalon', 'tiro', 'muslo', 'entrepierna', 'largo_pantalon', 'largo_falda', 'bota',
        'estatura',
        'notas',
        'tomadas_en_taller',
    ];

    protected $casts = [
        'pecho' => 'float',
        'busto' => 'float',
        'cintura' => 'float',
        'cadera' => 'float',
        'hombros' => 'float',
        'espalda' => 'float',
        'talle_delantero' => 'float',
        'talle_espalda' => 'float',
        'largo_saco' => 'float',
        'largo_manga' => 'float',
        'cuello' => 'float',
        'cintura_pantalon' => 'float',
        'cadera_pantalon' => 'float',
        'tiro' => 'float',
        'muslo' => 'float',
        'entrepierna' => 'float',
        'largo_pantalon' => 'float',
        'largo_falda' => 'float',
        'bota' => 'float',
        'estatura' => 'float',
        'tomadas_en_taller' => 'boolean',
    ];

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    private const PRENDAS_CON_PANTALON = ['terno_2p', 'terno_3p', 'pantalon'];
    private const PRENDAS_CON_FALDA = ['falda'];

    public function getPatronLabelAttribute(): string
    {
        return self::PATRONES[$this->patron] ?? $this->patron;
    }

    /**
     * Medidas imprescindibles según el patrón y la prenda.
     *
     * @return array<int, string>
     */
    public static function minimasPara(string $patron, ?string $tipoPrenda = null): array
    {
        $minimas = match ($patron) {
            'dama' => ['busto', 'cintura', 'cadera', 'hombros', 'largo_manga', 'talle_delantero', 'talle_espalda', 'largo_saco'],
            'nino' => ['pecho', 'cintura', 'largo_manga', 'largo_saco'],
            default => ['pecho', 'cintura', 'hombros', 'largo_manga', 'largo_saco'],
        };

        if ($tipoPrenda !== null && in_array($tipoPrenda, self::PRENDAS_CON_PANTALON, true)) {
            $minimas[] = 'largo_pantalon';

            if ($patron === 'caballero') {
                $minimas[] = 'entrepierna';
            }
        }

        if ($patron === 'dama' && $tipoPrenda !== null && in_array($tipoPrenda, self::PRENDAS_CON_FALDA, true)) {
            $minimas[] = 'largo_falda';
        }

        return $minimas;
    }

    /**
     * ¿El perfil tiene las medidas mínimas para confeccionar la prenda?
     */
    public function tieneMedidasMinimas(?string $tipoPrenda = null): bool
    {
        foreach (self::minimasPara((string) $this->patron, $tipoPrenda) as $campo) {
            if ($this->{$campo} === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Copia de las medidas para congelarlas en la línea del pedido.
     *
     * @return array<string, float|string|null>
     */
    public function comoArreglo(): array
    {
        $medidas = ['patron' => $this->patron];

        foreach (self::CAMPOS as $campo) {
            $medidas[$campo] = $this->{$campo};
        }

        return $medidas;
    }
}
