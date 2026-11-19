<?php

namespace App\Http\View\Composers;

use App\Models\Cita;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\ProductoVariante;
use Illuminate\View\View;

/**
 * Datos del layout de administración: contadores del menú y aviso de rotación.
 */
class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'pendingOrders' => Pedido::where('estado', 'Pendiente')->count(),
            'lowStockCount' => ProductoVariante::bajoStock()->count(),
            'citasHoy' => Cita::whereDate('inicio', now()->toDateString())
                ->whereIn('estado', Cita::ESTADOS_ACTIVOS)
                ->count(),
            'passwordRotationDays' => Configuracion::obtenerRotacionDias(),
        ]);
    }
}
