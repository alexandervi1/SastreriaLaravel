<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\CitaBloqueo;
use App\Models\Configuracion;
use App\Models\Usuario;
use App\Services\AgendaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;

class CitaController extends Controller
{
    public function index(Request $request)
    {
        $zona = Configuracion::obtenerZonaHoraria();
        $dia = $request->filled('dia')
            ? Carbon::parse((string) $request->query('dia'), $zona)->startOfDay()
            : Carbon::now($zona)->startOfDay();

        $vista = $request->query('vista') === 'semana' ? 'semana' : 'dia';
        $estado = in_array($request->query('estado'), Cita::ESTADOS, true) ? $request->query('estado') : null;

        $desde = $vista === 'semana' ? $dia->copy()->startOfWeek() : $dia->copy();
        $hasta = $vista === 'semana' ? $dia->copy()->endOfWeek() : $dia->copy()->endOfDay();

        $citas = Cita::with(['usuario', 'pedido'])
            ->whereBetween('inicio', [$desde, $hasta])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderBy('inicio')
            ->get()
            ->groupBy(fn (Cita $cita) => $cita->inicio->toDateString());

        $bloqueos = CitaBloqueo::where('fin', '>=', $desde)
            ->where('inicio', '<=', $hasta)
            ->orderBy('inicio')
            ->get();

        $conteos = Cita::whereBetween('inicio', [$desde, $hasta])
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('admin.citas.index', compact('citas', 'bloqueos', 'conteos', 'dia', 'vista', 'estado', 'desde', 'hasta'));
    }

    public function create(Request $request)
    {
        $zona = Configuracion::obtenerZonaHoraria();
        $dia = $request->filled('dia')
            ? Carbon::parse((string) $request->query('dia'), $zona)->startOfDay()
            : Carbon::now($zona)->addDay()->startOfDay();

        $clientes = Usuario::where('rol_id', 2)->orderBy('nombre_completo')->get();
        $lugar = $request->query('lugar') === Cita::LUGAR_DOMICILIO ? Cita::LUGAR_DOMICILIO : Cita::LUGAR_TALLER;
        $huecos = app(AgendaService::class)->huecosDisponibles($dia, $lugar === Cita::LUGAR_DOMICILIO ? 2 : 1);
        $domicilioActiva = Configuracion::citaDomicilioActiva();

        return view('admin.citas.create', compact('clientes', 'huecos', 'dia', 'lugar', 'domicilioActiva'));
    }

    public function store(Request $request, AgendaService $agenda)
    {
        $data = $request->validate([
            'usuario_id' => 'required|exists:usuario,usuario_id',
            'tipo'       => 'required|in:medidas,prueba,consulta',
            'lugar'      => 'nullable|in:taller,domicilio',
            'direccion_cliente' => 'nullable|required_if:lugar,domicilio|string|max:255',
            'inicio'     => 'required|date',
            'notas'      => 'nullable|string|max:500',
        ]);

        $cliente = Usuario::where('rol_id', 2)->find($data['usuario_id']);

        if (! $cliente) {
            return back()->with('error', 'Elige un cliente válido.');
        }

        try {
            $inicio = Carbon::parse($data['inicio'], Configuracion::obtenerZonaHoraria());
            $agenda->reservar(
                $cliente,
                $data['tipo'],
                $inicio,
                null,
                $data['notas'] ?? null,
                $data['lugar'] ?? Cita::LUGAR_TALLER,
                $data['direccion_cliente'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.citas.index', ['dia' => $inicio->toDateString()])
            ->with('success', 'Cita creada para el cliente.');
    }

    public function update(Request $request, Cita $cita)
    {
        $data = $request->validate([
            'estado' => 'required|in:' . implode(',', Cita::ESTADOS),
        ]);

        $nuevo = $data['estado'];

        if (! $cita->puedeCambiarA($nuevo)) {
            return back()->with('error', "No se puede pasar una cita de {$cita->estado} a {$nuevo}.");
        }

        $cita->estado = $nuevo;
        $cita->save();

        return back()->with('success', 'Estado de la cita actualizado.');
    }

    public function storeBloqueo(Request $request)
    {
        $data = $request->validate([
            'inicio' => 'required|date',
            'fin'    => 'required|date|after:inicio',
            'motivo' => 'required|string|max:120',
        ]);

        CitaBloqueo::create($data);

        return back()->with('success', 'Bloqueo agregado a la agenda.');
    }

    public function destroyBloqueo(CitaBloqueo $bloqueo)
    {
        $bloqueo->delete();

        return back()->with('success', 'Bloqueo eliminado.');
    }
}
