@extends('layouts.admin')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-box-open text-primary me-2"></i> Pedidos Recibidos</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $pedidos->count() }} {{ \Illuminate\Support\Str::plural('registro', $pedidos->count()) }}</span>
    </div>
    <div class="card-body p-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('admin.pedidos.index', ['desde' => $desde->toDateString()]) }}" class="btn btn-sm rounded-pill {{ $estado ? 'btn-outline-secondary' : 'btn-primary' }}">
                Todos ({{ $conteos->sum() }})
            </a>
            @foreach($estados as $opcion)
                <a href="{{ route('admin.pedidos.index', ['estado' => $opcion, 'desde' => $desde->toDateString()]) }}" class="btn btn-sm rounded-pill {{ $estado === $opcion ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $opcion }} ({{ $conteos[$opcion] ?? 0 }})
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.pedidos.index') }}" class="d-flex flex-wrap align-items-end gap-2 mb-3">
            @if($estado)
                <input type="hidden" name="estado" value="{{ $estado }}">
            @endif
            <div>
                <label for="desde" class="form-label small text-muted mb-1">Pedidos desde</label>
                <input type="date" id="desde" name="desde" class="form-control form-control-sm" value="{{ $desde->toDateString() }}">
            </div>
            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">Filtrar</button>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>N° Comprobante</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                    <tr>
                        <td class="fw-bold">{{ $pedido->numero_comprobante ?? 'N/A' }}</td>
                        <td>{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</td>
                        <td>{{ $pedido->usuario->nombre_completo ?? 'N/A' }}</td>
                        <td>${{ number_format($pedido->total, 2) }}</td>
                        <td>
                            @if($pedido->estado == 'Entregado')
                                <span class="badge bg-success rounded-pill px-3 py-2">Entregado</span>
                            @elseif($pedido->estado == 'Confirmado')
                                <span class="badge bg-primary rounded-pill px-3 py-2">Confirmado</span>
                            @elseif($pedido->estado == 'En confección')
                                <span class="badge bg-info rounded-pill px-3 py-2 text-dark">En confección</span>
                            @elseif($pedido->estado == 'Listo para entrega')
                                <span class="badge bg-success rounded-pill px-3 py-2">Listo para entrega</span>
                            @elseif($pedido->estado == 'Cancelado')
                                <span class="badge bg-danger rounded-pill px-3 py-2">Cancelado</span>
                            @else
                                <span class="badge bg-warning rounded-pill px-3 py-2 text-dark">Pendiente</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('admin.pedidos.show', $pedido->pedido_id) }}" class="btn btn-sm btn-outline-primary btn-action" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">Todavía no hay pedidos registrados.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
