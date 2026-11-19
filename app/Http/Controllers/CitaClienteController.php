<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\AgendaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;

class CitaClienteController extends Controller
{
    public function index()
    {
        $citas = Cita::with('pedido')
            ->where('usuario_id', auth()->id())
            ->orderByDesc('inicio')
            ->get();

        return view('citas.index', compact('citas'));
    }

    public function create(Request $request)
    {
        $zona = Configuracion::obtenerZonaHoraria();
        $dia = $request->filled('dia')
            ? Carbon::parse((string) $request->query('dia'), $zona)->startOfDay()
            : Carbon::now($zona)->addDay()->startOfDay();

        $huecos = app(AgendaService::class)->huecosDisponibles($dia);
        $tipo = in_array($request->query('tipo'), Cita::TIPOS, true) ? $request->query('tipo') : Cita::TIPO_CONSULTA;
        $pedido = null;

        if ($request->filled('pedido_id')) {
            $pedido = Pedido::where('usuario_id', auth()->id())->find($request->query('pedido_id'));
        }

        return view('citas.create', compact('dia', 'huecos', 'tipo', 'pedido'));
    }

    public function store(Request $request, AgendaService $agenda)
    {
        $data = $request->validate([
            'tipo'      => 'required|in:medidas,prueba,consulta',
            'inicio'    => 'required|date',
            'pedido_id' => 'nullable|exists:pedido,pedido_id',
            'notas'     => 'nullable|string|max:500',
        ]);

        $pedido = null;
        if (! empty($data['pedido_id'])) {
            $pedido = Pedido::where('usuario_id', auth()->id())->find($data['pedido_id']);

            if (! $pedido) {
                return back()->with('error', 'El pedido indicado no existe o no es tuyo.');
            }
        }

        try {
            $inicio = Carbon::parse($data['inicio'], Configuracion::obtenerZonaHoraria());

            $agenda->reservar(auth()->user(), $data['tipo'], $inicio, $pedido, $data['notas'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('citas.index')
            ->with('success', 'Cita reservada. Te esperamos en el taller.');
    }

    public function destroy(Cita $cita)
    {
        abort_unless($cita->usuario_id === auth()->id(), 404);

        if (! in_array($cita->estado, Cita::ESTADOS_ACTIVOS, true)) {
            return back()->with('error', 'Esta cita ya no se puede cancelar.');
        }

        $limite = Carbon::now(Configuracion::obtenerZonaHoraria())
            ->addHours(Configuracion::obtenerCancelacionMinima());

        if ($limite->gt($cita->inicio)) {
            return back()->with('error', 'Ya es demasiado tarde para cancelar: llama al taller.');
        }

        $cita->estado = Cita::ESTADO_CANCELADA;
        $cita->save();

        return back()->with('success', 'Cita cancelada.');
    }
}
