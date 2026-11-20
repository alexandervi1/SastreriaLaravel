@extends('layouts.admin')

@section('title', 'Carritos Activos')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold"><i class="fas fa-cart-shopping text-primary me-2"></i> Carritos Activos</h5>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-light text-muted fw-normal">{{ $carritos->count() }} {{ \Illuminate\Support\Str::plural('registro', $carritos->count()) }}</span>
            <span class="badge bg-info rounded-pill px-3">Valor estimado: ${{ number_format($valorTotal, 2) }}</span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Cliente</th>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Precio unit.</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end pe-4">Agregado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carritos as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary"><i class="fas fa-user-circle me-2 text-secondary"></i>{{ $item->usuario->nombre_usuario ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $item->usuario->nombre_completo ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $item->producto->nombre ?? 'Producto eliminado' }}</div>
                                <div class="small text-muted">{{ $item->producto->categoria->nombre ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                {{ $item->cantidad }} u
                            </td>
                            <td class="text-center text-muted">${{ number_format($item->producto->precio ?? 0, 2) }}</td>
                            <td class="text-end fw-bold text-success">${{ number_format($item->cantidad * ($item->producto->precio ?? 0), 2) }}</td>
                            <td class="text-end pe-4 text-muted small">
                                {{ $item->fecha_agregado ? \Illuminate\Support\Carbon::parse($item->fecha_agregado)->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-cart-shopping fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">No hay carritos activos en este momento.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
