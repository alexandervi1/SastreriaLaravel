@extends('layouts.app')

@section('title', 'Categoría: ' . $categoria->nombre)

@section('content')
<!-- Reusamos el catálogo del home pero filtrado -->
<div class="text-center mb-5 mt-4" id="catalogo">
    <h2 class="fw-bold mb-4 position-relative d-inline-block pb-2">
        Categoría: {{ $categoria->nombre }}
        <span class="position-absolute bottom-0 start-50 translate-middle-x bg-brand" style="height: 3px; width: 50%;"></span>
    </h2>
    <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
        <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">Todos</a>
        @foreach($categorias as $cat)
            <a href="{{ route('categorias.show', $cat->categoria_id) }}" class="btn {{ $categoria->categoria_id == $cat->categoria_id ? 'cat' : 'btn-outline-primary' }} rounded-pill px-4 shadow-sm">{{ $cat->nombre }}</a>
        @endforeach
    </div>
</div>

<div class="row g-4">
    @forelse($categoria->productos as $producto)
        <div class="col-sm-6 col-md-4 col-xl-3">
            <div class="card h-100 shadow-sm border-0 product-card overflow-hidden rounded-4">
                <div class="position-relative">
                    <img src="{{ asset($producto->imagen_url ?? 'img/placeholder.svg') }}" class="card-img-top object-fit-cover" style="height: 200px;" alt="{{ $producto->nombre }}">
                    @if($producto->stockVenta <= 0)
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center">
                            <span class="badge bg-danger fs-5 px-3 py-2">Agotado</span>
                        </div>
                    @endif
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold fs-5 text-truncate" title="{{ $producto->nombre }}">{{ $producto->nombre }}</h5>
                    <p class="card-text text-muted small text-truncate-2" style="height: 40px;">{{ $producto->descripcion }}</p>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="fs-4 fw-bold text-success">${{ number_format($producto->precio, 2) }}</span>
                        @if($producto->unidadVenta)
                            <span class="text-muted small">/ {{ $producto->unidadVenta->abreviatura }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0 pb-3 mt-2 px-3">
                    @if($producto->stockVenta > 0)
                        @auth
                            @if(auth()->user()->esCliente())
                                <form action="{{ route('carrito.agregar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="producto_id" value="{{ $producto->producto_id }}">
                                    <div class="input-group mb-2">
                                        <input type="number" name="cantidad" class="form-control form-control-sm text-center" value="1" min="1" max="{{ floor($producto->stockVenta) }}">
                                        <span class="input-group-text">{{ $producto->unidadVenta->abreviatura ?? 'u' }}</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                        <i class="fas fa-cart-plus me-1"></i> Agregar
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary w-100 fw-bold">Modo Admin</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 fw-bold">Inicia sesión</a>
                        @endauth
                    @else
                        <button class="btn btn-secondary w-100 fw-bold" disabled>No Disponible</button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h3>No hay productos en esta categoría</h3>
            <a href="{{ route('home') }}" class="btn btn-primary mt-3">Ver todos los productos</a>
        </div>
    @endforelse
</div>

<style>
.product-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; z-index: 10; }
.text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
