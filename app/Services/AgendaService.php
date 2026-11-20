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
    public function huecosDisponibles(Carbon $dia, int $bloques = 1): array
    {
        $bloques = max(1, $bloques);
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
        $paso = $duracion * $bloques;

        while ($cursor->copy()->addMinutes($paso)->lte($finJornada)) {
            $fin = $cursor->copy()->addMinutes($paso);

            if ($cursor->lt($hoy)) {
                $cursor = $cursor->copy()->addMinutes($paso);
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
    public function reservar(
        Usuario $usuario,
        string $tipo,
        Carbon $inicio,
        ?Pedido $pedido = null,
        ?string $notas = null,
        string $lugar = Cita::LUGAR_TALLER,
        ?string $direccionCliente = null
    ): Cita {
        if (! in_array($tipo, Cita::TIPOS, true)) {
            throw new RuntimeException('Tipo de cita inválido.');
        }

        if (! array_key_exists($lugar, Cita::LUGARES)) {
            throw new RuntimeException('Lugar de cita inválido.');
        }

        if ($lugar === Cita::LUGAR_DOMICILIO) {
            if (! Configuracion::citaDomicilioActiva()) {
                throw new RuntimeException('Las citas a domicilio no están disponibles.');
            }

            if (trim((string) $direccionCliente) === '') {
                throw new RuntimeException('Indica la dirección donde tomaremos las medidas.');
            }
        }

        $zona = Configuracion::obtenerZonaHoraria();
        $inicio = $inicio->copy()->setTimezone($zona);
        $bloques = $lugar === Cita::LUGAR_DOMICILIO ? 2 : 1;

        return DB::transaction(function () use ($usuario, $tipo, $inicio, $pedido, $notas, $lugar, $direccionCliente, $bloques) {
            $dia = $inicio->copy()->startOfDay();

            // Bloquea las citas activas del día antes de revalidar.
            Cita::whereIn('estado', Cita::ESTADOS_ACTIVOS)
                ->whereDate('inicio', $dia->toDateString())
                ->lockForUpdate()
                ->get();

            $hueco = collect($this->huecosDisponibles($dia, $bloques))
                ->first(fn (array $h) => $h['inicio']->equalTo($inicio));

            if (! $hueco) {
                throw new RuntimeException('Ese horario ya no está disponible. Elige otro hueco.');
            }

            return Cita::create([
                'usuario_id' => $usuario->usuario_id,
                'tipo' => $tipo,
                'lugar' => $lugar,
                'direccion_cliente' => $lugar === Cita::LUGAR_DOMICILIO ? trim((string) $direccionCliente) : null,
                'inicio' => $hueco['inicio'],
                'fin' => $hueco['fin'],
                'estado' => Cita::ESTADO_SOLICITADA,
                'pedido_id' => $pedido?->pedido_id,
                'notas' => $notas,
            ]);
        });
    }
}
