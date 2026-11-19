@extends('layouts.admin')

@section('title', 'Alerta de Bajo Stock')

@section('actions')
<button onclick="window.print()" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-print me-2"></i> Imprimir Reporte
</button>
@endsection

@section('content')

@if($variantes->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
        <h3 class="fw-bold text-dark">Inventario Saludable</h3>
        <p class="text-muted">Actualmente ninguna talla se encuentra por debajo del umbral mínimo ({{ $umbral }} unidades).</p>
    </div>
@else
    <div class="alert alert-danger px-4 py-3 d-flex align-items-center mb-4 rounded-4 shadow-sm">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">¡Atención requerida!</h5>
            <p class="mb-0">Las siguientes <strong>{{ $variantes->count() }}</strong> tallas están por agotarse (menos de {{ $umbral }} u. disponibles).</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-triangle-exclamation text-danger me-2"></i> Tallas Bajo el Umbral</h5>
            <span class="badge bg-light text-muted fw-normal">{{ $variantes->count() }} {{ \Illuminate\Support\Str::plural('registro', $variantes->count()) }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Modelo</th>
                            <th scope="col">Talla</th>
                            <th scope="col">Color</th>
                            <th scope="col">Stock</th>
                            <th scope="col" class="text-center">Estado</th>
                            <th scope="col" class="text-center pe-4">Acción Sugerida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($variantes as $variante)
                            @php
                                $critico = $variante->stock <= 10;
                            @endphp
                            <tr class="{{ $critico ? 'table-danger' : '' }}">
                                <td class="ps-4 py-3 fw-bold">
                                    {{ $variante->producto->nombre ?? 'Modelo eliminado' }}
                                </td>
                                <td>{{ $variante->talla }}</td>
                                <td class="text-muted">{{ $variante->color ?? '—' }}</td>
                                <td>
                                    <span class="fs-5 fw-bold {{ $critico ? 'text-danger' : 'text-warning' }}">{{ $variante->stock }}</span>
                                    <small>u</small>
                                </td>
                                <td class="text-center">
                                    @if($variante->stock == 0)
                                        <span class="badge bg-dark rounded-pill">Agotado</span>
                                    @elseif($critico)
                                        <span class="badge bg-danger rounded-pill">Crítico</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill">Bajo</span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    @if($variante->producto)
                                        <a href="{{ route('admin.productos.edit', $variante->producto_id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-boxes me-1"></i> Reabastecer
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
