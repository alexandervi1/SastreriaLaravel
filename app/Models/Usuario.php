<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'usuario_id';

    protected $fillable = [
        'nombre_usuario',
        'nombre_completo',
        'identificacion',
        'password',
        'rol_id',
        'direccion',
        'correo',
        'telefono',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password_changed_at' => 'datetime',
    ];

    /**
     * ¿La contraseña supera la antigüedad máxima permitida?
     */
    public function passwordVencida(?int $dias = null): bool
    {
        $dias = $dias ?? \App\Models\Configuracion::obtenerRotacionDias();
        $desde = $this->password_changed_at ?? $this->created_at;

        return $desde !== null && $desde->diffInDays(now()) >= $dias;
    }

    // ── Relaciones ──

    public function rol(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'rol_id');
    }

    public function carrito(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Carrito::class, 'usuario_id', 'usuario_id');
    }

    public function pedidos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pedido::class, 'usuario_id', 'usuario_id');
    }

    public function medidas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MedidaCliente::class, 'usuario_id', 'usuario_id');
    }

    // ── Helpers de rol ──

    public function esAdmin(): bool
    {
        return $this->rol_id === 1;
    }

    public function esCliente(): bool
    {
        return $this->rol_id === 2;
    }

    public function esVisitante(): bool
    {
        return $this->rol_id === 3;
    }

    /**
     * Columna usada para autenticación (username en vez de email).
     */
    public function getAuthIdentifierName()
    {
        return 'usuario_id';
    }
}
