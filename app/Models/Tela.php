<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tela extends Model
{
    protected $table = 'tela';
    protected $primaryKey = 'tela_id';

    protected $fillable = [
        'nombre',
        'composicion',
        'color',
        'recargo',
        'imagen_url',
        'activo',
    ];

    protected $casts = [
        'recargo' => 'decimal:2',
        'activo' => 'boolean',
    ];
}
