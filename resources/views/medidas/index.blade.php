@extends('layouts.app')

@section('title', 'Mis medidas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0"><i class="fas fa-ruler-combined text-primary me-3"></i>Mis medidas</h2>
    <a href="{{ route('medidas.create') }}" class="btn btn-primary fw-bold rounded-pill px-4">
        <i class="fas fa-plus me-2"></i> Nuevo perfil
    </a>
</div>

@if($medidas->isEmpty())
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
        <i class="fas fa-ruler-combined fa-4x text-muted mb-3"></i>
        <h3 class="fw-bold text-secondary">Aún no tienes medidas guardadas</h3>
        <p class="text-muted mb-4">Crea un perfil para encargar ternos a medida sin volver a medirte.</p>
        <a href="{{ route('medidas.create') }}" class="btn btn-primary btn-lg fw-bold px-5">Crear mi primer perfil</a>
    </div>
@else
    <div class="row row-cols-1 row-cols-md-2 g-4">
        @foreach($medidas as $medida)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="fw-bold mb-0">{{ $medida->nombre }}</h5>
                            @if($medida->tieneMedidasMinimas())
                                <span class="badge bg-success rounded-pill">Completo</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill">Incompleto</span>
                            @endif
                        </div>
                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted">Pecho</dt><dd class="col-7">{{ $medida->pecho !== null ? $medida->pecho . ' cm' : '—' }}</dd>
                            <dt class="col-5 text-muted">Cintura</dt><dd class="col-7">{{ $medida->cintura !== null ? $medida->cintura . ' cm' : '—' }}</dd>
                            <dt class="col-5 text-muted">Largo de manga</dt><dd class="col-7">{{ $medida->largo_manga !== null ? $medida->largo_manga . ' cm' : '—' }}</dd>
                            <dt class="col-5 text-muted">Entrepierna</dt><dd class="col-7">{{ $medida->entrepierna !== null ? $medida->entrepierna . ' cm' : '—' }}</dd>
                            <dt class="col-5 text-muted">Largo de pantalón</dt><dd class="col-7">{{ $medida->largo_pantalon !== null ? $medida->largo_pantalon . ' cm' : '—' }}</dd>
                        </dl>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pb-4 d-flex gap-2">
                        <a href="{{ route('medidas.edit', $medida->medida_id) }}" class="btn btn-outline-primary fw-bold rounded-pill flex-grow-1">Editar</a>
                        <form action="{{ route('medidas.destroy', $medida->medida_id) }}" method="POST" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger rounded-pill" onclick="return confirm('¿Eliminar este perfil?')" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
