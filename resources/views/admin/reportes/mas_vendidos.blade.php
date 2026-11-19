@extends('layouts.admin')

@section('title', 'Top Productos Más Vendidos')

@section('actions')
<button onclick="window.print()" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-print me-2"></i> Imprimir Reporte
</button>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
        <i class="fas fa-trophy text-warning fs-5 me-2"></i>
        <h6 class="mb-0 fw-bold m-0">Top 10 Productos con Mayor Rotación</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 mt-3">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-center" style="width: 80px;">Rango</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-end pe-4">Unidades Vendidas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $index => $producto)
                        <tr>
                            <td class="text-center ps-4">
                                @if($index == 0)
                                    <i class="fas fa-medal text-warning fa-2x" title="1er Lugar"></i>
                                @elseif($index == 1)
                                    <i class="fas fa-medal text-secondary fa-2x" title="2do Lugar"></i>
                                @elseif($index == 2)
                                    <i class="fas fa-medal fa-2x medal-bronze" title="3er Lugar"></i>
                                @else
                                    <span class="fs-5 fw-bold text-muted">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                {{ $producto->nombre }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $producto->categoria->nombre ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <span class="fs-5 fw-bold text-primary">{{ floor($producto->cantidad_vendida ?? 0) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">No hay datos de ventas para mostrar.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
