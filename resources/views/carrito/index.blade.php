@extends('layouts.app')

@section('title', 'Mi Carrito de Compras')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold mb-0"><i class="fas fa-shopping-cart text-info me-3"></i>Mi Carrito de Compras</h2>
    </div>
    <div class="col-auto">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Seguir Comprando</a>
    </div>
</div>

@if($items->isEmpty())
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
        <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
        <h3 class="fw-bold text-secondary">Tu carrito está vacío</h3>
        <p class="text-muted mb-4">¡Agrega algunos pescados o mariscos para empezar!</p>
        <a href="{{ route('home') }}" class="btn btn-info btn-lg fw-bold text-dark px-5">Ir al Catálogo</a>
    </div>
@else
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Productos ({{ $items->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Producto</th>
                                    <th scope="col" class="text-center">Precio</th>
                                    <th scope="col" class="text-center" style="width: 150px;">Cantidad</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                    <th scope="col" class="text-center pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset($item->producto->imagen_url ?? 'img/placeholder.svg') }}" class="rounded-3 me-3 object-fit-cover" style="width: 60px; height: 60px;" alt="{{ $item->producto->nombre }}">
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $item->producto->nombre }}</h6>
                                                    <small class="text-muted">
                                                        {{ $item->producto->categoria->nombre ?? '' }}
                                                        @if($item->tipo === 'medida')
                                                            · A medida @if($item->tela) · {{ $item->tela->nombre }}@endif
                                                            @if($item->requiere_cita) · Medidas en taller @endif
                                                        @elseif($item->variante)
                                                            · Talla {{ $item->variante->talla }}@if($item->variante->color) · {{ $item->variante->color }}@endif
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            ${{ number_format($precios[$item->carrito_id], 2) }}
                                        </td>
                                        <td>
                                            @if($item->tipo === 'medida')
                                                <div class="text-center text-muted small">1 unidad</div>
                                            @else
                                                <form action="{{ route('carrito.actualizar') }}" method="POST" class="d-flex justify-content-center">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="carrito_id" value="{{ $item->carrito_id }}">
                                                    <div class="input-group input-group-sm w-75">
                                                        <input type="number" name="cantidad" class="form-control text-center bg-light" value="{{ $item->cantidad }}" min="1" max="{{ $item->variante->stock ?? 0 }}" onchange="this.form.submit()">
                                                    </div>
                                                </form>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            ${{ number_format($subtotales[$item->carrito_id], 2) }}
                                        </td>
                                        <td class="text-center pe-4">
                                            <form action="{{ route('carrito.eliminar') }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="carrito_id" value="{{ $item->carrito_id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="Eliminar del carrito" onclick="return confirm('¿Seguro de remover este producto?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
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
            <div class="card border-0 shadow-lg rounded-4 bg-info bg-opacity-10 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <h4 class="card-title fw-bold mb-4 border-bottom pb-2 border-info">Resumen de Compra</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal ({{ collect($items)->sum('cantidad') }} items)</span>
                        <span class="fw-bold">${{ number_format($totales['subtotal'], 2) }}</span>
                    </div>
                    
                    @if($totales['iva_pct'] > 0)
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Impuestos (IVA {{ $totales['iva_pct'] }}%)</span>
                            <span>${{ number_format($totales['iva'], 2) }}</span>
                        </div>
                    @endif
                    
                    <hr class="border-info border-2 opacity-50">
                    
                    <div class="d-flex justify-content-between mb-4 mt-2">
                        <span class="fs-5 fw-bold text-dark">Total a pagar</span>
                        <span class="fs-4 fw-bold text-success">${{ number_format($totales['total'], 2) }}</span>
                    </div>

                    <form action="{{ route('pedidos.procesar') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">
                            <i class="fas fa-check-circle me-2"></i> Confirmar Pedido
                        </button>
                    </form>
                    
                    <p class="text-muted small text-center mt-3 mb-0">
                        <i class="fas fa-shield-alt me-1 text-success"></i> Pedido 100% seguro y garantizado
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
