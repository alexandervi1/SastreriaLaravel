@extends('layouts.app')

@section('title', 'Nueva cita')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-plus text-primary me-2"></i> Reservar cita</h5>
                <a href="{{ route('citas.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Mis citas</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('citas.create') }}" method="GET" class="row g-2 align-items-end mb-4">
                    <div class="col-md-5">
                        <label for="dia" class="form-label small fw-bold">Día</label>
                        <input type="date" class="form-control" id="dia" name="dia" value="{{ $dia->toDateString() }}" min="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-5">
                        <label for="tipo" class="form-label small fw-bold">Tipo de cita</label>
                        <select class="form-select" id="tipo" name="tipo">
                            <option value="medidas" {{ $tipo === 'medidas' ? 'selected' : '' }}>Toma de medidas</option>
                            <option value="prueba" {{ $tipo === 'prueba' ? 'selected' : '' }}>Prueba</option>
                            <option value="consulta" {{ $tipo === 'consulta' ? 'selected' : '' }}>Consulta</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Ver huecos</button>
                    </div>
                </form>

                @if($huecos === [])
                    <div class="alert alert-warning mb-0">No hay horarios disponibles para ese día. Prueba con otra fecha.</div>
                @else
                    <form action="{{ route('citas.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $tipo }}">
                        @if($pedido)
                            <input type="hidden" name="pedido_id" value="{{ $pedido->pedido_id }}">
                        @endif

                        <label class="form-label fw-bold">Horario *</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($huecos as $hueco)
                                <input type="radio" class="btn-check" name="inicio" id="hueco-{{ $loop->index }}" value="{{ $hueco['inicio']->format('Y-m-d H:i:s') }}" {{ $loop->first ? 'checked' : '' }} required>
                                <label class="btn btn-outline-primary" for="hueco-{{ $loop->index }}">{{ $hueco['inicio']->format('H:i') }}</label>
                            @endforeach
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label fw-bold">Notas</label>
                            <textarea class="form-control" id="notas" name="notas" rows="2" maxlength="500" placeholder="¿Algo que debamos saber?"></textarea>
                        </div>

                        @if($pedido)
                            <p class="small text-muted">La cita queda vinculada al pedido {{ $pedido->numero_comprobante ?? $pedido->pedido_id }}.</p>
                        @endif

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill shadow-sm">
                            <i class="fas fa-calendar-check me-2"></i> Confirmar cita
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
