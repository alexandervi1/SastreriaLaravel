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
        $ventaPorTalla = Configuracion::ventaPorTallaActiva();

        $view->with([
            'pendingOrders' => Pedido::where('estado', 'Pendiente')->count(),
            'lowStockCount' => $ventaPorTalla ? ProductoVariante::bajoStock()->count() : 0,
            'ventaPorTalla' => $ventaPorTalla,
            'citasHoy' => Cita::whereDate('inicio', now()->toDateString())
                ->whereIn('estado', Cita::ESTADOS_ACTIVOS)
                ->count(),
            'passwordRotationDays' => Configuracion::obtenerRotacionDias(),
        ]);
    }
}
