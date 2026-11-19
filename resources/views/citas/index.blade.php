@extends('layouts.app')

@section('title', 'Mis citas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0"><i class="fas fa-calendar-check text-primary me-3"></i>Mis citas</h2>
    <a href="{{ route('citas.create') }}" class="btn btn-primary fw-bold rounded-pill px-4">
        <i class="fas fa-plus me-2"></i> Nueva cita
    </a>
</div>

@if($citas->isEmpty())
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
        <i class="fas fa-calendar-check fa-4x text-muted mb-3"></i>
        <h3 class="fw-bold text-secondary">Aún no tienes citas</h3>
        <p class="text-muted mb-4">Reserva una toma de medidas, una prueba o una consulta.</p>
        <a href="{{ route('citas.create') }}" class="btn btn-primary btn-lg fw-bold px-5">Reservar cita</a>
    </div>
@else
    <div class="row row-cols-1 row-cols-md-2 g-4">
        @foreach($citas as $cita)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0 text-capitalize">{{ $cita->tipo }}</h5>
                            <span class="badge rounded-pill {{ $cita->estado === 'Cancelada' ? 'bg-secondary' : ($cita->estado === 'Confirmada' ? 'bg-primary' : 'bg-info text-dark') }}">{{ $cita->estado }}</span>
                        </div>
                        <p class="mb-2"><i class="far fa-calendar me-2 text-muted"></i>{{ $cita->inicio->format('d/m/Y') }}</p>
                        <p class="mb-2"><i class="far fa-clock me-2 text-muted"></i>{{ $cita->inicio->format('H:i') }} – {{ $cita->fin->format('H:i') }}</p>
                        @if($cita->pedido)
                            <p class="mb-0 small text-muted">Pedido {{ $cita->pedido->numero_comprobante ?? $cita->pedido->pedido_id }}</p>
                        @endif
                    </div>
                    @if(in_array($cita->estado, ['Solicitada', 'Confirmada'], true))
                        <div class="card-footer bg-transparent border-top-0 pb-4">
                            <form action="{{ route('citas.destroy', $cita->cita_id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill fw-bold" onclick="return confirm('¿Cancelar esta cita?')">
                                    Cancelar cita
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
