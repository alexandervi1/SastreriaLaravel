@extends('layouts.app')

@section('title', 'Detalle de Pedido #' . $pedido->pedido_id)

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap g-3">
    <div class="d-flex align-items-center">
        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left"></i> Mis Pedidos</a>
        <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="fas fa-home"></i> Ir al Inicio</a>
    </div>
    <h2 class="fw-bold mb-0">Detalle del Pedido</h2>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Productos Solicitados</h5>
                <span class="badge bg-success text-white px-3 py-2 rounded-pill fs-6">{{ $pedido->estado }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">Producto</th>
                                <th scope="col" class="text-center">Precio Unit.</th>
                                <th scope="col" class="text-center">Cantidad</th>
                                <th scope="col" class="text-end pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedido->lineas as $linea)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($linea->producto->imagen_url ?? 'img/placeholder.svg') }}" class="rounded-3 me-3 object-fit-cover" style="width: 50px; height: 50px;" alt="{{ $linea->producto_nombre }}">
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
                                    <td class="text-center text-muted">
                                        ${{ number_format($linea->precio_unitario, 2) }}
                                    </td>
                                    <td class="text-center">
                                        {{ $linea->cantidad }} u
                                    </td>
                                    <td class="text-end fw-bold pe-4">
                                        ${{ number_format($linea->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <h5 class="fw-bold border-bottom pb-3 mb-3">Resumen</h5>
                
                <div class="d-flex justify-content-between mb-2 text-muted">
                    <span>Fecha:</span>
                    <span class="fw-bold text-dark">{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2 text-muted">
                    <span>Nº Orden:</span>
                    <span class="fw-bold text-dark">#{{ str_pad($pedido->pedido_id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2 text-muted">
                    <span>Subtotal:</span>
                    <span class="fw-bold text-dark">${{ number_format($pedido->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-muted">
                    <span>IVA ({{ $pedido->iva_pct }}%):</span>
                    <span class="fw-bold text-dark">${{ number_format($pedido->iva, 2) }}</span>
                </div>
                
                <div class="d-flex justify-content-between mb-0 mt-3 align-items-center">
                    <span class="fs-5 fw-bold">Total Abonado</span>
                    <span class="fs-3 fw-bold text-success">${{ number_format($pedido->total, 2) }}</span>
                </div>

                <div class="mt-4">
                    <a href="{{ route('pedidos.receipt', $pedido->pedido_id) }}" target="_blank" class="btn btn-brand-dark w-100 py-2 fw-bold">
                        <i class="fas fa-file-invoice me-2"></i> Ver Recibo / Imprimir
                    </a>

                    @if($pedido->lineas->contains('requiere_cita', true) && $pedido->estado !== 'Cancelado')
                        <a href="{{ route('citas.create', ['tipo' => 'medidas', 'pedido_id' => $pedido->pedido_id]) }}" class="btn btn-primary w-100 py-2 fw-bold mt-2">
                            <i class="fas fa-calendar-plus me-2"></i> Agenda tu cita de medidas
                        </a>
                    @endif

                    @if($pedido->estado === 'En confección')
                        <a href="{{ route('citas.create', ['tipo' => 'prueba', 'pedido_id' => $pedido->pedido_id]) }}" class="btn btn-outline-primary w-100 py-2 fw-bold mt-2">
                            <i class="fas fa-ruler me-2"></i> Agendar prueba
                        </a>
                    @endif

                    @php($waPago = \App\Support\WhatsApp::enlace('Hola, quiero coordinar el pago del pedido ' . ($pedido->numero_comprobante ?? $pedido->pedido_id) . ' por $' . number_format((float) $pedido->total, 2) . '.'))
                    @if($waPago)
                        <a href="{{ $waPago }}" target="_blank" rel="noopener" class="btn btn-success w-100 py-2 fw-bold mt-2">
                            <i class="fab fa-whatsapp me-2"></i> Coordinar pago por WhatsApp
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-light border-0 rounded-bottom-4 py-3 text-center">
                <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Para cambios o reclamos con tu pedido, por favor contáctanos con el número de orden.</p>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
            <div class="card-body text-center p-4">
                <i class="fas fa-shipping-fast fa-3x mb-3 text-brand-soft"></i>
                <h5>Estado de Envío</h5>
                <p class="mb-0 opacity-75 small">Nos pondremos en contacto contigo pronto para coordinar la entrega o retiro en el local.</p>
            </div>
        </div>
    </div>
</div>
@endsection
