@extends('layouts.admin')

@section('title', 'Agenda del taller')

@section('actions')
<a href="{{ route('admin.citas.create', ['dia' => $dia->toDateString()]) }}" class="btn btn-primary rounded-pill px-4">
    <i class="fas fa-plus me-2"></i> Nueva cita
</a>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div class="btn-group">
        <a href="{{ route('admin.citas.index', ['dia' => $dia->copy()->subDay()->toDateString(), 'vista' => $vista, 'estado' => $estado]) }}" class="btn btn-outline-secondary"><i class="fas fa-chevron-left"></i></a>
        <a href="{{ route('admin.citas.index', ['vista' => $vista, 'estado' => $estado]) }}" class="btn btn-outline-secondary">Hoy</a>
        <a href="{{ route('admin.citas.index', ['dia' => $dia->copy()->addDay()->toDateString(), 'vista' => $vista, 'estado' => $estado]) }}" class="btn btn-outline-secondary"><i class="fas fa-chevron-right"></i></a>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.citas.index', ['dia' => $dia->toDateString(), 'vista' => 'dia', 'estado' => $estado]) }}" class="btn btn-sm {{ $vista === 'dia' ? 'btn-primary' : 'btn-outline-primary' }}">Día</a>
        <a href="{{ route('admin.citas.index', ['dia' => $dia->toDateString(), 'vista' => 'semana', 'estado' => $estado]) }}" class="btn btn-sm {{ $vista === 'semana' ? 'btn-primary' : 'btn-outline-primary' }}">Semana</a>
        <form method="GET" action="{{ route('admin.citas.index') }}" class="d-flex gap-2">
            <input type="hidden" name="vista" value="{{ $vista }}">
            <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\Cita::ESTADOS as $e)
                    <option value="{{ $e }}" {{ $estado === $e ? 'selected' : '' }}>{{ $e }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold">
                    {{ $vista === 'semana' ? 'Semana del ' . $desde->format('d/m/Y') : $dia->translatedFormat('l d/m/Y') }}
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($citas as $fecha => $delDia)
                    <div class="border-bottom">
                        <div class="bg-light px-3 py-2 small fw-bold text-muted">{{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d/m/Y') }}</div>
                        <div class="list-group list-group-flush">
                            @foreach($delDia as $cita)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                        <div>
                                            <div class="fw-bold">{{ $cita->inicio->format('H:i') }} – {{ $cita->fin->format('H:i') }}</div>
                                            <div>{{ $cita->usuario->nombre_completo ?? 'Cliente eliminado' }} · <span class="text-capitalize">{{ $cita->tipo }}</span></div>
                                            @if($cita->pedido)
                                                <div class="small text-muted">Pedido {{ $cita->pedido->numero_comprobante ?? $cita->pedido->pedido_id }}</div>
                                            @endif
                                            @if($cita->notas)
                                                <div class="small text-muted">{{ $cita->notas }}</div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="badge rounded-pill bg-secondary">{{ $cita->estado }}</span>
                                            <div class="d-flex gap-1 mt-2 flex-wrap justify-content-end">
                                                @if($cita->estado === 'Solicitada')
                                                    <form method="POST" action="{{ route('admin.citas.update', $cita->cita_id) }}">@csrf @method('PUT')<input type="hidden" name="estado" value="Confirmada"><button class="btn btn-sm btn-primary">Confirmar</button></form>
                                                @endif
                                                @if($cita->estado === 'Confirmada')
                                                    <form method="POST" action="{{ route('admin.citas.update', $cita->cita_id) }}">@csrf @method('PUT')<input type="hidden" name="estado" value="Atendida"><button class="btn btn-sm btn-success">Atendida</button></form>
                                                    <form method="POST" action="{{ route('admin.citas.update', $cita->cita_id) }}">@csrf @method('PUT')<input type="hidden" name="estado" value="No asistió"><button class="btn btn-sm btn-outline-secondary">No asistió</button></form>
                                                @endif
                                                @if(in_array($cita->estado, ['Solicitada', 'Confirmada'], true))
                                                    <form method="POST" action="{{ route('admin.citas.update', $cita->cita_id) }}">@csrf @method('PUT')<input type="hidden" name="estado" value="Cancelada"><button class="btn btn-sm btn-outline-danger">Cancelar</button></form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-xmark fa-3x mb-3 opacity-50"></i>
                        <p class="mb-0">No hay citas en este periodo.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-ban text-danger me-2"></i> Bloqueos</h6>
            </div>
            <div class="card-body">
                @forelse($bloqueos as $bloqueo)
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                        <div class="small">
                            <strong>{{ $bloqueo->motivo }}</strong><br>
                            {{ $bloqueo->inicio->format('d/m/Y H:i') }} – {{ $bloqueo->fin->format('d/m/Y H:i') }}
                        </div>
                        <form method="POST" action="{{ route('admin.citas.bloqueos.destroy', $bloqueo->bloqueo_id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button></form>
                    </div>
                @empty
                    <p class="small text-muted mb-3">Sin bloqueos en el periodo.</p>
                @endforelse

                <form method="POST" action="{{ route('admin.citas.bloqueos.store') }}" class="row g-2 mt-2">
                    @csrf
                    <div class="col-6"><label class="form-label small fw-bold">Inicio</label><input type="datetime-local" name="inicio" class="form-control form-control-sm" required></div>
                    <div class="col-6"><label class="form-label small fw-bold">Fin</label><input type="datetime-local" name="fin" class="form-control form-control-sm" required></div>
                    <div class="col-12"><label class="form-label small fw-bold">Motivo</label><input type="text" name="motivo" class="form-control form-control-sm" maxlength="120" required placeholder="Feriado, vacaciones…"></div>
                    <div class="col-12"><button class="btn btn-outline-danger w-100 btn-sm">Agregar bloqueo</button></div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom"><h6 class="mb-0 fw-bold">Resumen</h6></div>
            <div class="card-body">
                @foreach(\App\Models\Cita::ESTADOS as $e)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">{{ $e }}</span>
                        <strong>{{ $conteos[$e] ?? 0 }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
