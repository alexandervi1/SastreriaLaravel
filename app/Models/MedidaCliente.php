<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedidaCliente extends Model
{
    protected $table = 'medida_cliente';
    protected $primaryKey = 'medida_id';

    /** Medidas imprescindibles para confeccionar un terno a medida. */
    public const MINIMAS = ['pecho', 'cintura', 'largo_manga', 'entrepierna', 'largo_pantalon'];

    public const CAMPOS = [
        'pecho', 'cintura', 'cadera', 'hombros', 'espalda', 'largo_saco', 'largo_manga', 'cuello',
        'cintura_pantalon', 'cadera_pantalon', 'tiro', 'muslo', 'entrepierna', 'largo_pantalon', 'bota',
        'estatura',
    ];

    protected $fillable = [
        'usuario_id',
        'nombre',
        'pecho', 'cintura', 'cadera', 'hombros', 'espalda', 'largo_saco', 'largo_manga', 'cuello',
        'cintura_pantalon', 'cadera_pantalon', 'tiro', 'muslo', 'entrepierna', 'largo_pantalon', 'bota',
        'estatura',
        'notas',
        'tomadas_en_taller',
    ];

    protected $casts = [
        'pecho' => 'float',
        'cintura' => 'float',
        'cadera' => 'float',
        'hombros' => 'float',
        'espalda' => 'float',
        'largo_saco' => 'float',
        'largo_manga' => 'float',
        'cuello' => 'float',
        'cintura_pantalon' => 'float',
        'cadera_pantalon' => 'float',
        'tiro' => 'float',
        'muslo' => 'float',
        'entrepierna' => 'float',
        'largo_pantalon' => 'float',
        'bota' => 'float',
        'estatura' => 'float',
        'tomadas_en_taller' => 'boolean',
    ];

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    /**
     * ¿El perfil tiene las medidas mínimas para confeccionar?
     */
    public function tieneMedidasMinimas(): bool
    {
        foreach (self::MINIMAS as $campo) {
            if ($this->{$campo} === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Copia de las medidas para congelarlas en la línea del pedido.
     *
     * @return array<string, float|null>
     */
    public function comoArreglo(): array
    {
        $medidas = [];
        foreach (self::CAMPOS as $campo) {
            $medidas[$campo] = $this->{$campo};
        }

        return $medidas;
    }
}
