@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="fas fa-box-open text-info me-3"></i>Mis Pedidos</h2>
</div>

@if($pedidos->isEmpty())
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
        <i class="fas fa-box fa-4x text-muted mb-3"></i>
        <h3 class="fw-bold text-secondary">Aún no tienes pedidos</h3>
        <p class="text-muted mb-4">¡Cuando realices tu primera compra la verás aquí!</p>
        <a href="{{ route('home') }}" class="btn btn-info btn-lg fw-bold text-dark px-5">Ir al Catálogo</a>
    </div>
@else
    <div class="row row-cols-1 row-cols-md-2 g-4">
        @foreach($pedidos as $pedido)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-header bg-light border-bottom-0 pt-3 pb-0 bg-transparent text-secondary d-flex justify-content-between">
                        <span class="small fw-bold">Pedido #{{ str_pad($pedido->pedido_id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="small"><i class="far fa-calendar-alt me-1"></i> {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title fw-bold text-success mb-3">${{ number_format($pedido->total, 2) }}</h4>
                        <p class="card-text text-muted mb-4">
                            <strong>{{ $pedido->lineas->count() }}</strong> {{ \Illuminate\Support\Str::plural('línea', $pedido->lineas->count()) }} en este pedido.
                        </p>
                        <ul class="list-group list-group-flush mb-3">
                            @foreach($pedido->lineas->take(3) as $linea)
                                <li class="list-group-item bg-transparent px-0 text-truncate d-flex justify-content-between">
                                    <span>{{ $linea->producto_nombre }} @if($linea->talla) · {{ $linea->talla }}@endif</span>
                                    <span class="text-muted">{{ $linea->cantidad }} u.</span>
                                </li>
                            @endforeach
                            @if($pedido->lineas->count() > 3)
                                <li class="list-group-item bg-transparent px-0 text-muted fst-italic border-bottom-0">...y {{ $pedido->lineas->count() - 3 }} más</li>
                            @endif
                        </ul>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pb-4 text-center">
                        <a href="{{ route('pedidos.show', $pedido->pedido_id) }}" class="btn btn-outline-primary fw-bold w-100 rounded-pill">Ver Detalles</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
