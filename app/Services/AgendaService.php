<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\CitaBloqueo;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AgendaService
{
    /**
     * Huecos libres de un día según horario, bloqueos y citas activas.
     *
     * @return array<int, array{inicio: Carbon, fin: Carbon}>
     */
    public function huecosDisponibles(Carbon $dia): array
    {
        $zona = Configuracion::obtenerZonaHoraria();
        $dia = $dia->copy()->setTimezone($zona)->startOfDay();
        $hoy = Carbon::now($zona);

        if (! in_array($dia->dayOfWeekIso, Configuracion::obtenerDiasTaller(), true)) {
            return [];
        }

        if ($dia->lt($hoy->copy()->startOfDay())) {
            return [];
        }

        if ($dia->gt($hoy->copy()->addDays(Configuracion::obtenerAnticipacionMaxima())->startOfDay())) {
            return [];
        }

        $duracion = Configuracion::obtenerDuracionCita();
        $inicioJornada = $dia->copy()->setTimeFromTimeString(Configuracion::obtenerHoraInicio());
        $finJornada = $dia->copy()->setTimeFromTimeString(Configuracion::obtenerHoraFin());

        if ($duracion <= 0 || $inicioJornada->gte($finJornada)) {
            return [];
        }

        $bloqueos = CitaBloqueo::where('fin', '>', $inicioJornada)
            ->where('inicio', '<', $finJornada)
            ->get();

        $citas = Cita::whereIn('estado', Cita::ESTADOS_ACTIVOS)
            ->where('fin', '>', $inicioJornada)
            ->where('inicio', '<', $finJornada)
            ->get();

        $huecos = [];
        $cursor = $inicioJornada->copy();

        while ($cursor->copy()->addMinutes($duracion)->lte($finJornada)) {
            $fin = $cursor->copy()->addMinutes($duracion);

            if ($cursor->lt($hoy)) {
                $cursor = $fin;
                continue;
            }

            $ocupado = $bloqueos->contains(fn ($bloqueo) => $bloqueo->inicio->lt($fin) && $bloqueo->fin->gt($cursor))
                || $citas->contains(fn ($cita) => $cita->inicio->lt($fin) && $cita->fin->gt($cursor));

            if (! $ocupado) {
                $huecos[] = ['inicio' => $cursor->copy(), 'fin' => $fin];
            }

            $cursor = $fin;
        }

        return $huecos;
    }

    /**
     * Reserva un hueco con bloqueo transaccional para evitar dobles reservas.
     *
     * @throws \RuntimeException con un mensaje apto para el cliente.
     */
    public function reservar(Usuario $usuario, string $tipo, Carbon $inicio, ?Pedido $pedido = null, ?string $notas = null): Cita
    {
        if (! in_array($tipo, Cita::TIPOS, true)) {
            throw new RuntimeException('Tipo de cita inválido.');
        }

        $zona = Configuracion::obtenerZonaHoraria();
        $inicio = $inicio->copy()->setTimezone($zona);

        return DB::transaction(function () use ($usuario, $tipo, $inicio, $pedido, $notas) {
            $dia = $inicio->copy()->startOfDay();

            // Bloquea las citas activas del día antes de revalidar.
            Cita::whereIn('estado', Cita::ESTADOS_ACTIVOS)
                ->whereDate('inicio', $dia->toDateString())
                ->lockForUpdate()
                ->get();

            $hueco = collect($this->huecosDisponibles($dia))
                ->first(fn (array $h) => $h['inicio']->equalTo($inicio));

            if (! $hueco) {
                throw new RuntimeException('Ese horario ya no está disponible. Elige otro hueco.');
            }

            return Cita::create([
                'usuario_id' => $usuario->usuario_id,
                'tipo' => $tipo,
                'inicio' => $hueco['inicio'],
                'fin' => $hueco['fin'],
                'estado' => Cita::ESTADO_SOLICITADA,
                'pedido_id' => $pedido?->pedido_id,
                'notas' => $notas,
            ]);
        });
    }
}
