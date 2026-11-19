@extends('layouts.admin')

@section('title', 'Panel Inteligente')

@section('content')
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
    <!-- Resumen de Productos -->
    <div class="col">
        <div class="card h-100 rounded-4 border-0 shadow-sm stat-card stat-card-brand">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase fw-bold stat-label mb-0">Total Productos</h6>
                    <i class="fas fa-fish fa-2x opacity-75"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0">{{ $totalProductos }}</h2>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-4 pb-4">
                <a href="{{ route('admin.productos.index') }}" class="text-white text-decoration-none small text-uppercase fw-bold">Ver Catálogo <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Resumen de Clientes -->
    <div class="col">
        <div class="card h-100 rounded-4 border-0 shadow-sm stat-card stat-card-navy">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase fw-bold stat-label mb-0">Clientes Reales</h6>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0">{{ $totalClientes }}</h2>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-4 pb-4">
                <a href="{{ route('admin.clientes.index') }}" class="text-white text-decoration-none small text-uppercase fw-bold">Gestionar <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Resumen de Pedidos -->
    <div class="col">
        <div class="card h-100 rounded-4 border-0 shadow-sm stat-card stat-card-deep">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase fw-bold stat-label mb-0">Total Pedidos</h6>
                    <i class="fas fa-box-open fa-2x opacity-75"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0">{{ $totalPedidos }}</h2>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-4 pb-4">
                <a href="{{ route('admin.pedidos.index') }}" class="text-white text-decoration-none small text-uppercase fw-bold">Ver Despachos <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <!-- Resumen de Ingresos -->
    <div class="col">
        <div class="card h-100 rounded-4 border-0 shadow-sm stat-card stat-card-sky">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase fw-bold stat-label mb-0">Ingresos Totales</h6>
                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                </div>
                <h2 class="display-5 fw-bold mb-0">${{ number_format($ingresosTotales, 2) }}</h2>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-4 pb-4">
                <a href="{{ route('admin.reportes.ventas') }}" class="small text-uppercase fw-bold text-decoration-none text-brand-strong"><i class="fas fa-chart-line me-1"></i> Reporte Financiero</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Gráfico de Ventas Mensuales Analíticas -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="fas fa-chart-area text-primary fs-5 me-2"></i>
                <h6 class="mb-0 fw-bold m-0 text-brand">Tendencia de Ingresos (Últimos 6 Meses)</h6>
            </div>
            <div class="card-body p-4">
                <!-- Lienzo del Gráfico (Chart.js interactivo) -->
                <canvas id="ventasChart" height="250" data-labels="{{ json_encode($labelsGrafico) }}" data-values="{{ json_encode($datosGrafico) }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Alertas Activas de Stock -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-danger fs-5 me-2"></i>
                    <h6 class="mb-0 fw-bold m-0">Alertas Críticas</h6>
                </div>
                <a href="{{ route('admin.reportes.bajo-stock') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Revisar Total</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush rounded-bottom-4">
                    @forelse($productosBajoStock as $prod)
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center">
                                <span class="bg-danger rounded-circle p-2 me-3 d-inline-block shadow-sm"></span>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $prod->nombre }}</h6>
                                    <small class="text-muted"><i class="fas fa-tag me-1"></i>{{ $prod->categoria->nombre ?? 'Sin Categoría' }}</small>
                                </div>
                            </div>
                            <span class="badge bg-danger rounded-pill px-3 py-2 fs-6 shadow-sm">Solo {{ $prod->stock }} u</span>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3 opacity-75"></i>
                            <h6 class="text-muted fw-bold">Inventario Saludable</h6>
                            <p class="small text-muted mb-0 px-4">Ningún producto de la tienda está por debajo de las {{ $umbral }} unidades requeridas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cola Prioritaria: Últimos 5 Pedidos -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="fas fa-motorcycle text-warning fs-5 me-2"></i>
            <h6 class="mb-0 fw-bold m-0 text-brand">Centro de Despacho (Pedidos Recientes)</h6>
        </div>
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3">Administrar Todos <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Comprobante SRI</th>
                        <th class="py-3">Momento de Compra</th>
                        <th class="py-3">Identidad del Cliente</th>
                        <th class="py-3">Monto Final</th>
                        <th class="py-3">Estado de Envío</th>
                        <th class="text-end pe-4 py-3">Ruta de Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidosRecientes as $pr)
                    <tr>
                        <td class="ps-4 fw-bold text-dark py-3">
                            <i class="fas fa-receipt text-secondary me-2"></i>{{ $pr->numero_comprobante ?? 'N/A' }}
                        </td>
                        <td class="text-muted py-3">
                            <i class="far fa-clock me-1"></i> {{ $pr->fecha_pedido->diffForHumans() }}
                        </td>
                        <td class="py-3">
                            <div class="fw-bold"><i class="fas fa-user-circle text-primary me-2"></i>{{ $pr->usuario->nombre_completo ?? 'Desconocido' }}</div>
                        </td>
                        <td class="fw-bold text-success py-3">${{ number_format($pr->total, 2) }}</td>
                        <td class="py-3">
                            @if($pr->estado == 'Entregado')
                                <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm"><i class="fas fa-check me-1"></i>Entregado</span>
                            @elseif($pr->estado == 'Enviado')
                                <span class="badge bg-info rounded-pill px-3 py-2 text-dark shadow-sm"><i class="fas fa-truck me-1"></i>Enviado</span>
                            @elseif($pr->estado == 'Cancelado')
                                <span class="badge bg-danger rounded-pill px-3 py-2 shadow-sm"><i class="fas fa-times me-1"></i>Cancelado</span>
                            @else
                                <span class="badge bg-warning rounded-pill px-3 py-2 text-dark shadow-sm"><i class="fas fa-hourglass-half me-1"></i>Pendiente</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 py-3">
                            <a href="{{ route('admin.pedidos.show', $pr->pedido_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold">Atender</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                            <h6>Tu lista de despachos reciente está vacía.</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const canvas = document.getElementById('ventasChart');
    const labelsGrafico = JSON.parse(canvas.dataset.labels);
    const datosGrafico = JSON.parse(canvas.dataset.values);
    const ctx = canvas.getContext('2d');
    const css = getComputedStyle(document.documentElement);
    const brand = css.getPropertyValue('--color-primary').trim();
    const brandRgb = css.getPropertyValue('--bs-primary-rgb').trim();
    const surface = css.getPropertyValue('--color-surface').trim();
    
    // Relleno translúcido bajo la curva, derivado del color de marca
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, `rgba(${brandRgb}, 0.4)`);
    gradient.addColorStop(1, `rgba(${brandRgb}, 0)`);
    
    const data = {
        labels: labelsGrafico,
        datasets: [{
            label: 'Ingresos Registrados ($)',
            data: datosGrafico,
            borderColor: brand,
            backgroundColor: gradient,
            borderWidth: 3,
            pointBackgroundColor: surface,
            pointBorderColor: brand,
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 8,
            fill: true,
            tension: 0.4 // Curvas suaves
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Oculta la caja de leyenda para más minimalismo
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 46, 93, 0.9)', // Azul profundo para el hover
                    titleFont: { size: 13, family: "'Poppins', sans-serif" },
                    bodyFont: { size: 15, weight: 'bold', family: "'Poppins', sans-serif" },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Ingreso Bruto: $' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: "'Poppins', sans-serif" },
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", weight: 'bold' }
                    }
                }
            }
        }
    });
});
</script>
@endpush
