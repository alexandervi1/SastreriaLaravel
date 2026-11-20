@extends('layouts.admin')

@section('title', 'Nueva cita del taller')

@section('actions')
<a href="{{ route('admin.citas.index', ['dia' => $dia->toDateString()]) }}" class="btn btn-outline-secondary rounded-pill">
    <i class="fas fa-arrow-left me-2"></i> Volver a la agenda
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-plus text-primary me-2"></i> Cita para un cliente</h6>
    </div>
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.citas.create') }}" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold small">Día</label>
                <input type="date" name="dia" class="form-control" value="{{ $dia->toDateString() }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small">Lugar</label>
                <select name="lugar" class="form-select">
                    <option value="taller" {{ $lugar === 'taller' ? 'selected' : '' }}>En el taller</option>
                    @if($domicilioActiva)
                        <option value="domicilio" {{ $lugar === 'domicilio' ? 'selected' : '' }}>A domicilio</option>
                    @endif
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Ver huecos</button>
            </div>
        </form>

        @if($huecos === [])
            <div class="alert alert-warning">No hay huecos disponibles ese día.</div>
        @else
            <form method="POST" action="{{ route('admin.citas.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label fw-bold">Cliente *</label>
                    <select name="usuario_id" class="form-select" required>
                        <option value="">Selecciona…</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->usuario_id }}">{{ $cliente->nombre_completo }} ({{ $cliente->nombre_usuario }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tipo *</label>
                    <select name="tipo" class="form-select" required>
                        <option value="medidas">Toma de medidas</option>
                        <option value="prueba">Prueba</option>
                        <option value="consulta">Consulta</option>
                    </select>
                </div>
                <input type="hidden" name="lugar" value="{{ $lugar }}">
                @if($lugar === 'domicilio')
                    <div class="col-12">
                        <label class="form-label fw-bold">Dirección de la visita *</label>
                        <input type="text" name="direccion_cliente" class="form-control" required maxlength="255" placeholder="Calle, número, referencia">
                    </div>
                @endif
                <div class="col-12">
                    <label class="form-label fw-bold">Horario *</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($huecos as $hueco)
                            <input type="radio" class="btn-check" name="inicio" id="hueco-admin-{{ $loop->index }}" value="{{ $hueco['inicio']->format('Y-m-d H:i:s') }}" {{ $loop->first ? 'checked' : '' }} required>
                            <label class="btn btn-outline-primary" for="hueco-admin-{{ $loop->index }}">{{ $hueco['inicio']->format('H:i') }}–{{ $hueco['fin']->format('H:i') }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Notas</label>
                    <textarea name="notas" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-primary px-5">Crear cita</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
