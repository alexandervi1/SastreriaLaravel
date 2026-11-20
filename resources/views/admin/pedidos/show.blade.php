@extends('layouts.admin')

@section('title', 'Detalle del Pedido')

@section('actions')
<a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary rounded-pill">
    <i class="fas fa-arrow-left me-2"></i> Volver a la Lista
</a>
<a href="{{ route('admin.pedidos.orden-trabajo', $pedido->pedido_id) }}" class="btn btn-primary rounded-pill">
    <i class="fas fa-print me-2"></i> Orden de trabajo
</a>
@endsection

@section('content')
<div class="row g-4">
    <!-- Información del Cliente y Estado -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h6 class="mb-0 fw-bold text-brand">Información del Cliente</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><i class="fas fa-user text-muted me-2"></i> <strong>{{ $pedido->usuario->nombre_completo ?? 'N/A' }}</strong></p>
                <p class="mb-2"><i class="fas fa-id-card text-muted me-2"></i> {{ $pedido->usuario->identificacion ?? 'N/A' }}</p>
                <p class="mb-2"><i class="fas fa-envelope text-muted me-2"></i> {{ $pedido->usuario->correo ?? 'N/A' }}</p>
                <p class="mb-2"><i class="fas fa-phone text-muted me-2"></i> {{ $pedido->usuario->telefono ?? 'N/A' }}</p>
                @php
                    $waCliente = \App\Support\WhatsApp::enlace('Hola ' . ($pedido->usuario->nombre_completo ?? '') . ', te escribimos por tu pedido ' . ($pedido->numero_comprobante ?? $pedido->pedido_id) . '.');
                @endphp
                @if($waCliente && $pedido->usuario->telefono)
                    <a href="{{ $waCliente }}" target="_blank" rel="noopener" class="btn btn-success btn-sm rounded-pill mb-2">
                        <i class="fab fa-whatsapp me-1"></i> Escribir al cliente
                    </a>
                @endif
                <hr>
                <p class="mb-0"><i class="fas fa-map-marker-alt text-muted me-2"></i> {{ $pedido->usuario->direccion ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h6 class="mb-0 fw-bold text-brand">Actualizar Estado</h6>
            </div>
            <div class="card-body bg-light rounded-bottom-4">
                @php
                    $transiciones = array_values(array_unique(array_merge(
                        [$pedido->estado],
                        \App\Models\Pedido::TRANSICIONES[$pedido->estado] ?? []
                    )));
                    $sinTransiciones = count($transiciones) === 1;
                @endphp
                <form action="{{ route('admin.pedidos.update', $pedido->pedido_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Estado Actual</label>
                        <select name="estado" class="form-select" {{ $sinTransiciones ? 'disabled' : '' }}>
                            @foreach($transiciones as $estado)
                                <option value="{{ $estado }}" {{ $pedido->estado == $estado ? 'selected' : '' }}>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill" {{ $sinTransiciones ? 'disabled' : '' }}>
                        Guardar Cambios <i class="fas fa-save ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Detalle de Productos -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom border-light">
                <div>
                    <h6 class="mb-1 fw-bold text-brand">Pedido: {{ $pedido->numero_comprobante ?? 'S/N' }}</h6>
                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</small>
                </div>
                <div>
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
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Producto</th>
                                <th>Precio Unit.</th>
                                <th>Cantidad</th>
                                <th class="text-end pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedido->lineas as $linea)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        @if($linea->producto->imagen_url)
                                            <img src="{{ asset($linea->producto->imagen_url) }}" class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light text-muted d-flex align-items-center justify-content-center rounded me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="fw-bold d-block">{{ $linea->producto_nombre }}</span>
                                            @if($linea->tipo === 'medida')
                                                <small class="text-muted">A medida @if($linea->tela_nombre) · {{ $linea->tela_nombre }}@endif</small>
                                            @elseif($linea->talla || $linea->color)
                                                <small class="text-muted">{{ $linea->talla }}@if($linea->color) · {{ $linea->color }}@endif</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 align-middle">${{ number_format($linea->precio_unitario, 2) }}</td>
                                <td class="py-3 align-middle">
                                    {{ $linea->cantidad }} u
                                </td>
                                <td class="text-end pe-4 py-3 align-middle fw-bold">
                                    ${{ number_format($linea->subtotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end py-3 text-muted">Total a Pagar:</td>
                                <td class="text-end pe-4 py-3 h4 fw-bold mb-0 text-brand">
                                    ${{ number_format($pedido->total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
