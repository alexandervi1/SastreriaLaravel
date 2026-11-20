<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaBloqueo extends Model
{
    protected $table = 'cita_bloqueo';
    protected $primaryKey = 'bloqueo_id';

    protected $fillable = ['inicio', 'fin', 'motivo'];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];
}
